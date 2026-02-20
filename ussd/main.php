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

// Extract session data (Strict Arkesel Spec)
$sessionID = $data['sessionID'] ?? '';
$userID = $data['userID'] ?? '';
$newSession = $data['newSession'] ?? false;
$msisdn = $data['msisdn'] ?? '';
$userData = trim($data['userData'] ?? '');
$network = $data['network'] ?? 'MTN';

// Get contact info from environment
$contactPhone = $_ENV['USSD_CONTACT_PHONE'] ?? '+233541436414';
$contactEmail = $_ENV['USSD_CONTACT_EMAIL'] ?? 'support@eventic.com';

/**
 * Send USSD response and exit
 */
function sendResponse(string $sessionID, string $msisdn, string $userID, bool $continueSession, string $message, UssdLogger $logger): void
{
    // Strict Arkesel Response Schema
    $response = [
        'sessionID' => $sessionID,
        'userID' => $userID,
        'msisdn' => $msisdn,
        'message' => $message,
        'continueSession' => $continueSession,
    ];
    
    $logger->logResponse($response);
    
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
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
        
        // Generate payment reference (Shortened for provider limits)
        $shortSession = substr(preg_replace('/[^A-Za-z0-0]/', '', $sessionID), -4);
        $reference = 'V' . $shortSession . time(); 
        
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
                'payment_method' => 'mobile_money',
                'source' => 'ussd',
            ]);
            
            $logger->info('Vote record created', ['vote_id' => $vote->id, 'reference' => $reference]);
        } catch (Exception $e) {
            $logger->error('Failed to create vote record', ['error' => $e->getMessage()]);
            $message = "System error. Please try again.";
            $continueSession = false;
            break;
        }
        
        // Initiate payment with Kowri
        $kowriService = new \App\Services\KowriService();
        
        $nomineeName = $lastResponse['nomineeName'] ?? 'Nominee';
        $webhookUrl = ($_ENV['APP_URL'] ?? 'https://app.eventic.com') . '/v1/votes/ipn';
        
        // Network mapping (KowriService handles mapping to provider)
        $paymentData = [
            'amount' => $totalCost,
            'currency' => 'GHS',
            'order_id' => $reference,
            'email' => "ussd_{$sessionID}@eventic.com",
            'name' => "USSD Voter",
            'phone' => ltrim($msisdn, '+'),
            'description' => "{$votes} votes for {$nomineeName}",
            'network' => $network,
            'webhook_url' => $webhookUrl
        ];

        try {
            $kowriResponse = $kowriService->payNow($paymentData);
            
            if ($kowriResponse['success']) {
                $token = $kowriResponse['token'];
                
                // Store token in vote record
                $vote->payment_token = $token;
                $vote->save();
                
                $message = "Payment initiated!\nCheck your phone for the mobile money prompt.\n{$votes} vote(s) for {$nomineeName}\nRef: " . substr($reference, -8);
                $continueSession = false;
                $logger->info('Kowri MoMo payment initiated successfully', [
                    'reference' => $reference, 
                    'token' => $token,
                ]);
            } else {
                $errorMsg = $kowriResponse['message'] ?? 'Mobile money charge failed';
                $logger->error('Kowri direct charge failed', ['response' => $kowriResponse]);
                $message = "Payment failed: {$errorMsg}\nRef: " . substr($reference, -8);
                $continueSession = false;
            }
        } catch (Exception $e) {
            $logger->error('Kowri integration error', ['error' => $e->getMessage()]);
            
            // Check if it's a Kowri-specific error message we can show the user
            $errorMsg = $e->getMessage();
            if (strpos($errorMsg, 'Kowri PayNow Error:') !== false) {
                $displayMsg = str_replace('Kowri PayNow Error: ', '', $errorMsg);
                // Clean up any JSON if it was encoded
                if (strpos($displayMsg, '{') === 0) {
                    $jsonError = json_decode($displayMsg, true);
                    $displayMsg = $jsonError['message'] ?? $jsonError['statusMessage'] ?? 'Payment failed';
                }
                $message = $displayMsg;
            } else {
                $message = "System error. Please try again later.";
            }
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
