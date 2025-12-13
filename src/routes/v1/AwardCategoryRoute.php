<?php

/**
 * Award Category Routes (v1 API)
 */

use App\Controllers\AwardCategoryController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app): void {
    $categoryController = $app->getContainer()->get(AwardCategoryController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public routes (no auth required)
    $app->group('/v1', function ($group) use ($categoryController) {
        // List categories for an event
        // Query Params: ?include_results=true|false
        $group->get('/events/{eventId}/award-categories', [$categoryController, 'index']);

        // Get single category details
        // Query Params: ?include_results=true|false
        $group->get('/award-categories/{id}', [$categoryController, 'show']);

        // Get category statistics
        $group->get('/award-categories/{id}/stats', [$categoryController, 'getStats']);
    });

    // Protected routes (auth required - organizer/admin only)
    $app->group('/v1', function ($group) use ($categoryController) {
        // Create new category
        $group->post('/events/{eventId}/award-categories', [$categoryController, 'create']);

        // Update category
        $group->put('/award-categories/{id}', [$categoryController, 'update']);

        // Delete category
        $group->delete('/award-categories/{id}', [$categoryController, 'delete']);

        // Reorder categories
        $group->post('/events/{eventId}/award-categories/reorder', [$categoryController, 'reorder']);
    })->add($authMiddleware);
};
