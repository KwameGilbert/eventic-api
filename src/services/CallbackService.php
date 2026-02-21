<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\OrganizerBalance;
use App\Models\AwardVote;
use App\Models\TicketType;
use App\Services\ActivityLogService;
use Illuminate\Database\Capsule\Manager as DB;
use Exception;
use Illuminate\Support\Carbon;

/**
 * CallbackService
 * 
 * Orchestrates the processing of payment callbacks/webhooks from providers like Kowri.
 */
class CallbackService
{
    private ActivityLogService $activityLogger;

    public function __construct(ActivityLogService $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }

    /**
     * Handle incoming webhook for Ticket Orders
     */
    public function handleOrderWebhook(array $payload): void
    {
        $status = strtoupper($payload['status'] ?? '');
        $paymentReference = $payload['orderId'] ?? null;

        if (!$paymentReference) {
            throw new Exception('Order ID missing in payload');
        }

        $order = Order::where('payment_reference', $paymentReference)->first();
        if (!$order) {
            throw new Exception("Order not found for reference: {$paymentReference}");
        }

        if ($status === 'FULFILLED') {
            if ($order->status !== Order::STATUS_PAID) {
                $this->processSuccessfulOrder($order, $paymentReference);
            }
        } else if ($status === 'UNFULFILLED_ERROR') {
            $this->handleOrderFailure($order);
        }
    }

    /**
     * Handle incoming webhook for Award Votes
     */
    public function handleVoteWebhook(array $payload): void
    {
        $status = strtoupper($payload['status'] ?? '');
        $paymentReference = $payload['orderId'] ?? null;

        if (!$paymentReference) {
            throw new Exception('Order ID missing in payload');
        }

        $vote = AwardVote::with(['category', 'award.organizer'])->where('reference', $paymentReference)->first();
        if (!$vote) {
            throw new Exception("Vote not found for reference: {$paymentReference}");
        }

        if ($vote->isPaid()) {
            return;
        }

        if ($status === 'FULFILLED') {
            $this->processSuccessfulVote($vote, $paymentReference);
        } else if ($status === 'UNFULFILLED_ERROR') {
            $vote->status = 'failed';
            $vote->save();
        }
    }

    /**
     * Internal: Process successful order (tickets, transactions, balance)
     */
    private function processSuccessfulOrder(Order $order, string $reference): void
    {
        DB::beginTransaction();
        try {
            // Update Order Status
            $order->update([
                'status' => Order::STATUS_PAID,
                'payment_reference' => $reference,
                'paid_at' => Carbon::now(),
            ]);

            // Log activity
            $this->activityLogger->log(
                $order->user_id, 
                'confirm_order', 
                'Order', 
                $order->id, 
                "Order #{$order->order_number} confirmed/paid via IPN"
            );

            // Generate Tickets and create transactions
            $orderItems = $order->items()->with('event.organizer')->get();

            foreach ($orderItems as $item) {
                // Generate tickets
                for ($i = 0; $i < $item->quantity; $i++) {
                    Ticket::create([
                        'order_id' => $order->id,
                        'event_id' => $item->event_id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'ticket_code' => Ticket::generateUniqueCode(),
                        'status' => Ticket::STATUS_ACTIVE,
                    ]);
                }

                // Create transaction and update balance
                $event = $item->event;
                if ($event && $event->organizer) {
                    $organizerId = $event->organizer->id;

                    Transaction::createTicketSale(
                        $organizerId,
                        $item->event_id,
                        $order->id,
                        $item->id,
                        (float) $item->total_price,
                        (float) $item->admin_amount,
                        (float) $item->organizer_amount,
                        (float) $item->payment_fee,
                        "Ticket sale: {$event->title}",
                        'kowri',
                        'website'
                    );

                    $balance = OrganizerBalance::getOrCreate($organizerId);
                    $balance->addPendingEarnings((float) $item->organizer_amount);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Internal: Handle order payment failure
     */
    private function handleOrderFailure(Order $order): void
    {
        if ($order->status !== Order::STATUS_PENDING) {
            return;
        }

        DB::beginTransaction();
        try {
            $order->update(['status' => Order::STATUS_FAILED]);
            
            // Release reserved tickets
            foreach ($order->items as $item) {
                $ticketType = TicketType::find($item->ticket_type_id);
                if ($ticketType) {
                    $ticketType->increment('remaining', $item->quantity);
                }
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Internal: Process successful vote (save, transaction, balance)
     */
    private function processSuccessfulVote(AwardVote $vote, string $paymentReference): void
    {
        DB::beginTransaction();
        try {
            $vote->status = 'paid';
            $vote->save();

            // Log activity
            $this->activityLogger->log(
                null,
                'confirm_vote', 
                'AwardVote', 
                $vote->id, 
                "Confirmed {$vote->number_of_votes} votes for nominee ID #{$vote->nominee_id} (Reference: {$paymentReference})"
            );

            // Create transaction and update balance
            $award = $vote->award;
            if ($award && $award->organizer) {
                $organizerId = $award->organizer->id;

                Transaction::createVotePurchase(
                    $organizerId,
                    $vote->award_id,
                    $vote->id,
                    (float) $vote->gross_amount,
                    (float) $vote->admin_amount,
                    (float) $vote->organizer_amount,
                    (float) $vote->payment_fee,
                    "Vote purchase (IPN): {$award->title}",
                    $vote->payment_method,
                    $vote->source
                );

                $balance = OrganizerBalance::getOrCreate($organizerId);
                $balance->addPendingEarnings((float) $vote->organizer_amount);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
