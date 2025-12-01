<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Organizer;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

/**
 * OrganizerController
 * Handles organizer-related operations using Eloquent ORM
 */
class OrganizerController
{
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
            if (Organizer::findByUserId((int)$data['user_id'])) {
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
            $query = $queryParams['q'] ?? '';

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
