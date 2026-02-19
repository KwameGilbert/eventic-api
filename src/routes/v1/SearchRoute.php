<?php

/**
 * Search Routes (v1 API)
 */

use App\Controllers\SearchController;
use Slim\App;

return function (App $app): void {
    // Get controller from container
    $searchController = $app->getContainer()->get(SearchController::class);

    // Public search routes
    $app->group('/v1/search', function ($group) use ($searchController) {
        $group->get('', [$searchController, 'globalSearch']);
    });
};
