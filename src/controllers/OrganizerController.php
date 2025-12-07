<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Organizer;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Support\Carbon;
use Exception;

/**
 * OrganizerController
 * Handles organizer-related operations using Eloquent ORM
 */
class OrganizerController
{
    /**
     * Get dashboard data for the authenticated organizer
     * This endpoint provides all necessary data for the organizer dashboard in a single call
     */
    public function getDashboard(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            // Get organizer profile
            $organizer = Organizer::findByUserId((int) $jwtUser->id);

            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer profile not found', 404);
            }

            // Get user info for greeting
            $user = User::find($jwtUser->id);
            $firstName = explode(' ', $user->name ?? 'User')[0];

            // Get organizer's events
            $events = Event::where('organizer_id', $organizer->id)->get();
            $eventIds = $events->pluck('id')->toArray();

            // === STATS ===
            $totalEvents = $events->count();

            // Get orders for organizer's events (through order_items)
            $orderIds = OrderItem::whereIn('event_id', $eventIds)
                ->pluck('order_id')
                ->unique()
                ->toArray();

            $orders = Order::whereIn('id', $orderIds)->get();
            $paidOrders = $orders->where('status', 'paid');

            $totalOrders = $paidOrders->count();
            $totalRevenue = $paidOrders->sum('total_amount');

            // Get tickets sold for organizer's events
            $ticketsSold = Ticket::whereIn('event_id', $eventIds)
                ->whereHas('order', function ($q) {
                    $q->where('status', 'paid');
                })
                ->count();

            // Calculate percentage changes (compare to previous period)
            $lastMonthStart = Carbon::now()->subMonth()->startOfMonth();
            $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();
            $thisMonthStart = Carbon::now()->startOfMonth();

            $lastMonthOrders = Order::whereIn('id', $orderIds)
                ->where('status', 'paid')
                ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
                ->count();

            $thisMonthOrders = Order::whereIn('id', $orderIds)
                ->where('status', 'paid')
                ->where('created_at', '>=', $thisMonthStart)
                ->count();

            $orderChange = $lastMonthOrders > 0
                ? round((($thisMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100)
                : ($thisMonthOrders > 0 ? 100 : 0);

            $stats = [
                [
                    'label' => 'Total Events',
                    'value' => (string) $totalEvents,
                    'change' => '0%', // Events don't have a natural time-based comparison
                    'trend' => 'up',
                    'ringProgress' => min(100, $totalEvents * 10)
                ],
                [
                    'label' => 'Total Orders',
                    'value' => number_format($totalOrders),
                    'change' => abs($orderChange) . '%',
                    'trend' => $orderChange >= 0 ? 'up' : 'down',
                    'ringProgress' => min(100, (int) ($totalOrders / 10))
                ],
                [
                    'label' => 'Tickets Sold',
                    'value' => number_format($ticketsSold),
                    'change' => '0%',
                    'trend' => 'up',
                    'ringProgress' => min(100, (int) ($ticketsSold / 50))
                ],
                [
                    'label' => 'Total Revenue',
                    'value' => 'GH₵' . number_format($totalRevenue, 2),
                    'change' => '0%',
                    'trend' => 'up',
                    'ringProgress' => min(100, (int) ($totalRevenue / 1000))
                ],
            ];

            // === TICKET SALES BY TYPE (This Week) ===
            $weekStart = Carbon::now()->startOfWeek();
            $ticketSalesByType = OrderItem::whereIn('event_id', $eventIds)
                ->whereHas('order', function ($q) use ($weekStart) {
                    $q->where('status', 'paid')
                        ->where('created_at', '>=', $weekStart);
                })
                ->with('ticketType')
                ->get()
                ->groupBy(function ($item) {
                    return $item->ticketType->name ?? 'Unknown';
                })
                ->map(function ($items, $name) {
                    return [
                        'name' => $name,
                        'value' => $items->sum('quantity')
                    ];
                })
                ->values()
                ->toArray();

            // If no data, provide some structure
            if (empty($ticketSalesByType)) {
                $ticketSalesByType = [
                    ['name' => 'VIP', 'value' => 0],
                    ['name' => 'Regular', 'value' => 0],
                    ['name' => 'Early Bird', 'value' => 0],
                ];
            }

            // === WEEKLY REVENUE DATA ===
            $weeklyRevenueData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $dayName = $date->format('D');

                $dayRevenue = Order::whereIn('id', $orderIds)
                    ->where('status', 'paid')
                    ->whereDate('created_at', $date->toDateString())
                    ->sum('total_amount');

                $weeklyRevenueData[] = [
                    'day' => $dayName,
                    'revenue' => round($dayRevenue / 1000, 1) // In thousands
                ];
            }

            // === MONTHLY REVENUE DATA ===
            $monthlyRevenueData = [];
            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthName = $date->format('M');

                $monthRevenue = Order::whereIn('id', $orderIds)
                    ->where('status', 'paid')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount');

                $monthlyRevenueData[] = [
                    'month' => $monthName,
                    'revenue' => round($monthRevenue / 1000, 1) // In thousands
                ];
            }

