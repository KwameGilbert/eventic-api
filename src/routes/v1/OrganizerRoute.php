<?php

/**
 * Organizer Routes (v1 API)
 */

use App\Controllers\OrganizerController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app): void {
    // Get controller and middleware from container
    $organizerController = $app->getContainer()->get(OrganizerController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Public organizer routes
    $app->group('/v1/organizers', function ($group) use ($organizerController) {
        $group->get('', [$organizerController, 'index']);

        // Query Params: ?query={search_term}
        $group->get('/search', [$organizerController, 'search']);
        $group->get('/{id}', [$organizerController, 'show']);
    });

    // Protected organizer routes (require authentication)
    $app->group('/v1/organizers', function ($group) use ($organizerController) {
        // Dashboard - fetch all dashboard data in a single call
        $group->get('/data/dashboard', [$organizerController, 'getDashboard']);

        // Events - fetch all events for the organizer
        $group->get('/data/events', [$organizerController, 'getEvents']);

        // CRUD operations
        $group->post('', [$organizerController, 'create']);
        $group->put('/{id}', [$organizerController, 'update']);
        $group->delete('/{id}', [$organizerController, 'delete']);
    })->add($authMiddleware);
};
