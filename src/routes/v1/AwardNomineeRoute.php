<?php

/**
 * Award Nominee Routes (v1 API)
 * 
 * This file defines all routes for managing award nominees.
 * 
 * ============================================================================
 * PUBLIC ROUTES (No Authentication Required)
 * ============================================================================
 * 
 * GET  /v1/nominees/award-categories/{categoryId}
 *      List all nominees for a specific category
 *      Query Params: ?include_stats=true|false
 *      Response: Array of nominees with optional vote statistics
 * 
 * GET  /v1/nominees/awards/{awardId}
 *      List all nominees for a specific award
 *      Query Params: ?include_stats=true|false
 *      Response: Array of nominees grouped by category
 * 
 * GET  /v1/nominees/code/{code}
 *      Get nominee by their unique 4-character voting code
 *      Query Params: ?include_stats=true|false
 *      Response: Single nominee details
 *      Example: GET /v1/nominees/code/A3K7
 * 
 * GET  /v1/nominees/{id}
 *      Get single nominee by ID
 *      Query Params: ?include_stats=true|false
 *      Response: Single nominee details
 * 
 * GET  /v1/nominees/{id}/stats
 *      Get voting statistics for a nominee
 *      Response: Vote counts and revenue data
 * 
 * ============================================================================
 * PROTECTED ROUTES (Authentication Required - Organizer/Admin Only)
 * ============================================================================
 * 
 * POST /v1/nominees/award-categories/{categoryId}
 *      Create a new nominee in a category
 *      Body: multipart/form-data with name, description, image (optional)
 *      Response: Created nominee with auto-generated nominee_code
 * 
 * PUT  /v1/nominees/{id}
 *      Update an existing nominee
 *      Body: JSON with fields to update
 * 
 * POST /v1/nominees/{id}
 *      Update nominee (alternative for file uploads)
 *      Body: multipart/form-data with fields to update
 * 
 * DELETE /v1/nominees/{id}
 *        Delete a nominee (fails if nominee has paid votes)
 * 
 * POST /v1/nominees/award-categories/{categoryId}/reorder
 *      Reorder nominees within a category
 *      Body: { "order": [nomineeId1, nomineeId2, ...] }
 * 
 * ============================================================================
 */

use App\Controllers\AwardNomineeController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app): void {
    $nomineeController = $app->getContainer()->get(AwardNomineeController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes (no auth required)
    $app->group('/v1', function ($group) use ($nomineeController) {
        // List nominees by category
        // Query Params: ?include_stats=true|false
        $group->get('/nominees/award-categories/{categoryId}', [$nomineeController, 'index']);

        // List all nominees for an award
        // Query Params: ?include_stats=true|false
        $group->get('/nominees/awards/{awardId}', [$nomineeController, 'getByAward']);

        // Get single nominee details by code (for voting)
        // Query Params: ?include_stats=true|false
        $group->get('/nominees/code/{code}', [$nomineeController, 'showByCode']);

        // Get single nominee details
        // Query Params: ?include_stats=true|false
        $group->get('/nominees/{id}', [$nomineeController, 'show']);

        // Get nominee statistics
        $group->get('/nominees/{id}/stats', [$nomineeController, 'getStats']);
    });

    // Protected routes (auth required - organizer/admin only)
    $app->group('/v1', function ($group) use ($nomineeController) {
        // Create new nominee (with image upload)
        $group->post('/nominees/award-categories/{categoryId}', [$nomineeController, 'create']);

        // Update nominee (with image upload)
        $group->put('/nominees/{id}', [$nomineeController, 'update']);
        // POST endpoint for update with file uploads (multipart/form-data doesn't work well with PUT)
        $group->post('/nominees/{id}', [$nomineeController, 'update']);

        // Delete nominee
        $group->delete('/nominees/{id}', [$nomineeController, 'delete']);

        // Reorder nominees
        $group->post('/nominees/award-categories/{categoryId}/reorder', [$nomineeController, 'reorder']);
    })->add($authMiddleware);
};
