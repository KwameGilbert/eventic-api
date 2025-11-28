<?php

/**
 * User Routes (v1 API)
 * Demonstrates Eloquent ORM usage in Slim Framework
 */

require_once CONTROLLER . 'UserController.php';

return function ($app): void {
    $userController = new UserController();
    
    // User routes
    $app->get('/v1/users', [$userController, 'index']);
    $app->get('/v1/users/{id}', [$userController, 'show']);
    $app->post('/v1/users', [$userController, 'create']);
    $app->put('/v1/users/{id}', [$userController, 'update']);
    $app->delete('/v1/users/{id}', [$userController, 'delete']);
};
