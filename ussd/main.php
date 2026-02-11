<?php

/**
 * USSD Entry Point
 * 
 * Integrated with Eventic API using shared models and database connection.
 * This handles USSD voting for awards.
 */

// Bootstrap: Load main API infrastructure
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/UssdLogger.php';

use Dotenv\Dotenv;
use App\Config\EloquentBootstrap;
use App\Models\AwardNominee;
use App\Models\AwardCategory;
use App\Models\Award;
use App\Models\AwardVote;
use Phpfastcache\Helper\Psr16Adapter;

// Load environment variables from parent directory
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Initialize Eloquent ORM (uses same database as main API)
EloquentBootstrap::boot();

// Set up session caching
$defaultDriver = 'Files';
$Psr16Adapter = new Psr16Adapter($defaultDriver);

// Get the JSON request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Initialize logger
$logger = new UssdLogger($data['sessionID'] ?? 'unknown');
$logger->logRequest($data);

// Extract session data
$sessionID = $data['sessionID'] ?? '';
$userID = $data['userID'] ?? '';
$newSession = $data['newSession'] ?? false;
$msisdn = $data['msisdn'] ?? '';
$userData = trim($data['userData'] ?? '');
$network = strtolower($data['network'] ?? 'mtn');

// Get contact info from environment
$contactPhone = $_ENV['USSD_CONTACT_PHONE'] ?? '+233541436414';
$contactEmail = $_ENV['USSD_CONTACT_EMAIL'] ?? 'support@eventic.com';

/**
 * Send USSD response and exit
 */
