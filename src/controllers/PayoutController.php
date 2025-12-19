<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helper\ResponseHelper;
use App\Models\PayoutRequest;
use App\Models\OrganizerBalance;
use App\Models\Organizer;
use App\Models\Event;
use App\Models\Award;
use App\Models\PlatformSetting;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Carbon;
use Exception;

/**
 * PayoutController
 * 
 * Handles payout requests for organizers and payout management for admins
 */
class PayoutController
{
    /**
     * Get payout request history for current organizer
     * GET /v1/organizer/payouts
     */
    public function getOrganizerPayouts(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $organizer = Organizer::where('user_id', $user->id)->first();

            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer not found', 404);
            }

            $queryParams = $request->getQueryParams();
            $status = $queryParams['status'] ?? null;
            $type = $queryParams['type'] ?? null;
            $limit = min((int) ($queryParams['limit'] ?? 20), 100);
            $page = max((int) ($queryParams['page'] ?? 1), 1);

            $query = PayoutRequest::where('organizer_id', $organizer->id)
                ->with(['event', 'award'])
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, ['pending', 'processing', 'completed', 'rejected'])) {
                $query->where('status', $status);
            }

            if ($type && in_array($type, ['event', 'award'])) {
                $query->where('payout_type', $type);
            }

            $payouts = $query->paginate($limit, ['*'], 'page', $page);

            $data = [
                'payouts' => $payouts->items(),
                'pagination' => [
                    'current_page' => $payouts->currentPage(),
                    'per_page' => $payouts->perPage(),
                    'total' => $payouts->total(),
                    'last_page' => $payouts->lastPage(),
                ],
            ];

            return ResponseHelper::success($response, 'Payouts fetched successfully', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch payouts', 500, $e->getMessage());
        }
    }

    /**
     * Get balance summary for current organizer
     * GET /v1/organizer/balance
     */
    public function getOrganizerBalance(Request $request, Response $response): Response
    {
        try {
            $user = $request->getAttribute('user');
            $organizer = Organizer::where('user_id', $user->id)->first();

            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer not found', 404);
            }

            // Get or create balance record
            $balance = OrganizerBalance::getOrCreate($organizer->id);

            // Recalculate to ensure accuracy
            $balance->recalculateFromTransactions();

            $data = [
                'available_balance' => (float) $balance->available_balance,
                'pending_balance' => (float) $balance->pending_balance,
                'total_earned' => (float) $balance->total_earned,
                'total_withdrawn' => (float) $balance->total_withdrawn,
                'can_request_payout' => $balance->canRequestPayout(),
                'min_payout_amount' => PlatformSetting::getMinPayoutAmount(),
                'payout_hold_days' => PlatformSetting::getPayoutHoldDays(),
                'last_payout_at' => $balance->last_payout_at?->toIso8601String(),
            ];

            return ResponseHelper::success($response, 'Balance fetched successfully', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch balance', 500, $e->getMessage());
        }
    }

    /**
     * Request a payout for an event
     * POST /v1/organizer/payouts/events/{eventId}
     */
    public function requestEventPayout(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $eventId = (int) $args['eventId'];
            $data = $request->getParsedBody();

            $organizer = Organizer::where('user_id', $user->id)->first();
            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer not found', 404);
            }

            // Verify event ownership
            $event = Event::where('id', $eventId)
                ->where('organizer_id', $organizer->id)
                ->first();

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found or not owned by you', 404);
            }

            // Validate required fields
            if (empty($data['payment_method']) || empty($data['account_number']) || empty($data['account_name'])) {
                return ResponseHelper::error($response, 'Payment method, account number, and account name are required', 400);
            }

            // Check for pending payout requests
            $pendingRequest = PayoutRequest::where('organizer_id', $organizer->id)
                ->where('event_id', $eventId)
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if ($pendingRequest) {
                return ResponseHelper::error($response, 'You already have a pending payout request for this event', 400);
            }

            // Get balance
            $balance = OrganizerBalance::getOrCreate($organizer->id);

            if (!$balance->canRequestPayout()) {
                $minAmount = PlatformSetting::getMinPayoutAmount();
                return ResponseHelper::error($response, "Minimum payout amount is GHS {$minAmount}", 400);
            }

            // Calculate available amount for this event (simplified - in production you'd track per-event)
            $availableAmount = $balance->available_balance;

            $requestedAmount = (float) ($data['amount'] ?? $availableAmount);

            if ($requestedAmount > $availableAmount) {
                return ResponseHelper::error($response, 'Requested amount exceeds available balance', 400);
            }

            if ($requestedAmount < PlatformSetting::getMinPayoutAmount()) {
                return ResponseHelper::error($response, 'Amount below minimum payout threshold', 400);
            }

            // Create payout request
            $payout = PayoutRequest::create([
                'organizer_id' => $organizer->id,
                'event_id' => $eventId,
                'payout_type' => PayoutRequest::TYPE_EVENT,
                'amount' => $requestedAmount,
                'gross_amount' => $requestedAmount,
                'admin_fee' => 0, // No additional fee for payout
                'payment_method' => $data['payment_method'],
                'account_number' => $data['account_number'],
                'account_name' => $data['account_name'],
                'bank_name' => $data['bank_name'] ?? null,
                'status' => PayoutRequest::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            return ResponseHelper::success($response, 'Payout request submitted successfully', [
                'payout_id' => $payout->id,
                'amount' => $requestedAmount,
                'status' => 'pending',
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to request payout', 500, $e->getMessage());
        }
    }

    /**
     * Request a payout for an award
     * POST /v1/organizer/payouts/awards/{awardId}
     */
    public function requestAwardPayout(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $awardId = (int) $args['awardId'];
            $data = $request->getParsedBody();

            $organizer = Organizer::where('user_id', $user->id)->first();
            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer not found', 404);
            }

            // Verify award ownership
            $award = Award::where('id', $awardId)
                ->where('organizer_id', $organizer->id)
                ->first();

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found or not owned by you', 404);
            }

            // Validate required fields
            if (empty($data['payment_method']) || empty($data['account_number']) || empty($data['account_name'])) {
                return ResponseHelper::error($response, 'Payment method, account number, and account name are required', 400);
            }

            // Check for pending payout requests
            $pendingRequest = PayoutRequest::where('organizer_id', $organizer->id)
                ->where('award_id', $awardId)
                ->whereIn('status', ['pending', 'processing'])
                ->first();

            if ($pendingRequest) {
                return ResponseHelper::error($response, 'You already have a pending payout request for this award', 400);
            }

            // Get balance
            $balance = OrganizerBalance::getOrCreate($organizer->id);

            if (!$balance->canRequestPayout()) {
                $minAmount = PlatformSetting::getMinPayoutAmount();
                return ResponseHelper::error($response, "Minimum payout amount is GHS {$minAmount}", 400);
            }

            $availableAmount = $balance->available_balance;
            $requestedAmount = (float) ($data['amount'] ?? $availableAmount);

            if ($requestedAmount > $availableAmount) {
                return ResponseHelper::error($response, 'Requested amount exceeds available balance', 400);
            }

            if ($requestedAmount < PlatformSetting::getMinPayoutAmount()) {
                return ResponseHelper::error($response, 'Amount below minimum payout threshold', 400);
            }

            // Create payout request
            $payout = PayoutRequest::create([
                'organizer_id' => $organizer->id,
                'award_id' => $awardId,
                'payout_type' => PayoutRequest::TYPE_AWARD,
                'amount' => $requestedAmount,
                'gross_amount' => $requestedAmount,
                'admin_fee' => 0,
                'payment_method' => $data['payment_method'],
                'account_number' => $data['account_number'],
                'account_name' => $data['account_name'],
                'bank_name' => $data['bank_name'] ?? null,
                'status' => PayoutRequest::STATUS_PENDING,
                'notes' => $data['notes'] ?? null,
            ]);

            return ResponseHelper::success($response, 'Payout request submitted successfully', [
                'payout_id' => $payout->id,
                'amount' => $requestedAmount,
                'status' => 'pending',
            ], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to request payout', 500, $e->getMessage());
        }
    }

    /**
     * Cancel a pending payout request
     * POST /v1/organizer/payouts/{payoutId}/cancel
     */
    public function cancelPayout(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $payoutId = (int) $args['payoutId'];

            $organizer = Organizer::where('user_id', $user->id)->first();
            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer not found', 404);
            }

            $payout = PayoutRequest::where('id', $payoutId)
                ->where('organizer_id', $organizer->id)
                ->first();

            if (!$payout) {
                return ResponseHelper::error($response, 'Payout request not found', 404);
            }

            if (!$payout->canBeCancelled()) {
                return ResponseHelper::error($response, 'This payout request cannot be cancelled', 400);
            }

            $payout->update(['status' => 'cancelled']);

            return ResponseHelper::success($response, 'Payout request cancelled successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to cancel payout', 500, $e->getMessage());
        }
    }

    // ==================== Admin Methods ====================

    /**
     * Verify user has admin role
     */
    private function verifyAdminRole(Request $request, Response $response): ?Response
    {
        $user = $request->getAttribute('user');
        if (!$user || $user->role !== 'admin') {
            return ResponseHelper::error($response, 'Unauthorized: Admin access required', 403);
        }
        return null;
    }

    /**
     * Get all payout requests (admin only)
     * GET /v1/admin/payouts
     */
    public function getAllPayouts(Request $request, Response $response): Response
    {
        // Verify admin role
        $authResult = $this->verifyAdminRole($request, $response);
        if ($authResult) {
            return $authResult;
        }

        try {
            $queryParams = $request->getQueryParams();
            $status = $queryParams['status'] ?? null;
            $type = $queryParams['type'] ?? null;
            $limit = min((int) ($queryParams['limit'] ?? 20), 100);
            $page = max((int) ($queryParams['page'] ?? 1), 1);

            $query = PayoutRequest::with(['organizer.user', 'event', 'award', 'processor'])
                ->orderBy('created_at', 'desc');

            if ($status && in_array($status, ['pending', 'processing', 'completed', 'rejected'])) {
                $query->where('status', $status);
            }

            if ($type && in_array($type, ['event', 'award'])) {
                $query->where('payout_type', $type);
            }

            $payouts = $query->paginate($limit, ['*'], 'page', $page);

            $data = [
                'payouts' => collect($payouts->items())->map(function ($payout) {
                    return [
                        'id' => $payout->id,
                        'organizer' => $payout->organizer ? [
                            'id' => $payout->organizer->id,
                            'name' => $payout->organizer->organization_name,
                            'email' => $payout->organizer->user->email ?? null,
                        ] : null,
                        'source_name' => $payout->getSourceName(),
                        'payout_type' => $payout->payout_type,
                        'amount' => (float) $payout->amount,
                        'payment_method' => $payout->getPaymentMethodLabel(),
                        'account_number' => $payout->account_number,
                        'account_name' => $payout->account_name,
                        'bank_name' => $payout->bank_name,
                        'status' => $payout->status,
                        'status_label' => $payout->getStatusLabel(),
                        'processed_by' => $payout->processor?->name,
                        'processed_at' => $payout->processed_at?->toIso8601String(),
                        'rejection_reason' => $payout->rejection_reason,
                        'notes' => $payout->notes,
                        'created_at' => $payout->created_at->toIso8601String(),
                    ];
                }),
                'pagination' => [
                    'current_page' => $payouts->currentPage(),
                    'per_page' => $payouts->perPage(),
                    'total' => $payouts->total(),
                    'last_page' => $payouts->lastPage(),
                ],
            ];

            return ResponseHelper::success($response, 'Payouts fetched successfully', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch payouts', 500, $e->getMessage());
        }
    }

    /**
     * Approve a payout request (admin only)
     * POST /v1/admin/payouts/{payoutId}/approve
     */
    public function approvePayout(Request $request, Response $response, array $args): Response
    {
        // Verify admin role
        $authResult = $this->verifyAdminRole($request, $response);
        if ($authResult) {
            return $authResult;
        }

        try {
            $user = $request->getAttribute('user');
            $payoutId = (int) $args['payoutId'];
            $data = $request->getParsedBody();

            $payout = PayoutRequest::find($payoutId);

            if (!$payout) {
                return ResponseHelper::error($response, 'Payout request not found', 404);
            }

            if (!$payout->approve($user->id, $data['notes'] ?? null)) {
                return ResponseHelper::error($response, 'Payout request cannot be approved', 400);
            }

            return ResponseHelper::success($response, 'Payout request approved successfully', [
                'payout_id' => $payout->id,
                'status' => $payout->status,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to approve payout', 500, $e->getMessage());
        }
    }

    /**
     * Reject a payout request (admin only)
     * POST /v1/admin/payouts/{payoutId}/reject
     */
    public function rejectPayout(Request $request, Response $response, array $args): Response
    {
        // Verify admin role
        $authResult = $this->verifyAdminRole($request, $response);
        if ($authResult) {
            return $authResult;
        }

        try {
            $user = $request->getAttribute('user');
            $payoutId = (int) $args['payoutId'];
            $data = $request->getParsedBody();

            if (empty($data['reason'])) {
                return ResponseHelper::error($response, 'Rejection reason is required', 400);
            }

            $payout = PayoutRequest::find($payoutId);

            if (!$payout) {
                return ResponseHelper::error($response, 'Payout request not found', 404);
            }

            if (!$payout->reject($user->id, $data['reason'])) {
                return ResponseHelper::error($response, 'Payout request cannot be rejected', 400);
            }

            return ResponseHelper::success($response, 'Payout request rejected successfully', [
                'payout_id' => $payout->id,
                'status' => $payout->status,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reject payout', 500, $e->getMessage());
        }
    }

    /**
     * Mark a payout as completed (admin only)
     * POST /v1/admin/payouts/{payoutId}/complete
     */
    public function completePayout(Request $request, Response $response, array $args): Response
    {
        // Verify admin role
        $authResult = $this->verifyAdminRole($request, $response);
        if ($authResult) {
            return $authResult;
        }

        try {
            $payoutId = (int) $args['payoutId'];
            $data = $request->getParsedBody();

            $payout = PayoutRequest::find($payoutId);

            if (!$payout) {
                return ResponseHelper::error($response, 'Payout request not found', 404);
            }

            if (!$payout->markCompleted($data['notes'] ?? null)) {
                return ResponseHelper::error($response, 'Payout is not in processing status', 400);
            }

            return ResponseHelper::success($response, 'Payout completed successfully', [
                'payout_id' => $payout->id,
                'status' => $payout->status,
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to complete payout', 500, $e->getMessage());
        }
    }

    /**
     * Get payout summary statistics (admin only)
     * GET /v1/admin/payouts/summary
     */
    public function getPayoutSummary(Request $request, Response $response): Response
    {
        // Verify admin role
        $authResult = $this->verifyAdminRole($request, $response);
        if ($authResult) {
            return $authResult;
        }

        try {
            $pendingCount = PayoutRequest::where('status', 'pending')->count();
            $processingCount = PayoutRequest::where('status', 'processing')->count();
            $completedCount = PayoutRequest::where('status', 'completed')->count();
            $rejectedCount = PayoutRequest::where('status', 'rejected')->count();

            $pendingAmount = PayoutRequest::where('status', 'pending')->sum('amount');
            $processingAmount = PayoutRequest::where('status', 'processing')->sum('amount');
            $completedAmount = PayoutRequest::where('status', 'completed')->sum('amount');

            $data = [
                'counts' => [
                    'pending' => $pendingCount,
                    'processing' => $processingCount,
                    'completed' => $completedCount,
                    'rejected' => $rejectedCount,
                    'total' => $pendingCount + $processingCount + $completedCount + $rejectedCount,
                ],
                'amounts' => [
                    'pending' => (float) $pendingAmount,
                    'processing' => (float) $processingAmount,
                    'completed' => (float) $completedAmount,
                ],
            ];

            return ResponseHelper::success($response, 'Payout summary fetched successfully', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch payout summary', 500, $e->getMessage());
        }
    }
}
