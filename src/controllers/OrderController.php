<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\OrganizerBalance;
use App\Services\ActivityLogService;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;
use Exception;

/**
 * OrderController
 * Handles order creation, payment processing, and Kowri webhook verification
 */
class OrderController
{
    private \App\Services\KowriService $kowriService;
    private ActivityLogService $activityLogger;

    public function __construct(ActivityLogService $activityLogger)
    {
        $this->kowriService = new \App\Services\KowriService();
        $this->activityLogger = $activityLogger;
    }

    /**
     * Create a new order
     * POST /v1/orders
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        DB::beginTransaction();
        try {
            $data = $request->getParsedBody();
            $tokenUser = $request->getAttribute('user');
            
            // Fetch full user data from database (JWT only contains id, email, role, status)
            $user = User::find($tokenUser->id);
            
            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 401);
            }
            
            $isPos = $user->role === 'pos';

            if (empty($data['items']) || !is_array($data['items'])) {
                return ResponseHelper::error($response, 'Items array is required', 400);
            }

            $totalAmount = 0;
            $dynamicFeesTotal = 0;
            $orderItemsData = [];

            foreach ($data['items'] as $item) {
                if (empty($item['ticket_type_id']) || empty($item['quantity'])) {
                    throw new Exception('Invalid item format');
                }

                $ticketType = TicketType::find($item['ticket_type_id']);
                if (!$ticketType || !$ticketType->isActive()) {
                    throw new Exception("Ticket type {$item['ticket_type_id']} is not active");
                }
                
                // POS Assignment Check
                if ($isPos) {
                     $assigned = \App\Models\PosAssignment::where('user_id', $user->id)
                                              ->where('event_id', $ticketType->event_id)
                                              ->exists();
                     if (!$assigned) {
                          throw new Exception("POS user is not assigned to sell tickets for event {$ticketType->event_id}");
                     }
                }

                if ($ticketType->remaining < $item['quantity']) {
                    throw new Exception("Not enough tickets remaining for {$ticketType->name}");
                }

                // Automatic Price Switching (Sale Price vs Regular Price)
                $baseUnitPrice = $ticketType->getCurrentPrice();
                $effectiveUnitPrice = $ticketType->getEffectivePrice();
                $dynamicMarkupPerTicket = $ticketType->getDynamicFeeAmount();
                
                $itemTotal = $effectiveUnitPrice * $item['quantity'];
                $baseTotalForItem = $baseUnitPrice * $item['quantity'];
                $totalAmount += $itemTotal;
                
                // Track dynamic fees separately if needed for accounting
                $dynamicFeesTotal += $dynamicMarkupPerTicket * $item['quantity'];

                // Get event for revenue share calculation
                $event = Event::find($ticketType->event_id);
                $adminSharePercent = $event ? $event->getAdminSharePercent() : 10;
                $organizerSharePercent = 100 - $adminSharePercent;
                
                // Revenue split based on BASE price
                $organizerAmount = round($baseTotalForItem * ($organizerSharePercent / 100), 2);
                
                // Admin gets: (Base * AdminShare%) + (Dynamic Markup Amount)
                // Note: Admin will absorb the payment fee from their total share at the end of the order summary
                // But for the OrderItem, we calculate the gross share
                $adminGrossAmount = $itemTotal - $organizerAmount;

                $orderItemsData[] = [
                    'event_id' => $ticketType->event_id,
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $effectiveUnitPrice,
                    'total_price' => $itemTotal,
                    'admin_share_percent' => $adminSharePercent,
                    'admin_amount' => $adminGrossAmount, // Gross for now, fee subtracted later
                    'organizer_amount' => $organizerAmount,
                    'payment_fee' => 0, // Will calculate on the whole order
                ];
            }

            // Calculate final fees (1.5% Payment fee)
            // The dynamic markup is already IN the totalAmount (subtotal)
            $paymentFee = round($totalAmount * 0.015, 2);
            $grandTotal = $totalAmount + $paymentFee;

            // Subtract payment fee from AdminAmount for each item (pro-rata)
            foreach ($orderItemsData as &$itemData) {
                $itemProportion = $itemData['total_price'] / $totalAmount;
                $itemFee = round($paymentFee * $itemProportion, 2);
                $itemData['payment_fee'] = $itemFee;
                $itemData['admin_amount'] = round($itemData['admin_amount'] - $itemFee, 2);
            }

            // Create Order
            $orderData = [
                'user_id' => $user->id,
                'subtotal' => $totalAmount,
                'fees' => $paymentFee,
                'total_amount' => $grandTotal,
                'status' => Order::STATUS_PENDING,
                'customer_email' => $data['customer_email'] ?? $user->email,
                'customer_name' => $data['customer_name'] ?? $user->name,
                'customer_phone' => $data['customer_phone'] ?? $user->phone,
            ];
            
            if ($isPos) {
                $orderData['pos_user_id'] = $user->id;
            }

            $order = Order::create($orderData);

            // Create Order Items
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
                
                // Reserve tickets (decrement remaining)
                $ticketType = TicketType::find($itemData['ticket_type_id']);
                $ticketType->decrement('remaining', $itemData['quantity']);
            }

            DB::commit();

            // Generate unique reference
            $reference = 'EVT-' . $order->id . '-' . time();
            $order->update(['payment_reference' => $reference]);

            // Log activity
            $this->activityLogger->logCreate(
                $user->id, 
                'Order', 
                $order->id, 
                $order->toArray(), 
                "Created order: {$order->order_number} for amount: {$order->total_amount}"
            );

            return ResponseHelper::success($response, 'Order created successfully. Proceed to payment.', [
                'order_id' => $order->id,
                'reference' => $reference,
                'subtotal' => $totalAmount,
                'fees' => $paymentFee,
                'total_amount' => $grandTotal,
                'status' => $order->status,
                'is_pos' => $isPos,
                'customer_email' => $order->customer_email,
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($response, $e->getMessage(), 400);
        }
    }

    /**
     * Initialize Kowri payment
     * POST /v1/orders/{id}/pay
     */
    public function initializePayment(Request $request, Response $response, array $args): Response
    {
        try {
            $orderId = $args['id'];
            $user = $request->getAttribute('user');
            
            $order = Order::find($orderId);
            
            if (!$order) {
                return ResponseHelper::error($response, 'Order not found', 404);
            }

            if ($order->user_id !== $user->id) {
                return ResponseHelper::error($response, 'Unauthorized', 403);
            }

            if ($order->status === Order::STATUS_PAID) {
                return ResponseHelper::error($response, 'Order is already paid', 400);
            }

            $data = $request->getParsedBody();
            $network = strtoupper($data['network'] ?? '');
            $isCard = ($network === 'CARD');

            // Initialize Kowri payment
            $paymentData = [
                'amount' => $order->total_amount,
                'order_id' => $order->payment_reference,
                'email' => $order->customer_email,
                'name' => $order->customer_name,
                'phone' => $data['phone'] ?? $order->customer_phone,
                'description' => "Order #{$order->id} payment",
                'webhook_url' => $_ENV['APP_URL'] . '/v1/payment/webhook',
                'redirect_url' => $_ENV['FRONTEND_URL'] . '/payment/verify?order_id=' . $order->id,
                'network' => $network
            ];

            if ($isCard) {
                // Card payments MUST use checkout URL
                $kowriResponse = $this->kowriService->createInvoice($paymentData);
                $tokenValue = $kowriResponse['pay_token'] ?? null;
                $checkoutUrl = $kowriResponse['raw']['result']['checkoutUrl'] ?? null;
            } else {
                // MoMo payments use direct prompt
                $kowriResponse = $this->kowriService->payNow($paymentData);
                $tokenValue = $kowriResponse['token'] ?? null;
                $checkoutUrl = null;
            }

            if (!$kowriResponse['success']) {
                return ResponseHelper::error($response, 'Payment initialization failed', 400);
            }

            // Save the pay_token/transaction_id generated by Kowri
            if ($tokenValue) {
                $order->update(['payment_token' => $tokenValue]);
            }

            return ResponseHelper::success($response, 'Payment initialized', [
                'pay_token' => $tokenValue,
                'reference' => $order->payment_reference,
                'amount' => $order->total_amount,
                'currency' => 'GHS',
                'status' => $kowriResponse['status'] ?? 'pending',
                'checkout_url' => $checkoutUrl,
                'mode' => $isCard ? 'hosted' : 'direct'
            ]);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Payment initialization failed', 500, $e->getMessage());
        }
    }