function sendResponse(string $sessionID, string $msisdn, string $userID, bool $continueSession, string $message, UssdLogger $logger): void
{
    $response = [
        'sessionID' => $sessionID,
        'msisdn' => $msisdn,
        'userID' => $userID,
        'continueSession' => $continueSession,
        'message' => $message,
    ];
    
    $logger->logResponse($response);
    
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

/**
 * Map network name to Paystack provider code
 */
function getPaystackProvider(string $network): string
{
    $providers = [
        'mtn' => 'mtn',
        'vodafone' => 'vod',
        'airteltigo' => 'tgo',
        'airtel' => 'atl',
    ];
    return $providers[$network] ?? 'mtn';
}

// Handle new session - show main menu
if ($newSession) {
    $message = "Welcome to Eventic Voting!\n" .
        "Choose an option:\n" .
        "1. Vote for Nominee\n" .
        "2. View Results\n" .
        "3. Contact Us";
    
    $currentState = [
        'sessionID' => $sessionID,
        'msisdn' => $msisdn,
        'userData' => $userData,
        'network' => $network,
        'newSession' => $newSession,
        'message' => $message,
        'level' => 1,
    ];
    
    $userResponseTracker = $Psr16Adapter->get($sessionID) ?? [];
    $userResponseTracker[] = $currentState;
    $Psr16Adapter->set($sessionID, $userResponseTracker, 300); // 5-minute TTL
    
    sendResponse($sessionID, $msisdn, $userID, true, $message, $logger);
}

// Get session history
$userResponseTracker = $Psr16Adapter->get($sessionID) ?? [];

if (empty($userResponseTracker)) {
    $logger->error('Session not found', ['sessionID' => $sessionID]);
    sendResponse($sessionID, $msisdn, $userID, false, 'Session expired. Please dial again.', $logger);
}

$lastResponse = $userResponseTracker[count($userResponseTracker) - 1];
$message = "Invalid input.";
$continueSession = false;
$level = $lastResponse['level'] ?? 1;

// Process based on current level
switch ($level) {
    case 1: // Main menu selection
        if ($userData === '1') {
            $message = "Enter nominee code:";
            $continueSession = true;
            $level = 2;
        } elseif ($userData === '2') {
            $message = "Visit eventic.com to view live results.\nVoting results are updated in real-time.";
            $continueSession = false;
        } elseif ($userData === '3') {
            $message = "Contact us:\nPhone: {$contactPhone}\nEmail: {$contactEmail}";
            $continueSession = false;
        } else {
            $message = "Invalid option. Please dial again.";
            $continueSession = false;
        }
        break;
        
    case 2: // Nominee code entered
        $nomineeCode = strtoupper($userData);
        
        // Find nominee using Eloquent model
        $nominee = AwardNominee::findByCode($nomineeCode);
        
        if (!$nominee) {
            $logger->warning('Invalid nominee code', ['code' => $nomineeCode]);
            $message = "Invalid nominee code. Please check and try again.";
            $continueSession = false;
            break;
        }
        
        // Load relationships
        $category = $nominee->category;
        $award = $nominee->award;
        
        if (!$category || !$award) {
            $logger->error('Nominee missing category/award', ['nominee_id' => $nominee->id]);
            $message = "System error. Please try again later.";
            $continueSession = false;
            break;
        }
        
        // Check if voting is open
        if (!$category->isVotingOpen() && !$award->isVotingOpen()) {
            $message = "Voting is currently closed for this category.";
            $continueSession = false;
            break;
        }
        
        $message = "Enter number of votes for:\n{$nominee->name}\nCategory: {$category->name}\nCost: GHS {$category->cost_per_vote}/vote";
        $continueSession = true;
        $level = 3;
        
        // Store nominee info in session
        $lastResponse['nomineeId'] = $nominee->id;
        $lastResponse['nomineeCode'] = $nomineeCode;
        $lastResponse['nomineeName'] = $nominee->name;
        $lastResponse['categoryId'] = $category->id;
        $lastResponse['categoryName'] = $category->name;
        $lastResponse['awardId'] = $award->id;
        $lastResponse['awardTitle'] = $award->title;
        $lastResponse['costPerVote'] = (float) $category->cost_per_vote;
        break;
        
    case 3: // Number of votes entered
        $votes = (int) $userData;
        
        if ($votes < 1 || $votes > 1000) {
            $message = "Invalid vote count. Enter between 1-1000:";
            $continueSession = true;
            break;
        }
        
        $costPerVote = $lastResponse['costPerVote'] ?? 1.00;
        $totalCost = $votes * $costPerVote;
        
        $message = "Confirm vote:\n" .
            "Nominee: {$lastResponse['nomineeName']}\n" .
            "Votes: {$votes}\n" .
            "Total: GHS {$totalCost}\n\n" .
            "Reply 1 to confirm";
        $continueSession = true;
        $level = 4;
        
        $lastResponse['votes'] = $votes;
        $lastResponse['totalCost'] = $totalCost;
        break;
        
    case 4: // Confirmation
        if ($userData !== '1') {
            $message = "Vote cancelled.";
            $continueSession = false;
            break;
        }
        
        $nomineeId = $lastResponse['nomineeId'] ?? 0;
        $categoryId = $lastResponse['categoryId'] ?? 0;
        $awardId = $lastResponse['awardId'] ?? 0;
        $votes = $lastResponse['votes'] ?? 0;
        $costPerVote = $lastResponse['costPerVote'] ?? 1.00;
        $totalCost = $lastResponse['totalCost'] ?? 0;
        
        // Get award for revenue split calculation
        $award = Award::find($awardId);
        if (!$award) {
            $logger->error('Award not found for payment', ['award_id' => $awardId]);
            $message = "System error. Please try again.";
            $continueSession = false;
            break;
        }
        
        // Calculate revenue split
        $revenueSplit = $award->calculateRevenueSplit($totalCost);
        
        // Generate payment reference
        $reference = 'USSD_' . $sessionID . '_' . time();
        
        // Create pending vote record
        try {
            $vote = AwardVote::create([
                'nominee_id' => $nomineeId,
                'category_id' => $categoryId,
                'award_id' => $awardId,
                'number_of_votes' => $votes,
                'cost_per_vote' => $costPerVote,
                'gross_amount' => $totalCost,
                'admin_share_percent' => $revenueSplit['admin_share_percent'],
                'admin_amount' => $revenueSplit['admin_amount'],
                'organizer_amount' => $revenueSplit['organizer_amount'],
                'payment_fee' => $revenueSplit['payment_fee'],
                'status' => 'pending',
                'reference' => $reference,
                'voter_phone' => $msisdn,
            ]);
            
            $logger->info('Vote record created', ['vote_id' => $vote->id, 'reference' => $reference]);
        } catch (Exception $e) {
            $logger->error('Failed to create vote record', ['error' => $e->getMessage()]);
            $message = "System error. Please try again.";
            $continueSession = false;
            break;
        }
        
        // Initiate ExpressPay mobile money payment
        $merchantId = $_ENV['EXPRESSPAY_MERCHANT_ID'] ?? '';
        $apiKey = $_ENV['EXPRESSPAY_API_KEY'] ?? '';
        $isLive = ($_ENV['APP_ENV'] ?? 'development') === 'production';
        
        if (empty($merchantId) || empty($apiKey)) {
            $logger->error('ExpressPay credentials not configured');
            $message = "Payment system unavailable. Contact support.";
            $continueSession = false;
            break;
        }
        
        // ExpressPay API URL
        $expressPayUrl = $isLive 
            ? 'https://expresspaygh.com/api/submit.php'
            : 'https://sandbox.expresspaygh.com/api/submit.php';
        
        // Get nominee name for order description
        $nomineeName = $lastResponse['nomineeName'] ?? 'Nominee';
        
        // ExpressPay request data
        $expressPayData = [
            'merchant-id' => $merchantId,
            'api-key' => $apiKey,
            'firstname' => 'USSD',
            'lastname' => 'Voter',
            'email' => "ussd_{$sessionID}@eventic.com",
            'phonenumber' => ltrim($msisdn, '+'),  // Remove + prefix if present
            'amount' => number_format($totalCost, 2, '.', ''),
            'order-id' => $reference,
            'order-desc' => "{$votes} votes for {$nomineeName}",
            'redirect-url' => $_ENV['APP_URL'] ?? 'https://eventic.com',
            'order-img-url' => $_ENV['APP_LOGO'] ?? 'https://eventic.com/logo.png',
            'ipn-url' => ($_ENV['APP_URL'] ?? 'https://eventic.com') . '/ussd/main_process.php',
        ];
        
        // Build query string
        $postFields = http_build_query($expressPayData);
        
        $ch = curl_init($expressPayUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/x-www-form-urlencoded",
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        $logger->debug('ExpressPay response', [
            'http_code' => $httpCode, 
            'response' => substr($response, 0, 300),
            'curl_error' => $curlError
        ]);
        
        if ($curlError) {
            $logger->error('ExpressPay curl error', ['error' => $curlError]);
            $message = "Payment initiation failed. Please try again.";
            $continueSession = false;
            break;
        }
        
        $responseData = json_decode($response, true);
        
        // ExpressPay returns status 1 for success
        if (($responseData['status'] ?? 0) == 1 && !empty($responseData['token'])) {
            $token = $responseData['token'];
            
            // Store token in vote record for verification
            $vote->payment_token = $token;
            $vote->save();
            
            // Now initiate mobile money prompt using the token
            $mobileMoneyUrl = $isLive
                ? 'https://expresspaygh.com/api/mobile-money.php'
                : 'https://sandbox.expresspaygh.com/api/mobile-money.php';
            
            // Determine network code for ExpressPay
            $networkCode = match(strtolower($network)) {
                'mtn' => 'MTN',
                'vodafone', 'voda' => 'VOD',
                'airteltigo', 'airtel', 'tigo' => 'ATL',
                default => 'MTN'
            };
            
            $mobileMoneyData = [
                'merchant-id' => $merchantId,
                'api-key' => $apiKey,
                'token' => $token,
                'network-code' => $networkCode,
            ];
            
            $ch2 = curl_init($mobileMoneyUrl);
            curl_setopt_array($ch2, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($mobileMoneyData),
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/x-www-form-urlencoded",
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => 30,
            ]);
            
            $mmResponse = curl_exec($ch2);
            $mmError = curl_error($ch2);
            curl_close($ch2);
            
            $logger->debug('ExpressPay mobile money response', ['response' => $mmResponse, 'error' => $mmError]);
            
            $mmResponseData = json_decode($mmResponse, true);
            
            if (($mmResponseData['status'] ?? 0) == 1) {
                $message = "Payment initiated!\nCheck your phone for the mobile money prompt.\nRef: " . substr($reference, -8);
                $continueSession = false;
                $logger->info('Payment initiated successfully', ['reference' => $reference, 'token' => $token]);
            } else {
                $errorMsg = $mmResponseData['message'] ?? $mmResponseData['error-message'] ?? 'Mobile money prompt failed';
                $logger->error('Mobile money prompt failed', ['error' => $errorMsg]);
                $message = "Payment failed. Please try again.\nRef: " . substr($reference, -8);
                $continueSession = false;
            }
        } else {
            $errorMsg = $responseData['message'] ?? $responseData['error-message'] ?? 'Unknown error';
            $logger->error('ExpressPay initiation failed', ['error' => $errorMsg, 'response' => $responseData]);
            $message = "Payment failed: {$errorMsg}";
            $continueSession = false;
        }
        break;
        
    default:
        $message = "Session error. Please dial again.";
        $continueSession = false;
}

// Update session state
$currentState = [
    'sessionID' => $sessionID,
    'msisdn' => $msisdn,
    'userData' => $userData,
    'network' => $network,
    'newSession' => false,
    'message' => $message,
    'level' => $level,
];

// Merge with previous state data (nominee info, votes, etc.)
$currentState = array_merge($lastResponse, $currentState);
$userResponseTracker[] = $currentState;
$Psr16Adapter->set($sessionID, $userResponseTracker, 300); // 5-minute TTL

sendResponse($sessionID, $msisdn, $userID, $continueSession, $message, $logger);
