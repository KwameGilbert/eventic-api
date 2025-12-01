<?php

/**
 * Organizer Routes (v1 API)
 */

use App\Controllers\OrganizerController;
use Slim\App;

return function (App $app): void {
    // Get controller from container
    $organizerController = $app->getContainer()->get(OrganizerController::class);
    
    // Organizer routes
    $app->group('/v1/organizers', function ($group) use ($organizerController) {
        $group->get('', [$organizerController, 'index']);
        $group->get('/search', [$organizerController, 'search']); // 
        $group->get('/{id}', [$organizerController, 'show']);
        $group->post('', [$organizerController, 'create']);
        $group->put('/{id}', [$organizerController, 'update']);
        $group->delete('/{id}', [$organizerController, 'delete']);
    });
};
