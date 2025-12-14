<?php

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use App\Controllers\AwardController;
use App\Middleware\AuthMiddleware;

return function (RouteCollectorProxy $group) {
    
    // Public routes - No authentication required
    $group->get('', [AwardController::class, 'index']);
    $group->get('/featured', [AwardController::class, 'featured']);
    $group->get('/search', [AwardController::class, 'search']);
    $group->get('/{id}', [AwardController::class, 'show']);
    $group->get('/{id}/leaderboard', [AwardController::class, 'leaderboard']);
    
    // Protected routes - Require authentication (organizer or admin)
    $group->post('', [AwardController::class, 'create'])->add(AuthMiddleware::class);
    $group->put('/{id}', [AwardController::class, 'update'])->add(AuthMiddleware::class);
    $group->delete('/{id}', [AwardController::class, 'delete'])->add(AuthMiddleware::class);
};