    /**
     * Verify Kowri payment
     * GET /v1/orders/{id}/verify
     */
    public function verifyPayment(Request $request, Response $response, array $args): Response
    {
        try {
            $orderId = $args['id'];
            $order = Order::find($orderId);
            
            if (!$order) {
                return ResponseHelper::error($response, 'Order not found', 404);
            }

            if (empty($order->payment_token)) {
                return ResponseHelper::error($response, 'Payment token not found for this order', 400);
            }

            // Verify with Kowri
            $verification = $this->kowriService->queryTransaction($order->payment_token);

            if ($verification['status'] === 'paid') {
                // Payment successful - process the order
                $this->processSuccessfulPayment($order, $order->payment_reference);

                return ResponseHelper::success($response, 'Payment verified successfully', [
                    'order_id' => $order->id,
                    'status' => 'paid',
                    'amount_paid' => $verification['amount'],
                ]);
            } else {
                return ResponseHelper::error($response, 'Payment was not successful', 400, [
                    'payment_status' => $verification['status'],
                    'raw_status' => $verification['raw_status'],
                ]);
            }

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Payment verification failed', 500, $e->getMessage());
        }
    }

    /**
     * Kowri Webhook Handler
     * POST /v1/payment/webhook
     */
    public function handleWebhook(Request $request, Response $response): Response
    {
        try {
            $payload = $request->getParsedBody();
            
            if (empty($payload)) {
                $payload = json_decode(file_get_contents('php://input'), true);
            }

            if (empty($payload) || !isset($payload['status'])) {
                return ResponseHelper::error($response, 'Invalid payload', 400);
            }

            // status: FULFILLED or UNFULFILLED_ERROR
            $status = strtoupper($payload['status']);
            $orderId = $payload['orderId'] ?? null;
            $transactionId = $payload['transactionId'] ?? null;

            if (!$orderId) {
                return ResponseHelper::error($response, 'Order ID missing', 400);
            }

            $order = Order::where('payment_reference', $orderId)->first();
            
            if (!$order) {
                return ResponseHelper::error($response, 'Order not found', 404);
            }

            if ($status === 'FULFILLED') {
                if ($order->status !== Order::STATUS_PAID) {
                    $this->processSuccessfulPayment($order, $orderId);
                }
            } else if ($status === 'UNFULFILLED_ERROR') {
                $this->handleChargeFailed($payload);
            }

            return ResponseHelper::success($response, 'Webhook processed', [], 200);

        } catch (Exception $e) {
            error_log('Kowri Webhook Error: ' . $e->getMessage());
            return ResponseHelper::error($response, 'Webhook processing failed', 500);
        }
    }

