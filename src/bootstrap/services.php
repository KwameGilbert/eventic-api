<?php

/**
 * Service Container Registration
 * 
 * Registers all services, controllers, and middleware with the DI container
 */

use App\Services\EmailService;
use App\Services\SMSService;
use App\Services\AuthService;
use App\Services\PasswordResetService;
use App\Services\VerificationService;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\OrganizerController;
use App\Controllers\PasswordResetController;
use App\Controllers\AttendeeController;
use App\Controllers\EventController;
use App\Controllers\EventImageController;
use App\Controllers\TicketTypeController;
use App\Controllers\OrderController;
use App\Controllers\TicketController;
use App\Controllers\ScannerController;
use App\Controllers\PosController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;
use App\Middleware\JsonBodyParserMiddleware;

return function ($container) {
    
    // ==================== SERVICES ====================
    
    $container->set(EmailService::class, function () {
        return new EmailService();
    });

    $container->set(SMSService::class, function () {
        return new SMSService();
    });
    
    $container->set(AuthService::class, function () {
        return new AuthService();
    });
    
    $container->set(PasswordResetService::class, function ($container) {
        return new PasswordResetService($container->get(EmailService::class));
    });
    
    $container->set(VerificationService::class, function ($container) {
        return new VerificationService($container->get(EmailService::class));
    });

    $container->set(\Psr\Http\Message\ResponseFactoryInterface::class, function () {
        return new \Slim\Psr7\Factory\ResponseFactory();
    });
    
    // ==================== CONTROLLERS ====================
    
    $container->set(AuthController::class, function ($container) {
        return new AuthController($container->get(AuthService::class));
    });
    
    $container->set(UserController::class, function () {
        return new UserController();
    });

    $container->set(OrganizerController::class, function () {
        return new OrganizerController();
    });
    
    $container->set(PasswordResetController::class, function ($container) {
        return new PasswordResetController(
            $container->get(AuthService::class),
            $container->get(EmailService::class)
        );
    });

    $container->set(AttendeeController::class, function () {
        return new AttendeeController();
    });

    $container->set(EventController::class, function () {
        return new EventController();
    });

    $container->set(EventImageController::class, function () {
        return new EventImageController();
    });

    $container->set(TicketTypeController::class, function () {
        return new TicketTypeController();
    });

    $container->set(OrderController::class, function () {
        return new OrderController();
    });

    $container->set(TicketController::class, function () {
        return new TicketController();
    });

    $container->set(ScannerController::class, function () {
        return new ScannerController();
    });

    $container->set(PosController::class, function () {
        return new PosController();
    });
    
    // ==================== MIDDLEWARES ====================
    
    $container->set(AuthMiddleware::class, function ($container) {
        return new AuthMiddleware($container->get(AuthService::class));
    });
    
    $container->set(RateLimitMiddleware::class, function () {
        return new RateLimitMiddleware();
    });
    
    $container->set(JsonBodyParserMiddleware::class, function () {
        return new JsonBodyParserMiddleware();
    });

    
    return $container;
};
