<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use Slim\App;

/**
 * Authentication Routes
 * 
 * All authentication-related endpoints
 * Prefix: /v1/auth
 */

return function (App $app) {
    // Get auth controller from container
    $authController = $app->getContainer()->get(AuthController::class);
    
    // Public routes (no authentication required)
    $app->post('/v1/auth/register', [$authController, 'register']);
    $app->post('/v1/auth/login', [$authController, 'login']);
    $app->post('/v1/auth/refresh', [$authController, 'refresh']);
    
    // Protected routes (authentication required)
    $app->group('/v1/auth', function ($group) use ($authController) {
        $group->get('/me', [$authController, 'me']);
        $group->post('/logout', [$authController, 'logout']);
    })->add($app->getContainer()->get(AuthMiddleware::class));
};
