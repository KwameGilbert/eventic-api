<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Models\Organizer;
use App\Models\Event;
use App\Models\Award;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\AwardVote;
use App\Models\Ticket;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Support\Carbon;
use Exception;

/**
 * AdminController
 * Handles administrator dashboard and management operations
 */
class AdminController
{
    /**
     * Get admin dashboard overview
     * GET /v1/admin/dashboard
     */
    public function getDashboard(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            // Verify admin role
            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            // === PLATFORM STATISTICS ===
            
            // Users
            $totalUsers = User::count();
            $organizers = Organizer::count();
            $attendees = User::where('role', 'attendee')->count();
            $newUsersThisMonth = User::where('created_at', '>=', Carbon::now()->startOfMonth())->count();

            // Events
            $totalEvents = Event::count();
            $eventsByStatus = [
                'published' => Event::where('status', 'published')->count(),
                'draft' => Event::where('status', 'draft')->count(),
                'pending' => Event::where('status', 'pending')->count(),
                'cancelled' => Event::where('status', 'cancelled')->count(),
                'completed' => Event::where('status', 'completed')->count(),
            ];

            // Awards
            $totalAwards = Award::count();
            $awardsByStatus = [
                'published' => Award::where('status', 'published')->count(),
                'draft' => Award::where('status', 'draft')->count(),
                'pending' => Award::where('status', 'pending')->count(),
                'completed' => Award::where('status', 'completed')->count(),
                'closed' => Award::where('status', 'closed')->count(),
            ];

            // Revenue
            $totalTicketRevenue = (float) OrderItem::whereHas('order', function ($query) {
                $query->where('status', 'paid');
            })->sum('total_price');
            $totalVoteRevenue = (float) AwardVote::where('status', 'paid')
                ->with('category')
                ->get()
                ->sum(function ($vote) {
                    return $vote->number_of_votes * ($vote->category->cost_per_vote ?? 5);
                });
            $totalRevenue = $totalTicketRevenue + $totalVoteRevenue;
            $platformFees = ($totalTicketRevenue * 0.015) + ($totalVoteRevenue * 0.05);

            // Orders & Sales
            $totalOrders = Order::where('status', 'paid')->count();
            $totalTicketsSold = Ticket::count();
            $totalVotesCast = AwardVote::where('status', 'paid')->sum('number_of_votes');

            // Recent Activity
            $recentOrders = Order::with(['user', 'items.event'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'user_name' => $order->user->name ?? 'N/A',
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at->toIso8601String(),
                        'items_count' => $order->items->count(),
                    ];
                });