    /**
     * Handle failed charge event
     */
    private function handleChargeFailed(array $data): void
    {
        $reference = $data['orderId'] ?? null;
        
        if (!$reference) {
            return;
        }

        $order = Order::where('payment_reference', $reference)->first();
        
        if (!$order || $order->status !== Order::STATUS_PENDING) {
            return;
        }

        // Mark order as failed and release tickets
        DB::beginTransaction();
        try {
            $order->update([
                'status' => Order::STATUS_FAILED,
            ]);

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
            error_log('Failed to process charge failure: ' . $e->getMessage());
        }
    }

    /**
     * Process successful payment and generate tickets
     */
    private function processSuccessfulPayment(Order $order, string $reference): void
    {
        DB::beginTransaction();
        try {
            // Update Order Status
            $order->update([
                'status' => Order::STATUS_PAID,
                'payment_reference' => $reference,
                'paid_at' => \Illuminate\Support\Carbon::now(),
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
                        'attendee_id' => null,
                    ]);
                }

                // Create transaction record for this order item
                $event = $item->event;
                if ($event && $event->organizer) {
                    $organizerId = $event->organizer->id;

                    // Create transaction
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

                    // Update organizer balance (add to pending)
                    $balance = OrganizerBalance::getOrCreate($organizerId);
                    $balance->addPendingEarnings((float) $item->organizer_amount);
                }
            }

