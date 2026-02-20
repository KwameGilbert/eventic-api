<?php

declare(strict_types=1);

/**
 * Vote Verification Job
 * 
 * Checks all pending votes with a payment token and verifies them with Kowri.
 * Run as cron job: * * * * * php /path/to/verify_votes.php
 */

require_once __DIR__ . '/src/config/Constants.php';
require_once BASE . 'vendor/autoload.php';
$app = require_once BOOTSTRAP . 'app.php';

use App\Models\AwardVote;
use App\Models\Transaction;
use App\Models\OrganizerBalance;
use App\Services\KowriService;

// Initialize Kowri service
$kowriService = new KowriService();

echo "[" . date('Y-m-d H:i:s') . "] Award Vote Verification Job Started\n";
echo str_repeat('-', 50) . "\n";

// Fetch pending votes with a payment token
// Only process those created at least 2 minutes ago to avoid race conditions 
// with immediate user redirects/callbacks
$pendingVotes = AwardVote::where('status', 'pending')
    ->whereNotNull('payment_token')
    ->where('created_at', '<=', date('Y-m-d H:i:s', strtotime('-2 minutes')))
    ->with(['award.organizer'])
    ->get();

if ($pendingVotes->isEmpty()) {
    echo "No pending votes requiring verification.\n";
} else {
    echo "Found " . $pendingVotes->count() . " pending votes to verify.\n";

    $count = 0;
    foreach ($pendingVotes as $vote) {
        try {
            echo "Processing Vote ID: {$vote->id} (Ref: {$vote->reference})... ";
            
            $paymentStatus = $kowriService->queryTransaction($vote->payment_token);
            
            if ($paymentStatus['status'] === 'paid') {
                // Double check if already paid by some reason
                if ($vote->status === 'paid') {
                    echo "ALREADY PAID\n";
                    continue;
                }

                // Mark as paid
                $vote->status = 'paid';
                $vote->save();
                
                echo "SUCCESS (Paid)\n";
                
                // Perform side effects
                $award = $vote->award;
                if ($award && $award->organizer) {
                    $organizerId = $award->organizer->id;

                    // Create transaction if it doesn't exist
                    $existingTransaction = Transaction::where('vote_id', $vote->id)->first();
                    if (!$existingTransaction) {
                        Transaction::createVotePurchase(
                            $organizerId,
                            $vote->award_id,
                            $vote->id,
                            (float) $vote->gross_amount,
                            (float) $vote->admin_amount,
                            (float) $vote->organizer_amount,
                            (float) $vote->payment_fee,
                            "Vote purchase (Verified): {$award->title}",
                            'kowri',
                            'website'
                        );

                        // Update organizer balance
                        $balance = OrganizerBalance::getOrCreate($organizerId);
                        $balance->addPendingEarnings((float) $vote->organizer_amount);
                        echo "  - Transaction created and balance updated.\n";
                    }
                }
                $count++;
            } else {
                echo "STILL PENDING (" . ($paymentStatus['raw']['message'] ?? 'No message') . ")\n";
                
                // If it's very old (e.g. > 24 hours), we might want to mark it as failed/expired
                if (strtotime($vote->created_at->toDateTimeString()) < strtotime('-24 hours')) {
                    $vote->status = 'failed';
                    $vote->save();
                    echo "  - Marked as FAILED (Over 24 hours old)\n";
                }
            }
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }
    }
}

echo str_repeat('-', 50) . "\n";
echo "Job finished. Verified and updated {$count} votes.\n";
