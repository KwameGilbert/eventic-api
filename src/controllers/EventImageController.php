<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EventImage;
use App\Models\Event;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * EventImageController
 * Handles event image gallery operations
 */
class EventImageController
{
    /**
     * Get all images for an event
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $eventId = $queryParams['event_id'] ?? null;

            if (!$eventId) {
                return ResponseHelper::error($response, 'Event ID is required', 400);
            }

            $images = EventImage::where('event_id', $eventId)->get();
            
            return ResponseHelper::success($response, 'Event images fetched successfully', [
                'images' => $images,
                'count' => $images->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch event images', 500, $e->getMessage());
        }
    }

    /**
     * Add an image to an event
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // Validate required fields
            if (empty($data['event_id']) || empty($data['image_path'])) {
                return ResponseHelper::error($response, 'Event ID and Image Path are required', 400);
            }
            
            // Verify event exists
            if (!Event::find($data['event_id'])) {
                return ResponseHelper::error($response, 'Event not found', 404);
            }
            
            $image = EventImage::create($data);
            
            return ResponseHelper::success($response, 'Event image added successfully', $image->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to add event image', 500, $e->getMessage());
        }
    }

    /**
     * Delete an event image
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $image = EventImage::find($id);
            
            if (!$image) {
                return ResponseHelper::error($response, 'Image not found', 404);
            }
            
            // TODO: Add logic to delete physical file if needed
            
            $image->delete();
            
            return ResponseHelper::success($response, 'Event image deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete event image', 500, $e->getMessage());
        }
    }
}