            DB::commit();
            error_log('=== ORDER PAYMENT PROCESSED - SENDING EMAIL ===');
            error_log('Order ID: ' . $order->id);

            // Send confirmation email with tickets
            try {
                error_log('Creating EmailService...');
                $emailService = new \App\Services\EmailService();
                
                // Reload order with items for email
                error_log('Loading order relationships...');
                $order->load(['items.event', 'items.ticketType', 'tickets']);
                error_log('Items loaded: ' . $order->items->count());
                
                error_log('Calling sendTicketConfirmationEmail...');
                $result = $emailService->sendTicketConfirmationEmail($order);
                error_log('Email send result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            } catch (\Exception $e) {
                // Log but don't fail - email is not critical
                error_log('Failed to send ticket confirmation email: ' . $e->getMessage());
                error_log('Exception trace: ' . $e->getTraceAsString());
            }

        } catch (Exception $e) {
            DB::rollBack();
            error_log('Failed to process successful payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get user orders
     * GET /v1/orders
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $orders = Order::where('user_id', $user->id)
                ->with(['items.ticketType.event', 'tickets'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->toArray();
            
            return ResponseHelper::success($response, 'Orders fetched successfully', $orders);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch orders', 500, $e->getMessage());
        }
    }

    /**
     * Get single order
     * GET /v1/orders/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $order = Order::where('id', $args['id'])
                ->where('user_id', $user->id)
                ->with(['items.ticketType.event', 'tickets.ticketType', 'tickets.event'])
                ->first();
            
            if (!$order) {
                return ResponseHelper::error($response, 'Order not found', 404);
            }
            
            return ResponseHelper::success($response, 'Order fetched successfully', $order->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch order', 500, $e->getMessage());
        }
    }

    /**
     * Cancel a pending order
     * POST /v1/orders/{id}/cancel
     */
    public function cancel(Request $request, Response $response, array $args): Response
    {
        DB::beginTransaction();
        try {
            $tokenUser = $request->getAttribute('user');
            $orderId = $args['id'];
            
            // Fetch full user
            $user = User::find($tokenUser->id);
            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 401);
            }
            
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();
            
            if (!$order) {
                return ResponseHelper::error($response, 'Order not found', 404);
            }
            
            // Only pending orders can be cancelled
            if ($order->status !== Order::STATUS_PENDING) {
                return ResponseHelper::error($response, 'Only pending orders can be cancelled', 400);
            }
            
            // Restore ticket quantities
            $orderItems = OrderItem::where('order_id', $order->id)->get();
            foreach ($orderItems as $item) {
                $ticketType = TicketType::find($item->ticket_type_id);
                if ($ticketType) {
                    $ticketType->increment('remaining', $item->quantity);
                }
            }
            
            // Update order status to cancelled
            $order->update(['status' => Order::STATUS_CANCELLED]);
            
            DB::commit();

            // Log activity
            $this->activityLogger->log(
                $user->id, 
                'cancel', 
                'Order', 
                $order->id, 
                "Cancelled order: {$order->order_number}"
            );

            return ResponseHelper::success($response, 'Order cancelled successfully', [
                'order_id' => $order->id,
                'status' => 'cancelled',
            ]);
            
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($response, 'Failed to cancel order', 500, $e->getMessage());
        }
    }

}
