<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Capsule\Manager as DB;
use Exception;

/**
 * OrderController
 * Handles order creation and payment processing
 */
class OrderController
{
    /**
     * Create a new order
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        DB::beginTransaction();
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user'); // Assuming AuthMiddleware sets this
            $isPos = $user->role === 'pos';

            if (empty($data['items']) || !is_array($data['items'])) {
                return ResponseHelper::error($response, 'Items array is required', 400);
            }

            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($data['items'] as $item) {
                if (empty($item['ticket_type_id']) || empty($item['quantity'])) {
                    throw new Exception('Invalid item format');
                }

                $ticketType = TicketType::find($item['ticket_type_id']);
                if (!$ticketType || !$ticketType->isAvailable()) {
                    throw new Exception("Ticket type {$item['ticket_type_id']} is not available");
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

                $itemTotal = $ticketType->price * $item['quantity'];
                $totalAmount += $itemTotal;

                $orderItemsData[] = [
                    'event_id' => $ticketType->event_id,
                    'ticket_type_id' => $ticketType->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $ticketType->price,
                    'total_price' => $itemTotal,
                ];
            }

            // Create Order
            $orderData = [
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => Order::STATUS_PENDING,
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

            return ResponseHelper::success($response, 'Order created successfully. Proceed to payment.', [
                'order_id' => $order->id,
                'total_amount' => $totalAmount,
                'status' => $order->status,
                'is_pos' => $isPos
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($response, $e->getMessage(), 400);
        }
    }

    /**
     * Mock Payment Webhook (Simulates successful payment)
     */
    public function mockPaymentWebhook(Request $request, Response $response, array $args): Response
    {
        DB::beginTransaction();
        try {
            $data = $request->getParsedBody();
            
            if (empty($data['order_id'])) {
                return ResponseHelper::error($response, 'Order ID is required', 400);
            }

            $order = Order::find($data['order_id']);
            
            if (!$order) {
                return ResponseHelper::error($response, 'Order not found', 404);
            }

            if ($order->status === Order::STATUS_PAID) {
                return ResponseHelper::success($response, 'Order already paid', $order->toArray());
            }

            // Update Order Status
            $order->update([
                'status' => Order::STATUS_PAID,
                'payment_reference' => 'MOCK-' . uniqid(),
            ]);

            // Generate Tickets
            $orderItems = $order->items;
            $generatedTickets = [];

            foreach ($orderItems as $item) {
                for ($i = 0; $i < $item->quantity; $i++) {
                    $ticket = Ticket::create([
                        'order_id' => $order->id,
                        'event_id' => $item->event_id,
                        'ticket_type_id' => $item->ticket_type_id,
                        'ticket_code' => Ticket::generateUniqueCode(),
                        'status' => Ticket::STATUS_ACTIVE,
                        // Attendee ID can be assigned later
                    ]);
                    $generatedTickets[] = $ticket;
                }
            }

            DB::commit();

            return ResponseHelper::success($response, 'Payment successful. Tickets generated.', [
                'order' => $order,
                'tickets_count' => count($generatedTickets)
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::error($response, 'Payment processing failed', 500, $e->getMessage());
        }
    }
    
    /**
     * Get user orders
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $user = $request->getAttribute('user');
            $orders = Order::where('user_id', $user->id)->with('items.ticketType.event')->orderBy('created_at', 'desc')->get();
            
            return ResponseHelper::success($response, 'Orders fetched successfully', $orders);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch orders', 500, $e->getMessage());
        }
    }
}
