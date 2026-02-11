<?php

/**
 * USSD Payment Webhook Handler (ExpressPay IPN)
 * 
 * Handles ExpressPay IPN (Instant Payment Notification) callbacks for USSD vote payments.
 * Integrated with Eventic API using shared models.
 */

// Bootstrap: Load main API infrastructure
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/UssdLogger.php';

use Dotenv\Dotenv;
use App\Config\EloquentBootstrap;
use App\Models\AwardVote;
use App\Models\Transaction;
use App\Models\OrganizerBalance;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize Eloquent ORM
EloquentBootstrap::boot();

// Initialize logger
$logger = new UssdLogger('webhook');

// ExpressPay sends data via POST parameters
$orderId = $_POST['order-id'] ?? $_GET['order-id'] ?? '';
$token = $_POST['token'] ?? $_GET['token'] ?? '';
$status = $_POST['status'] ?? $_GET['status'] ?? '';
$transactionId = $_POST['transaction-id'] ?? $_GET['transaction-id'] ?? '';

$logger->info('ExpressPay IPN received', [
    'order_id' => $orderId,
    'token' => $token,
    'status' => $status,
    'transaction_id' => $transactionId,
    'post_data' => $_POST,
    'get_data' => $_GET,
]);

// If no order-id or token, try to get from JSON body
if (empty($orderId) && empty($token)) {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    if ($data) {
        $orderId = $data['order-id'] ?? $data['orderId'] ?? '';
        $token = $data['token'] ?? '';
        $status = $data['status'] ?? '';
        $transactionId = $data['transaction-id'] ?? $data['transactionId'] ?? '';
        
        $logger->info('ExpressPay IPN from JSON body', [
            'order_id' => $orderId,
            'token' => $token,
            'status' => $status,
        ]);
    }
}

// Validate required parameters
if (empty($orderId) && empty($token)) {
    $logger->error('Missing order-id and token in IPN');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit();
}

// Find the vote by reference (order-id) or token
$vote = null;
if (!empty($orderId)) {
    $vote = AwardVote::where('reference', $orderId)->first();
}

if (!$vote && !empty($token)) {
    $vote = AwardVote::where('payment_token', $token)->first();
}

if (!$vote) {
    $logger->warning('Vote not found for IPN', ['order_id' => $orderId, 'token' => $token]);
    http_response_code(200);
    echo json_encode(['status' => 'not_found']);
    exit();
}

if ($vote->status === 'paid') {
    $logger->info('Vote already marked as paid', ['vote_id' => $vote->id]);
    http_response_code(200);
    echo json_encode(['status' => 'already_processed']);
    exit();
}

// Verify the transaction status with ExpressPay
$merchantId = $_ENV['EXPRESSPAY_MERCHANT_ID'] ?? '';
$apiKey = $_ENV['EXPRESSPAY_API_KEY'] ?? '';
$isLive = ($_ENV['APP_ENV'] ?? 'development') === 'production';

$queryUrl = $isLive
    ? 'https://expresspaygh.com/api/query.php'
    : 'https://sandbox.expresspaygh.com/api/query.php';

$queryData = [
    'merchant-id' => $merchantId,
    'api-key' => $apiKey,
    'token' => $token ?: $vote->payment_token,
];

$ch = curl_init($queryUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($queryData),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded",
    ],
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

$logger->debug('ExpressPay query response', ['response' => $response, 'error' => $curlError]);

$queryResult = json_decode($response, true);

// Check if payment was successful
// ExpressPay returns result-code 1 for successful payments
$resultCode = $queryResult['result-code'] ?? $queryResult['result'] ?? null;
$isSuccessful = ($resultCode == 1 || strtolower($queryResult['result'] ?? '') === 'approved');

if (!$isSuccessful) {
    $logger->info('Payment not yet successful', [
        'vote_id' => $vote->id, 
        'result_code' => $resultCode,
        'result' => $queryResult
    ]);
    http_response_code(200);
    echo json_encode(['status' => 'pending', 'message' => 'Payment not yet confirmed']);
    exit();
}

// Mark vote as paid
try {
    $vote->status = 'paid';
    $vote->payment_transaction_id = $transactionId ?: ($queryResult['transaction-id'] ?? null);
    $vote->save();
    
    $logger->info('Vote marked as paid', [
        'vote_id' => $vote->id,
        'nominee_id' => $vote->nominee_id,
        'votes' => $vote->number_of_votes,
        'amount' => $vote->gross_amount,
    ]);
    
    // Create transaction record
    try {
        Transaction::create([
            'reference' => $vote->reference,
            'transaction_type' => 'vote_purchase',
            'organizer_id' => $vote->award->organizer_id ?? null,
            'award_id' => $vote->award_id,
            'vote_id' => $vote->id,
            'gross_amount' => $vote->gross_amount,
            'admin_amount' => $vote->admin_amount,
            'organizer_amount' => $vote->organizer_amount,
            'payment_fee' => $vote->payment_fee,
            'status' => 'completed',
            'description' => "USSD Vote: {$vote->number_of_votes} votes for nominee #{$vote->nominee_id}",
            'metadata' => json_encode([
                'source' => 'ussd',
                'voter_phone' => $vote->voter_phone,
                'payment_provider' => 'expresspay',
                'transaction_id' => $vote->payment_transaction_id,
            ]),
        ]);
        
        $logger->info('Transaction record created', ['reference' => $vote->reference]);
    } catch (Exception $e) {
        $logger->error('Failed to create transaction', ['error' => $e->getMessage()]);
    }
    
    // Update organizer balance
    try {
        $award = $vote->award;
        if ($award && $award->organizer_id) {
            $balance = OrganizerBalance::firstOrCreate(
                ['organizer_id' => $award->organizer_id],
                ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
            );
            
            $balance->pending_balance += $vote->organizer_amount;
            $balance->total_earned += $vote->organizer_amount;
            $balance->save();
            
            $logger->info('Organizer balance updated', [
                'organizer_id' => $award->organizer_id,
                'amount_added' => $vote->organizer_amount,
            ]);
        }
    } catch (Exception $e) {
        $logger->error('Failed to update organizer balance', ['error' => $e->getMessage()]);
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    $logger->error('Failed to process payment', [
        'order_id' => $orderId,
        'error' => $e->getMessage(),
    ]);
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Processing failed']);
}