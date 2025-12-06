<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\OrderItem;
use App\Models\Organizer;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * EventController
 * Handles event-related operations using Eloquent ORM
 */
class EventController
{
    /**
     * Get all events (with optional filtering)
     * GET /v1/events
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = Event::with(['ticketTypes', 'eventType', 'organizer.user', 'images']);

            // Filter by status (default to published for public list)
            if (isset($queryParams['status'])) {
                $query->where('status', $queryParams['status']);
            } else {
                // Default to published events for public endpoint
                $query->where('status', Event::STATUS_PUBLISHED);
            }

            // Filter by event type
            if (isset($queryParams['event_type_id'])) {
                $query->where('event_type_id', $queryParams['event_type_id']);
            }

            // Filter by organizer
            if (isset($queryParams['organizer_id'])) {
                $query->where('organizer_id', $queryParams['organizer_id']);
            }

            // Filter by category slug
            if (isset($queryParams['category'])) {
                $query->whereHas('eventType', function ($q) use ($queryParams) {
                    $q->where('slug', $queryParams['category']);
                });
            }

            // Filter upcoming only
            if (isset($queryParams['upcoming']) && $queryParams['upcoming'] === 'true') {
                $query->where('start_time', '>', \Illuminate\Support\Carbon::now());
            }

            // Search by title, description, or venue
            if (isset($queryParams['search']) && !empty($queryParams['search'])) {
                $search = $queryParams['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('venue_name', 'LIKE', "%{$search}%");
                });
            }

            // Location filter
            if (isset($queryParams['location']) && !empty($queryParams['location'])) {
                $query->where('address', 'LIKE', "%{$queryParams['location']}%");
            }

            // Pagination
            $page = (int)($queryParams['page'] ?? 1);
            $perPage = (int)($queryParams['per_page'] ?? 20);
            $offset = ($page - 1) * $perPage;

            $totalCount = $query->count();
            $events = $query->orderBy('start_time', 'asc')
                           ->offset($offset)
                           ->limit($perPage)
                           ->get();

            // Format events for frontend compatibility
            $formattedEvents = $events->map(function ($event) {
                return $event->getFullDetails();
            });
            
            return ResponseHelper::success($response, 'Events fetched successfully', [
                'events' => $formattedEvents->toArray(),
                'count' => $events->count(),
                'total' => $totalCount,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($totalCount / $perPage),
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch events', 500, $e->getMessage());
        }
    }

    /**
     * Get featured events for homepage carousel
     * GET /v1/events/featured
     */
    public function featured(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $limit = (int)($queryParams['limit'] ?? 5);

            // First try to get featured events
            $events = Event::with(['ticketTypes', 'eventType', 'organizer.user'])
                ->where('status', Event::STATUS_PUBLISHED)
                ->where('is_featured', true)
                ->where('start_time', '>', \Illuminate\Support\Carbon::now())
                ->orderBy('start_time', 'asc')
                ->limit($limit)
                ->get();

            // If no featured events, fallback to upcoming events
            if ($events->isEmpty()) {
                $events = Event::with(['ticketTypes', 'eventType', 'organizer.user'])
                    ->where('status', Event::STATUS_PUBLISHED)
                    ->where('start_time', '>', \Illuminate\Support\Carbon::now())
                    ->orderBy('start_time', 'asc')
                    ->limit($limit)
                    ->get();
            }

            // Format for frontend carousel
            $formattedEvents = $events->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'eventSlug' => $event->slug,
                    'venue' => $event->venue_name . ($event->address ? ', ' . $event->address : ''),
                    'date' => $event->start_time ? $event->start_time->format('D d M Y, g:i A') : null,
                    'image' => $event->banner_image,
                    'category' => $event->eventType ? $event->eventType->name : null,
                ];
            });

            return ResponseHelper::success($response, 'Featured events fetched successfully', $formattedEvents->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch featured events', 500, $e->getMessage());
        }
    }

    /**
     * Get single event by ID or slug
     * GET /v1/events/{id}
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $identifier = $args['id'];
            
            // Try to find by ID first, then by slug
            if (is_numeric($identifier)) {
                $event = Event::with(['organizer.user', 'ticketTypes', 'images', 'eventType'])->find($identifier);
            } else {
                $event = Event::with(['organizer.user', 'ticketTypes', 'images', 'eventType'])
                    ->where('slug', $identifier)
                    ->first();
            }
            
            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }
            
            return ResponseHelper::success($response, 'Event fetched successfully', $event->getFullDetails());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch event', 500, $e->getMessage());
        }
    }

    /**
     * Create new event
     * POST /v1/events
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $request->getAttribute('user');
            
            // Get organizer for the user
            $organizer = Organizer::where('user_id', $user->id)->first();
            if (!$organizer && $user->role !== 'admin') {
                return ResponseHelper::error($response, 'Only organizers can create events', 403);
            }

            // Set organizer_id from authenticated user's organizer profile
            if ($organizer) {
                $data['organizer_id'] = $organizer->id;
            }
            
            // Validate required fields
            $requiredFields = ['title', 'start_time', 'end_time'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return ResponseHelper::error($response, "Field '$field' is required", 400);
                }
            }
            
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
            }
            
            // Set default status if not provided
            if (!isset($data['status'])) {
                $data['status'] = Event::STATUS_DRAFT;
            }

            // Validate tags
            if (isset($data['tags']) && !is_array($data['tags'])) {
                return ResponseHelper::error($response, 'Tags must be an array', 400);
            }
            
            $event = Event::create($data);
            
            return ResponseHelper::success($response, 'Event created successfully', $event->getFullDetails(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create event', 500, $e->getMessage());
        }
    }

    /**
     * Update event
     * PUT /v1/events/{id}
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $event = Event::find($id);
            
            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Authorization: Check if user is admin or the event organizer
            $user = $request->getAttribute('user');
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer || $organizer->id !== $event->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized: You do not own this event', 403);
                }
            }
            
            // Update slug if title changes and slug isn't manually provided
            if (isset($data['title']) && !isset($data['slug'])) {
                $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
            }

            // Validate tags
            if (isset($data['tags']) && !is_array($data['tags'])) {
                return ResponseHelper::error($response, 'Tags must be an array', 400);
            }
            
            $event->update($data);
            
            return ResponseHelper::success($response, 'Event updated successfully', $event->getFullDetails());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update event', 500, $e->getMessage());
        }
    }

    /**
     * Delete event
     * DELETE /v1/events/{id}
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $event = Event::find($id);
            
            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }

            // Authorization: Check if user is admin or the event organizer
            $user = $request->getAttribute('user');
            if ($user->role !== 'admin') {
                $organizer = Organizer::where('user_id', $user->id)->first();
                if (!$organizer || $organizer->id !== $event->organizer_id) {
                    return ResponseHelper::error($response, 'Unauthorized: You do not own this event', 403);
                }
            }

            // Validation: Check if event has tickets sold
            if (Ticket::where('event_id', $id)->exists()) {
                return ResponseHelper::error($response, 'Cannot delete event with existing tickets', 400);
            }

            // Validation: Check if event has any order items (even if no tickets generated yet)
            if (OrderItem::where('event_id', $id)->exists()) {
                return ResponseHelper::error($response, 'Cannot delete event with associated orders', 400);
            }
            
            $event->delete();
            
            return ResponseHelper::success($response, 'Event deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete event', 500, $e->getMessage());
        }
    }

    /**
     * Search events
     * GET /v1/events/search
     */
    public function search(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = $queryParams['query'] ?? '';

            if (empty($query)) {
                return ResponseHelper::error($response, 'Search query is required', 400);
            }

            $events = Event::with(['ticketTypes', 'eventType', 'organizer.user'])
                ->where('status', Event::STATUS_PUBLISHED)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%")
                      ->orWhere('venue_name', 'LIKE', "%{$query}%");
                })
                ->get();

            $formattedEvents = $events->map(function ($event) {
                return $event->getFullDetails();
            });

            return ResponseHelper::success($response, 'Events found', [
                'events' => $formattedEvents->toArray(),
                'count' => $events->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to search events', 500, $e->getMessage());
        }
    }
}
