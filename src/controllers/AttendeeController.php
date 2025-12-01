<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Attendee;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * AttendeeController
 * Handles attendee-related operations using Eloquent ORM
 */
class AttendeeController
{
    /**
     * Get all attendees
     */
    public function index(Request $request, Response $response, array $args): Response
    {
        try {
            $attendees = Attendee::all();
            
            return ResponseHelper::success($response, 'Attendees fetched successfully', [
                'attendees' => $attendees,
                'count' => $attendees->count()
            ]);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch attendees', 500, $e->getMessage());
        }
    }

    /**
     * Get single attendee by ID
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $attendee = Attendee::find($id);
            
            if (!$attendee) {
                return ResponseHelper::error($response, 'Attendee not found', 404);
            }
            
            return ResponseHelper::success($response, 'Attendee fetched successfully', $attendee->getFullProfile());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch attendee', 500, $e->getMessage());
        }
    }

    /**
     * Create new attendee profile
     */
    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();
            
            // Validate required fields
            if (empty($data['user_id'])) {
                return ResponseHelper::error($response, 'User ID is required', 400);
            }
            
            // Check if user already has an attendee profile
            if (Attendee::findByUserId((int)$data['user_id'])) {
                return ResponseHelper::error($response, 'User already has an attendee profile', 409);
            }
            
            $attendee = Attendee::create($data);
            
            return ResponseHelper::success($response, 'Attendee profile created successfully', $attendee->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create attendee profile', 500, $e->getMessage());
        }
    }

    /**
     * Update attendee profile
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $attendee = Attendee::find($id);
            
            if (!$attendee) {
                return ResponseHelper::error($response, 'Attendee not found', 404);
            }
            
            $attendee->updateProfile($data);
            
            return ResponseHelper::success($response, 'Attendee profile updated successfully', $attendee->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update attendee profile', 500, $e->getMessage());
        }
    }

    /**
     * Delete attendee profile
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = $args['id'];
            $attendee = Attendee::find($id);
            
            if (!$attendee) {
                return ResponseHelper::error($response, 'Attendee not found', 404);
            }
            
            $attendee->deleteProfile();
            
            return ResponseHelper::success($response, 'Attendee profile deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete attendee profile', 500, $e->getMessage());
        }
    }
}