            // === RECENT ACTIVITIES ===
            // Get recent refund requests (orders with status changes)
            $recentRefunds = Order::whereIn('id', $orderIds)
                ->where('status', 'refunded')
                ->orderBy('updated_at', 'desc')
                ->limit(1)
                ->first();

            // Get recent feedback (placeholder - would need reviews table)
            $activities = [];

            if ($recentRefunds) {
                $activities[] = [
                    'type' => 'refund',
                    'title' => '1 customer',
                    'description' => 'requested a refund',
                    'time' => Carbon::parse($recentRefunds->updated_at)->format('D, M d · g:i A')
                ];
            }

            // Get recent high-demand events
            $highDemandEvents = Event::where('organizer_id', $organizer->id)
                ->whereHas('ticketTypes', function ($q) {
                    $q->whereRaw('remaining < quantity * 0.2'); // Less than 20% remaining
                })
                ->count();

            if ($highDemandEvents > 0) {
                $activities[] = [
                    'type' => 'signup',
                    'title' => $highDemandEvents . ' event' . ($highDemandEvents > 1 ? 's' : ''),
                    'description' => 'are nearly sold out',
                    'time' => Carbon::now()->format('D, M d · g:i A')
                ];
            }

            // Get recent ticket sales count
            $recentSales = Ticket::whereIn('event_id', $eventIds)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->count();

            if ($recentSales > 0) {
                $activities[] = [
                    'type' => 'feedback',
                    'title' => $recentSales . ' ticket' . ($recentSales > 1 ? 's' : ''),
                    'description' => 'sold in the last 24 hours',
                    'time' => Carbon::now()->format('D, M d · g:i A')
                ];
            }

            // === RECENT ORDERS ===
            $recentOrders = Order::whereIn('id', $orderIds)
                ->with(['user', 'items.event', 'items.ticketType'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    $firstEvent = $order->items->first()?->event;
                    return [
                        'customer' => $order->customer_name ?? $order->user?->name ?? 'Guest',
                        'event' => $firstEvent?->title ?? 'Unknown Event',
                        'tickets' => $order->items->sum('quantity'),
                        'amount' => (float) $order->total_amount,
                        'status' => ucfirst($order->status),
                        'time' => $this->getRelativeTime($order->created_at)
                    ];
                })
                ->toArray();

            // === UPCOMING EVENT ===
            $upcomingEvent = Event::where('organizer_id', $organizer->id)
                ->where('start_time', '>', Carbon::now())
                ->where('status', 'published')
                ->orderBy('start_time', 'asc')
                ->with(['eventType', 'images'])
                ->first();

            $upcomingEventData = null;
            if ($upcomingEvent) {
                $upcomingEventData = [
                    'id' => $upcomingEvent->id,
                    'title' => $upcomingEvent->title,
                    'slug' => $upcomingEvent->slug,
                    'category' => $upcomingEvent->eventType?->name ?? 'Event',
                    'venue' => $upcomingEvent->venue,
                    'location' => $upcomingEvent->location . ', ' . $upcomingEvent->country,
                    'description' => substr($upcomingEvent->description ?? '', 0, 100) . '...',
                    'date' => Carbon::parse($upcomingEvent->start_time)->format('M d, Y'),
                    'time' => Carbon::parse($upcomingEvent->start_time)->format('g:i A') . ' - ' .
                        Carbon::parse($upcomingEvent->end_time)->format('g:i A'),
                    'image' => $upcomingEvent->images->first()?->image_url ??
                        'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=400&h=200&fit=crop'
                ];
            }

            // === CALENDAR EVENTS ===
            $calendarEvents = Event::where('organizer_id', $organizer->id)
                ->where('start_time', '>=', Carbon::now()->startOfMonth())
                ->where('start_time', '<=', Carbon::now()->addMonths(2))
                ->orderBy('start_time', 'asc')
                ->limit(5)
                ->with('eventType')
                ->get()
                ->map(function ($event) {
                    $date = Carbon::parse($event->start_time);
                    return [
                        'day' => $date->format('j'),
                        'dayName' => $date->format('D'),
                        'name' => $event->title,
                        'category' => $event->eventType?->name ?? 'Event',
                        'time' => Carbon::parse($event->start_time)->format('g:i A') . ' - ' .
                            Carbon::parse($event->end_time)->format('g:i A')
                    ];
                })
                ->toArray();

