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