            $recentVotes = AwardVote::with(['category', 'nominee'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($vote) {
                    return [
                        'id' => $vote->id,
                        'reference' => $vote->reference,
                        'voter_email' => $vote->voter_email,
                        'nominee_name' => $vote->nominee->name ?? 'N/A',
                        'category_name' => $vote->category->name ?? 'N/A',
                        'number_of_votes' => $vote->number_of_votes,
                        'status' => $vote->status,
                        'created_at' => $vote->created_at->toIso8601String(),
                    ];
                });

            // Pending Approvals
            $pendingEvents = Event::where('status', 'pending')
                ->with('organizer.user')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'slug' => $event->slug,
                        'organizer_name' => $event->organizer->user->name ?? 'N/A',
                        'start_time' => $event->start_time ? $event->start_time->toIso8601String() : null,
                        'created_at' => $event->created_at->toIso8601String(),
                    ];
                });

            $pendingAwards = Award::where('status', 'pending')
                ->with('organizer.user')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($award) {
                    return [
                        'id' => $award->id,
                        'title' => $award->title,
                        'slug' => $award->slug,
                        'organizer_name' => $award->organizer->user->name ?? 'N/A',
                        'ceremony_date' => $award->ceremony_date ? $award->ceremony_date->toIso8601String() : null,
                        'created_at' => $award->created_at->toIso8601String(),
                    ];
                });

            // Monthly Growth Trends (Last 12 months)
            $monthlyTrends = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = Carbon::now()->subMonths($i);
                $monthStart = $month->copy()->startOfMonth();
                $monthEnd = $month->copy()->endOfMonth();

                $monthlyTrends[] = [
                    'month' => $month->format('M Y'),
                    'users_registered' => User::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'events_created' => Event::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'awards_created' => Award::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'tickets_sold' => Ticket::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                    'votes_cast' => AwardVote::whereBetween('created_at', [$monthStart, $monthEnd])
                        ->where('status', 'paid')
                        ->sum('number_of_votes'),
                ];
            }

            // Top Performers
            $topEvents = Event::withCount(['tickets as tickets_sold'])
                ->orderBy('tickets_sold', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($event) {
                    $revenue = (float) OrderItem::where('event_id', $event->id)
                        ->whereHas('order', function ($query) {
                            $query->where('status', 'paid');
                        })
                        ->sum('total_price');
                    
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'slug' => $event->slug,
                        'tickets_sold' => $event->tickets_sold,
                        'revenue' => round($revenue, 2),
                    ];
                });

            $topAwards = Award::withCount(['votes as total_votes'])
                ->orderBy('total_votes', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($award) {
                    $votes = AwardVote::where('award_id', $award->id)
                        ->where('status', 'paid')
                        ->with('category')
                        ->get();
                    
                    $revenue = (float) $votes->sum(function ($vote) {
                        return $vote->number_of_votes * ($vote->category->cost_per_vote ?? 5);
                    });
                    
                    return [
                        'id' => $award->id,
                        'title' => $award->title,
                        'slug' => $award->slug,
                        'votes' => $votes->sum('number_of_votes'),
                        'revenue' => round($revenue, 2),
                    ];
                });

            $topOrganizers = Organizer::withCount(['events', 'awards'])
                ->with('user')
                ->orderBy('events_count', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($organizer) {
                    return [
                        'id' => $organizer->id,
                        'name' => $organizer->user->name ?? 'N/A',
                        'email' => $organizer->user->email ?? 'N/A',
                        'events_count' => $organizer->events_count,
                        'awards_count' => $organizer->awards_count,
                    ];
                });

            $data = [
                'platform_stats' => [
                    'total_users' => $totalUsers,
                    'organizers' => $organizers,
                    'attendees' => $attendees,
                    'new_users_this_month' => $newUsersThisMonth,
                    'total_events' => $totalEvents,
                    'total_awards' => $totalAwards,
                    'total_orders' => $totalOrders,
                    'total_tickets_sold' => $totalTicketsSold,
                    'total_votes_cast' => $totalVotesCast,
                ],
                'revenue_stats' => [
                    'total_revenue' => round($totalRevenue, 2),
                    'ticket_revenue' => round($totalTicketRevenue, 2),
                    'vote_revenue' => round($totalVoteRevenue, 2),
                    'platform_fees' => round($platformFees, 2),
                    'revenue_breakdown' => [
                        'events_percentage' => $totalRevenue > 0 ? round(($totalTicketRevenue / $totalRevenue) * 100, 1) : 0,
                        'awards_percentage' => $totalRevenue > 0 ? round(($totalVoteRevenue / $totalRevenue) * 100, 1) : 0,
                    ],
                ],
                'status_breakdown' => [
                    'events' => $eventsByStatus,
                    'awards' => $awardsByStatus,
                ],
                'pending_approvals' => [
                    'events' => $pendingEvents,
                    'awards' => $pendingAwards,
                    'total_pending' => $eventsByStatus['pending'] + $awardsByStatus['pending'],
                ],
                'recent_activity' => [
                    'orders' => $recentOrders,
                    'votes' => $recentVotes,
                ],
                'monthly_trends' => $monthlyTrends,
                'top_performers' => [
                    'events' => $topEvents,
                    'awards' => $topAwards,
                    'organizers' => $topOrganizers,
                ],
            ];

            return ResponseHelper::success($response, 'Admin dashboard data fetched successfully', $data);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch admin dashboard data', 500, $e->getMessage());
        }
    }

    /**
     * Get all users with filters
     * GET /v1/admin/users
     */
    public function getUsers(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $queryParams = $request->getQueryParams();
            $role = $queryParams['role'] ?? null;
            $search = $queryParams['search'] ?? null;

            $query = User::query();

            if ($role) {
                $query->where('role', $role);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->orderBy('created_at', 'desc')->get();

            return ResponseHelper::success($response, 'Users fetched successfully', ['users' => $users]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch users', 500, $e->getMessage());
        }
    }

    /**
     * Approve pending event
     * PUT /v1/admin/events/{id}/approve
     */
    public function approveEvent(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $eventId = (int) $args['id'];
            $event = Event::find($eventId);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            if ($event->status !== 'pending') {
                return ResponseHelper::error($response, 'Only pending events can be approved', 400);
            }

            $event->status = Event::STATUS_PUBLISHED;
            $event->save();

            return ResponseHelper::success($response, 'Event approved successfully', ['event' => $event]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to approve event', 500, $e->getMessage());
        }
    }

    /**
     * Reject pending event
     * PUT /v1/admin/events/{id}/reject
     */
    public function rejectEvent(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $eventId = (int) $args['id'];
            $event = Event::find($eventId);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            if ($event->status !== 'pending') {
                return ResponseHelper::error($response, 'Only pending events can be rejected', 400);
            }

            $event->status = Event::STATUS_DRAFT;
            $event->save();

            return ResponseHelper::success($response, 'Event rejected successfully', ['event' => $event]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reject event', 500, $e->getMessage());
        }
    }

    /**
     * Approve pending award
     * PUT /v1/admin/awards/{id}/approve
     */
    public function approveAward(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $awardId = (int) $args['id'];
            $award = Award::find($awardId);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            if ($award->status !== 'pending') {
                return ResponseHelper::error($response, 'Only pending awards can be approved', 400);
            }

            $award->status = Award::STATUS_PUBLISHED;
            $award->save();

            return ResponseHelper::success($response, 'Award approved successfully', ['award' => $award]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to approve award', 500, $e->getMessage());
        }
    }

    /**
     * Reject pending award
     * PUT /v1/admin/awards/{id}/reject
     */
    public function rejectAward(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $awardId = (int) $args['id'];
            $award = Award::find($awardId);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            if ($award->status !== 'pending') {
                return ResponseHelper::error($response, 'Only pending awards can be rejected', 400);
            }

            $award->status = Award::STATUS_DRAFT;
            $award->save();

            return ResponseHelper::success($response, 'Award rejected successfully', ['award' => $award]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reject award', 500, $e->getMessage());
        }
    }

    // ===================================================================
    // EVENT MANAGEMENT
    // ===================================================================

    /**
     * Get all events (admin)
     * GET /v1/admin/events
     */
    public function getEvents(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            // Get all events with organizer info, tickets count, and revenue
            $events = Event::with(['organizer.user', 'ticketTypes'])
                ->withCount(['tickets as tickets_sold'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($event) {
                    // Calculate total revenue for this event
                    $totalRevenue = (float) OrderItem::where('event_id', $event->id)
                        ->whereHas('order', function ($query) {
                            $query->where('status', 'paid');
                        })
                        ->sum('total_price');

                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'slug' => $event->slug,
                        'description' => $event->description,
                        'organizer_id' => $event->organizer_id,
                        'organizer_name' => $event->organizer->user->name ?? 'N/A',
                        'venue_name' => $event->venue_name,
                        'address' => $event->address,
                        'banner_image' => $event->banner_image,
                        'start_time' => $event->start_time ? $event->start_time->toIso8601String() : null,
                        'end_time' => $event->end_time ? $event->end_time->toIso8601String() : null,
                        'status' => $event->status,
                        'is_featured' => (bool) $event->is_featured,
                        'tickets_sold' => $event->tickets_sold ?? 0,
                        'total_revenue' => round($totalRevenue, 2),
                        'created_at' => $event->created_at->toIso8601String(),
                        'updated_at' => $event->updated_at->toIso8601String(),
                    ];
                });

            return ResponseHelper::success($response, 'Events fetched successfully', ['events' => $events]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch events', 500, $e->getMessage());
        }
    }

    /**
     * Update event status
     * PUT /v1/admin/events/{id}/status
     */
    public function updateEventStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $eventId = (int) $args['id'];
            $data = $request->getParsedBody();
            $status = $data['status'] ?? null;

            // Validate status
            $validStatuses = [
                Event::STATUS_DRAFT,
                Event::STATUS_PENDING,
                Event::STATUS_PUBLISHED,
                Event::STATUS_CANCELLED,
                Event::STATUS_COMPLETED
            ];

            if (!in_array($status, $validStatuses)) {
                return ResponseHelper::error($response, 'Invalid status. Must be: draft, pending, published, cancelled, or completed', 400);
            }

            $event = Event::find($eventId);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            $event->status = $status;
            $event->save();

            return ResponseHelper::success($response, "Event status updated to {$status}", ['event' => $event]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update event status', 500, $e->getMessage());
        }
    }

    /**
     * Toggle event featured status
     * PUT /v1/admin/events/{id}/feature
     */
    public function toggleEventFeatured(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $eventId = (int) $args['id'];
            $data = $request->getParsedBody();
            $isFeatured = filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $event = Event::find($eventId);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            $event->is_featured = $isFeatured;
            $event->save();

            $message = $isFeatured ? 'Event featured successfully' : 'Event unfeatured successfully';
            return ResponseHelper::success($response, $message, ['event' => $event]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update featured status', 500, $e->getMessage());
        }
    }

    /**
     * Delete event
     * DELETE /v1/admin/events/{id}
     */
    public function deleteEvent(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $eventId = (int) $args['id'];
            $event = Event::find($eventId);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Get counts before deletion for reporting
            $ticketsCount = Ticket::where('event_id', $eventId)->count();
            $ordersCount = Order::whereHas('items', function ($query) use ($eventId) {
                $query->where('event_id', $eventId);
            })->count();

            // Delete the event (cascade will handle related records)
            $eventTitle = $event->title;
            $event->delete();

            return ResponseHelper::success($response, 'Event deleted successfully', [
                'event_title' => $eventTitle,
                'tickets_deleted' => $ticketsCount,
                'orders_affected' => $ordersCount,
                'message' => "Event '{$eventTitle}' and all associated data has been permanently deleted."
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete event', 500, $e->getMessage());
        }
    }

    /**
     * Get single event details (admin - full details)
     * GET /v1/admin/events/{id}
     */
    public function getEventDetail(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $eventId = (int) $args['id'];
            $event = Event::with(['organizer.user'])->find($eventId);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Calculate tickets sold and revenue
            $ticketsSold = Ticket::where('event_id', $eventId)->count();
            $totalRevenue = (float) OrderItem::where('event_id', $eventId)
                ->whereHas('order', function ($query) {
                    $query->where('status', 'paid');
                })
                ->sum('total_price');

            $eventData = [
                'id' => $event->id,
                'title' => $event->title,
                'slug' => $event->slug,
                'description' => $event->description,
                'organizer_id' => $event->organizer_id,
                'organizer_name' => $event->organizer->user->name ?? 'N/A',
                'venue_name' => $event->venue_name,
                'address' => $event->address,
                'city' => $event->city,
                'country' => $event->country,
                'banner_image' => $event->banner_image,
                'start_time' => $event->start_time ? $event->start_time->toIso8601String() : null,
                'end_time' => $event->end_time ? $event->end_time->toIso8601String() : null,
                'status' => $event->status,
                'is_featured' => (bool) $event->is_featured,
                'platform_fee_percentage' => (float) $event->platform_fee_percentage ?? 1.5,
                'tickets_sold' => $ticketsSold,
                'total_revenue' => round($totalRevenue, 2),
                'created_at' => $event->created_at->toIso8601String(),
                'updated_at' => $event->updated_at->toIso8601String(),
            ];

            return ResponseHelper::success($response, 'Event details fetched successfully', ['event' => $eventData]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch event details', 500, $e->getMessage());
        }
    }

    /**
     * Update event (admin - full update)
     * PUT /v1/admin/events/{id}
     */
    public function updateEventFull(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $eventId = (int) $args['id'];
            $data = $request->getParsedBody();

            $event = Event::find($eventId);

            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Update allowed fields
            if (isset($data['title'])) $event->title = $data['title'];
            if (isset($data['description'])) $event->description = $data['description'];
            if (isset($data['venue_name'])) $event->venue_name = $data['venue_name'];
            if (isset($data['address'])) $event->address = $data['address'];
            if (isset($data['city'])) $event->city = $data['city'];
            if (isset($data['country'])) $event->country = $data['country'];
            if (isset($data['banner_image'])) $event->banner_image = $data['banner_image'];
            if (isset($data['start_time'])) $event->start_time = $data['start_time'];
            if (isset($data['end_time'])) $event->end_time = $data['end_time'];
            if (isset($data['status'])) $event->status = $data['status'];
            if (isset($data['is_featured'])) $event->is_featured = filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN);
            if (isset($data['platform_fee_percentage'])) {
                $event->platform_fee_percentage = (float) $data['platform_fee_percentage'];
            }

            $event->save();

            return ResponseHelper::success($response, 'Event updated successfully', ['event' => $event]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update event', 500, $e->getMessage());
        }
    }

    // ===================================================================
    // AWARD MANAGEMENT
    // ===================================================================

    /**
     * Get all awards (admin)
     * GET /v1/admin/awards
     */
    public function getAwards(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            // Get all awards with organizer info, categories count, votes count, and revenue
            $awards = Award::with(['organizer.user', 'categories'])
                ->withCount('categories')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($award) {
                    // Calculate total votes for this award
                    $totalVotes = AwardVote::whereHas('category', function ($query) use ($award) {
                        $query->where('award_id', $award->id);
                    })
                    ->where('status', 'paid')
                    ->sum('number_of_votes');

                    // Calculate total revenue for this award
                    $votes = AwardVote::whereHas('category', function ($query) use ($award) {
                        $query->where('award_id', $award->id);
                    })
                    ->where('status', 'paid')
                    ->with('category')
                    ->get();

                    $totalRevenue = (float) $votes->sum(function ($vote) {
                        return $vote->number_of_votes * ($vote->category->cost_per_vote ?? 5);
                    });

                    return [
                        'id' => $award->id,
                        'title' => $award->title,
                        'slug' => $award->slug,
                        'description' => $award->description,
                        'organizer_id' => $award->organizer_id,
                        'organizer_name' => $award->organizer->user->name ?? 'N/A',
                        'banner_image' => $award->banner_image,
                        'ceremony_date' => $award->ceremony_date ? $award->ceremony_date->toIso8601String() : null,
                        'voting_start_date' => $award->voting_start_date ? $award->voting_start_date->toIso8601String() : null,
                        'voting_end_date' => $award->voting_end_date ? $award->voting_end_date->toIso8601String() : null,
                        'status' => $award->status,
                        'is_featured' => (bool) $award->is_featured,
                        'categories_count' => $award->categories_count ?? 0,
                        'total_votes' => $totalVotes,
                        'total_revenue' => round($totalRevenue, 2),
                        'created_at' => $award->created_at->toIso8601String(),
                        'updated_at' => $award->updated_at->toIso8601String(),
                    ];
                });

            return ResponseHelper::success($response, 'Awards fetched successfully', ['awards' => $awards]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch awards', 500, $e->getMessage());
        }
    }

    /**
     * Update award status
     * PUT /v1/admin/awards/{id}/status
     */
    public function updateAwardStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $awardId = (int) $args['id'];
            $data = $request->getParsedBody();
            $status = $data['status'] ?? null;

            // Validate status
            $validStatuses = [
                Award::STATUS_DRAFT,
                Award::STATUS_PENDING,
                Award::STATUS_PUBLISHED,
                Award::STATUS_COMPLETED,
                Award::STATUS_CLOSED
            ];

            if (!in_array($status, $validStatuses)) {
                return ResponseHelper::error($response, 'Invalid status. Must be: draft, pending, published, completed, or closed', 400);
            }

            $award = Award::find($awardId);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            $award->status = $status;
            $award->save();

            return ResponseHelper::success($response, "Award status updated to {$status}", ['award' => $award]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update award status', 500, $e->getMessage());
        }
    }

    /**
     * Toggle award featured status
     * PUT /v1/admin/awards/{id}/feature
     */
    public function toggleAwardFeatured(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $awardId = (int) $args['id'];
            $data = $request->getParsedBody();
            $isFeatured = filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $award = Award::find($awardId);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            $award->is_featured = $isFeatured;
            $award->save();

            $message = $isFeatured ? 'Award featured successfully' : 'Award unfeatured successfully';
            return ResponseHelper::success($response, $message, ['award' => $award]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update featured status', 500, $e->getMessage());
        }
    }

    /**
     * Delete award
     * DELETE /v1/admin/awards/{id}
     */
    public function deleteAward(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $awardId = (int) $args['id'];
            $award = Award::find($awardId);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            // Get counts before deletion for reporting
            $categoriesCount = $award->categories()->count();
            $votesCount = AwardVote::whereHas('category', function ($query) use ($awardId) {
                $query->where('award_id', $awardId);
            })->count();

            // Delete the award (cascade will handle related records)
            $awardTitle = $award->title;
            $award->delete();

            return ResponseHelper::success($response, 'Award deleted successfully', [
                'award_title' => $awardTitle,
                'categories_deleted' => $categoriesCount,
                'votes_affected' => $votesCount,
                'message' => "Award '{$awardTitle}' and all associated data has been permanently deleted."
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete award', 500, $e->getMessage());
        }
    }

    /**
     * Get single award details (admin - full details)
     * GET /v1/admin/awards/{id}
     */
    public function getAwardDetail(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $awardId = (int) $args['id'];
            $award = Award::with(['organizer.user', 'categories'])->find($awardId);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            // Calculate total votes and revenue
            $totalVotes = AwardVote::whereHas('category', function ($query) use ($awardId) {
                $query->where('award_id', $awardId);
            })
            ->where('status', 'paid')
            ->sum('number_of_votes');

            $votes = AwardVote::whereHas('category', function ($query) use ($awardId) {
                $query->where('award_id', $awardId);
            })
            ->where('status', 'paid')
            ->with('category')
            ->get();

            $totalRevenue = (float) $votes->sum(function ($vote) {
                return $vote->number_of_votes * ($vote->category->cost_per_vote ?? 5);
            });

            $awardData = [
                'id' => $award->id,
                'title' => $award->title,
                'slug' => $award->slug,
                'description' => $award->description,
                'organizer_id' => $award->organizer_id,
                'organizer_name' => $award->organizer->user->name ?? 'N/A',
                'banner_image' => $award->banner_image,
                'ceremony_date' => $award->ceremony_date ? $award->ceremony_date->toIso8601String() : null,
                'voting_start_date' => $award->voting_start_date ? $award->voting_start_date->toIso8601String() : null,
                'voting_end_date' => $award->voting_end_date ? $award->voting_end_date->toIso8601String() : null,
                'status' => $award->status,
                'is_featured' => (bool) $award->is_featured,
                'platform_fee_percentage' => (float) $award->platform_fee_percentage ?? 5.0,
                'categories_count' => $award->categories->count(),
                'total_votes' => $totalVotes,
                'total_revenue' => round($totalRevenue, 2),
                'created_at' => $award->created_at->toIso8601String(),
                'updated_at' => $award->updated_at->toIso8601String(),
            ];

            return ResponseHelper::success($response, 'Award details fetched successfully', ['award' => $awardData]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch award details', 500, $e->getMessage());
        }
    }

    /**
     * Update award (admin - full update)
     * PUT /v1/admin/awards/{id}
     */
    public function updateAwardFull(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $awardId = (int) $args['id'];
            $data = $request->getParsedBody();

            $award = Award::find($awardId);

            if (!$award) {
                return ResponseHelper::error($response, 'Award not found', 404);
            }

            // Update allowed fields
            if (isset($data['title'])) $award->title = $data['title'];
            if (isset($data['description'])) $award->description = $data['description'];
            if (isset($data['banner_image'])) $award->banner_image = $data['banner_image'];
            if (isset($data['ceremony_date'])) $award->ceremony_date = $data['ceremony_date'];
            if (isset($data['voting_start_date'])) $award->voting_start_date = $data['voting_start_date'];
            if (isset($data['voting_end_date'])) $award->voting_end_date = $data['voting_end_date'];
            if (isset($data['status'])) $award->status = $data['status'];
            if (isset($data['is_featured'])) $award->is_featured = filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN);
            if (isset($data['platform_fee_percentage'])) {
                $award->platform_fee_percentage = (float) $data['platform_fee_percentage'];
            }

            $award->save();

            return ResponseHelper::success($response, 'Award updated successfully', ['award' => $award]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update award', 500, $e->getMessage());
        }
    }

    /**
     * Update user status (activate, suspend, deactivate)
     * PUT /v1/admin/users/{id}/status
     */
    public function updateUserStatus(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $userId = (int) $args['id'];
            $data = $request->getParsedBody();
            $status = $data['status'] ?? null;

            // Validate status
            if (!in_array($status, [User::STATUS_ACTIVE, User::STATUS_INACTIVE, User::STATUS_SUSPENDED])) {
                return ResponseHelper::error($response, 'Invalid status. Must be: active, inactive, or suspended', 400);
            }

            $user = User::find($userId);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Prevent admin from suspending themselves
            if ($user->id === $jwtUser->id && $status !== User::STATUS_ACTIVE) {
                return ResponseHelper::error($response, 'You cannot change your own status', 400);
            }

            $user->status = $status;
            $user->save();

            return ResponseHelper::success($response, 'User status updated successfully', ['user' => $user]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update user status', 500, $e->getMessage());
        }
    }

    /**
     * Reset user password
     * POST /v1/admin/users/{id}/reset-password
     */
    public function resetUserPassword(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $userId = (int) $args['id'];
            $data = $request->getParsedBody();
            $newPassword = $data['password'] ?? null;

            if (!$newPassword || strlen($newPassword) < 6) {
                return ResponseHelper::error($response, 'Password must be at least 6 characters long', 400);
            }

            $user = User::find($userId);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Update password (will be auto-hashed by User model mutator)
            $user->password = $newPassword;
            $user->first_login = true; // Force password change on next login
            $user->save();

            return ResponseHelper::success($response, 'Password reset successfully', [
                'message' => 'The user will be required to change their password on next login'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to reset password', 500, $e->getMessage());
        }
    }

    /**
     * Update user role
     * PUT /v1/admin/users/{id}/role
     */
    public function updateUserRole(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $userId = (int) $args['id'];
            $data = $request->getParsedBody();
            $role = $data['role'] ?? null;

            // Validate role
            $validRoles = [User::ROLE_ADMIN, User::ROLE_ORGANIZER, User::ROLE_ATTENDEE, User::ROLE_POS, User::ROLE_SCANNER];
            if (!in_array($role, $validRoles)) {
                return ResponseHelper::error($response, 'Invalid role', 400);
            }

            $user = User::find($userId);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Prevent admin from changing their own role
            if ($user->id === $jwtUser->id) {
                return ResponseHelper::error($response, 'You cannot change your own role', 400);
            }

            $oldRole = $user->role;
            $user->role = $role;
            $user->save();

            // If changing to/from organizer, handle organizer profile
            if ($role === User::ROLE_ORGANIZER && $oldRole !== User::ROLE_ORGANIZER) {
                // Create organizer profile if it doesn't exist
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer) {
                    Organizer::create([
                        'user_id' => $user->id,
                        'organization_name' => $user->name . "'s Organization",
                    ]);
                }
            }

            return ResponseHelper::success($response, 'User role updated successfully', ['user' => $user]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update user role', 500, $e->getMessage());
        }
    }

    /**
     * Delete user
     * DELETE /v1/admin/users/{id}
     */
    public function deleteUser(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $userId = (int) $args['id'];
            $user = User::find($userId);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            // Prevent admin from deleting themselves
            if ($user->id === $jwtUser->id) {
                return ResponseHelper::error($response, 'You cannot delete your own account', 400);
            }

            // Check if user has associated data
            $hasOrders = false;
            $hasEvents = false;
            $hasAwards = false;

            if ($user->role === 'organizer') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if ($organizer) {
                    $hasEvents = Event::where('organizer_id', $organizer->id)->exists();
                    $hasAwards = Award::where('organizer_id', $organizer->id)->exists();
                }
            } elseif ($user->role === 'attendee') {
                $hasOrders = Order::where('user_id', $user->id)->exists();
            }

            // Warn if user has associated data (but still allow deletion)
            $warnings = [];
            if ($hasEvents) $warnings[] = 'This user has created events';
            if ($hasAwards) $warnings[] = 'This user has created awards';
            if ($hasOrders) $warnings[] = 'This user has orders';

            // Delete user (cascade deletes will handle related records)
            $user->delete();

            return ResponseHelper::success($response, 'User deleted successfully', [
                'warnings' => $warnings,
                'message' => count($warnings) > 0 
                    ? 'User deleted. Related records may have been affected.' 
                    : 'User deleted successfully'
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete user', 500, $e->getMessage());
        }
    }

    /**
     * Get single user details
     * GET /v1/admin/users/{id}
     */
    public function getUser(Request $request, Response $response, array $args): Response
    {
        try {
            $jwtUser = $request->getAttribute('user');

            if ($jwtUser->role !== 'admin') {
                return ResponseHelper::error($response, 'Unauthorized. Admin access required.', 403);
            }

            $userId = (int) $args['id'];
            $user = User::with(['organizer', 'attendee'])->find($userId);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            return ResponseHelper::success($response, 'User fetched successfully', ['user' => $user]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch user', 500, $e->getMessage());
        }
    }
}
