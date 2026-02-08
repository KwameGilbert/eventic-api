<?php

/**
 * USSD Payment Webhook Handler
 * 
 * Handles Paystack webhook callbacks for USSD vote payments.
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

// Get webhook payload
$input = @file_get_contents("php://input");
$event = json_decode($input, true);

$logger->info('Webhook received', [
    'event_type' => $event['event'] ?? 'unknown',
    'reference' => $event['data']['reference'] ?? 'none',
]);

// Verify this is a successful charge
if (($event['event'] ?? '') !== 'charge.success') {
    $logger->debug('Ignoring non-success event', ['event' => $event['event'] ?? 'none']);
    http_response_code(200);
    echo json_encode(['status' => 'ignored']);
    exit();
}

$reference = $event['data']['reference'] ?? '';
$amount = $event['data']['amount'] ?? 0; // Amount in pesewas
$amountGHS = $amount / 100;

if (empty($reference)) {
    $logger->error('Missing reference in webhook');
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing reference']);
    exit();
}

// Find the pending vote by reference
$vote = AwardVote::where('reference', $reference)->first();

if (!$vote) {
    $logger->warning('Vote not found for reference', ['reference' => $reference]);
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

// Mark vote as paid
try {
    $vote->status = 'paid';
    $vote->save();
    
    $logger->info('Vote marked as paid', [
        'vote_id' => $vote->id,
        'nominee_id' => $vote->nominee_id,
        'votes' => $vote->number_of_votes,
        'amount' => $amountGHS,
    ]);
    
    // Create transaction record
    try {
        Transaction::create([
            'reference' => $reference,
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
            ]),
        ]);
        
        $logger->info('Transaction record created', ['reference' => $reference]);
    } catch (Exception $e) {
        $logger->error('Failed to create transaction', ['error' => $e->getMessage()]);
        // Don't fail the webhook for this - vote is already marked paid
    }
    
    // Update organizer balance
    try {
        $award = $vote->award;
        if ($award && $award->organizer_id) {
            $balance = OrganizerBalance::firstOrCreate(
                ['organizer_id' => $award->organizer_id],
                ['available_balance' => 0, 'pending_balance' => 0, 'total_earned' => 0, 'total_withdrawn' => 0]
            );
            
            // Add to pending balance (will become available after hold period)
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
        // Don't fail the webhook for this - vote is already marked paid
    }
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    $logger->error('Failed to process payment', [
        'reference' => $reference,
        'error' => $e->getMessage(),
    ]);
    
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Processing failed']);
}