            // === ASSEMBLE DASHBOARD DATA ===
            $dashboardData = [
                'user' => [
                    'name' => $user->name,
                    'firstName' => $firstName,
                    'email' => $user->email,
                ],
                'organizer' => [
                    'id' => $organizer->id,
                    'organizationName' => $organizer->organization_name,
                    'profileImage' => $organizer->profile_image,
                ],
                'stats' => $stats,
                'ticketSalesData' => $ticketSalesByType,
                'weeklyRevenueData' => $weeklyRevenueData,
                'monthlyRevenueData' => $monthlyRevenueData,
                'activities' => $activities,
                'recentOrders' => $recentOrders,
                'upcomingEvent' => $upcomingEventData,
                'calendarEvents' => $calendarEvents,
            ];

            return ResponseHelper::success($response, 'Dashboard data fetched successfully', $dashboardData);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch dashboard data', 500, $e->getMessage());
        }
    }

    /**
     * Get relative time string (e.g., "2 min ago", "1 hr ago")
     */
    private function getRelativeTime($datetime): string
    {
        $now = Carbon::now();
        $time = Carbon::parse($datetime);
        $diffInMinutes = $now->diffInMinutes($time);

        if ($diffInMinutes < 1) {
            return 'Just now';
        } elseif ($diffInMinutes < 60) {
            return $diffInMinutes . ' min ago';
        } elseif ($diffInMinutes < 1440) {
            $hours = floor($diffInMinutes / 60);
            return $hours . ' hr' . ($hours > 1 ? 's' : '') . ' ago';
        } else {
            $days = floor($diffInMinutes / 1440);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        }
    }

    /**
     * Get all organizers
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $organizers = Organizer::all();

            return ResponseHelper::success($response, 'Organizers fetched successfully', [
                'organizers' => $organizers,
                'count' => $organizers->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch organizers', 500, $e->getMessage());
        }
    }

    /**
     * Get single organizer by ID
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $organizer = Organizer::find($id);

            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer not found', 404);
            }

            return ResponseHelper::success($response, 'Organizer fetched successfully', $organizer->getFullProfile());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch organizer', 500, $e->getMessage());
        }
    }

    /**
     * Create new organizer
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();

            // Validate required fields
            if (empty($data['user_id']) || empty($data['organization_name'])) {
                return ResponseHelper::error($response, 'User ID and Organization Name are required', 400);
            }

            // Check if user already has an organizer profile
            if (Organizer::findByUserId((int) $data['user_id'])) {
                return ResponseHelper::error($response, 'User already has an organizer profile', 409);
            }

            $organizer = Organizer::create($data);

            return ResponseHelper::success($response, 'Organizer created successfully', $organizer->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create organizer', 500, $e->getMessage());
        }
    }

    /**
     * Update organizer
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();

            $organizer = Organizer::find($id);

            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer not found', 404);
            }

            // Authorization: Check if user is admin or the profile owner
            $user = $request->getAttribute('user');
            if ($user->role !== 'admin' && $organizer->user_id !== $user->id) {
                return ResponseHelper::error($response, 'Unauthorized: You do not own this profile', 403);
            }

            $organizer->updateProfile($data);

            return ResponseHelper::success($response, 'Organizer updated successfully', $organizer->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update organizer', 500, $e->getMessage());
        }
    }

    /**
     * Delete organizer
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $organizer = Organizer::find($id);

            if (!$organizer) {
                return ResponseHelper::error($response, 'Organizer not found', 404);
            }

            // Authorization: Check if user is admin or the profile owner
            $user = $request->getAttribute('user');
            if ($user->role !== 'admin' && $organizer->user_id !== $user->id) {
                return ResponseHelper::error($response, 'Unauthorized: You do not own this profile', 403);
            }

            $organizer->deleteProfile();

            return ResponseHelper::success($response, 'Organizer deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete organizer', 500, $e->getMessage());
        }
    }

    /**
     * Search organizers by name
     */
    public function search(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = $queryParams['query'] ?? '';

            if (empty($query)) {
                return ResponseHelper::error($response, 'Search query is required', 400);
            }

            $organizers = Organizer::searchByName($query);

            return ResponseHelper::success($response, 'Organizers found', [
                'organizers' => $organizers,
                'count' => $organizers->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to search organizers', 500, $e->getMessage());
        }
    }
}
