<?php

/**
 * Attendee Routes (v1 API)
 */

use App\Controllers\AttendeeController;
use Slim\App;

return function (App $app): void {
    // Get controller from container
    $attendeeController = $app->getContainer()->get(AttendeeController::class);
    
    // Attendee routes
    $app->group('/v1/attendees', function ($group) use ($attendeeController) {
        $group->get('', [$attendeeController, 'index']);
        $group->get('/{id}', [$attendeeController, 'show']);
        $group->post('', [$attendeeController, 'create']);
        $group->put('/{id}', [$attendeeController, 'update']);
        $group->delete('/{id}', [$attendeeController, 'delete']);
    });
};
