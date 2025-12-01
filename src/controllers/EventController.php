<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Event;
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
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = Event::query();

            // Filter by status (default to published if not specified for public list)
            if (isset($queryParams['status'])) {
                $query->where('status', $queryParams['status']);
            }

            // Filter by event type
            if (isset($queryParams['event_type_id'])) {
                $query->where('event_type_id', $queryParams['event_type_id']);
            }

            // Filter by organizer
            if (isset($queryParams['organizer_id'])) {
                $query->where('organizer_id', $queryParams['organizer_id']);
            }

            $events = $query->orderBy('start_time', 'asc')->get();
            
            return ResponseHelper::success($response, 'Events fetched successfully', [
                'events' => $events,
                'count' => $events->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch events', 500, $e->getMessage());
        }
    }

    /**
     * Get single event by ID
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $event = Event::with('organizer')->find($id);
            
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
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // Validate required fields
            $requiredFields = ['organizer_id', 'title', 'start_time', 'end_time'];
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
            
            $event = Event::create($data);
            
            return ResponseHelper::success($response, 'Event created successfully', $event->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create event', 500, $e->getMessage());
        }
    }

    /**
     * Update event
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
            
            // Update slug if title changes and slug isn't manually provided
            if (isset($data['title']) && !isset($data['slug'])) {
                $data['slug'] = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['title'])));
            }
            
            $event->update($data);
            
            return ResponseHelper::success($response, 'Event updated successfully', $event->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update event', 500, $e->getMessage());
        }
    }

    /**
     * Delete event
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $event = Event::find($id);
            
            if (!$event) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }
            
            $event->delete();
            
            return ResponseHelper::success($response, 'Event deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete event', 500, $e->getMessage());
        }
    }

    /**
     * Search events
     */
    public function search(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $query = $queryParams['q'] ?? '';

            if (empty($query)) {
                return ResponseHelper::error($response, 'Search query is required', 400);
            }

            $events = Event::where('title', 'LIKE', "%{$query}%")
                          ->orWhere('description', 'LIKE', "%{$query}%")
                          ->orWhere('venue_name', 'LIKE', "%{$query}%")
                          ->get();

            return ResponseHelper::success($response, 'Events found', [
                'events' => $events,
                'count' => $events->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to search events', 500, $e->getMessage());
        }
    }
}
