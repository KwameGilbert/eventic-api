<?php

/**
 * Event Routes (v1 API)
 */

use App\Controllers\EventController;
use Slim\App;

return function (App $app): void {
    // Get controller from container
    $eventController = $app->getContainer()->get(EventController::class);
    
    // Event routes
    $app->group('/v1/events', function ($group) use ($eventController) {
        // Query Params: ?status={status}&event_type_id={id}&organizer_id={id}
        $group->get('', [$eventController, 'index']);
        // Query Params: ?query={query}
        $group->get('/search', [$eventController, 'search']);
        $group->get('/{id}', [$eventController, 'show']);
        $group->post('', [$eventController, 'create']);
        $group->put('/{id}', [$eventController, 'update']);
        $group->delete('/{id}', [$eventController, 'delete']);
    });
};
