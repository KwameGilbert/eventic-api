<?php

/**
 * Admin Routes (v1 API)
 */

use App\Controllers\AdminController;
use App\Middleware\AuthMiddleware;
use Slim\App;

return function (App $app): void {
    // Get controller and middleware from container
    $adminController = $app->getContainer()->get(AdminController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);

    // Protected admin routes (require admin authentication)
    $app->group('/v1/admin', function ($group) use ($adminController) {
        // Dashboard
        $group->get('/dashboard', [$adminController, 'getDashboard']);

        // Users Management
        $group->get('/users', [$adminController, 'getUsers']);

        // Event Approvals
        $group->put('/events/{id}/approve', [$adminController, 'approveEvent']);
        $group->put('/events/{id}/reject', [$adminController, 'rejectEvent']);

        // Award Approvals
        $group->put('/awards/{id}/approve', [$adminController, 'approveAward']);
        $group->put('/awards/{id}/reject', [$adminController, 'rejectAward']);
    })->add($authMiddleware);
};
