<?php

/**
 * Middleware Configuration
 * 
 * Registers all application middleware
 */

use Slim\Middleware\ContentLengthMiddleware;
use App\Middleware\RequestResponseLoggerMiddleware;

return function ($app, $container, $config) {
    
    // Get configurations
    $environment = $config['env'];
    $corsConfig = require CONFIG . '/cors.php';
    
    // ==================== ERROR HANDLING ====================
    
    // Configure error middleware with custom handler
    $errorMiddleware = $app->addErrorMiddleware(
        displayErrorDetails: $environment === 'development',
        logErrors: true,
        logErrorDetails: $environment === 'development',
        logger: $container->get('logger')
    );
    
    // Set custom error handler
    $errorHandler = new \App\Helper\ErrorHandler(
        $container->get('logger'),
        $environment
    );
    $errorMiddleware->setDefaultErrorHandler($errorHandler);
    
    // ==================== HTTP LOGGING ====================
    
    // Add HTTP logger middleware if httpLogger exists
    if ($container->has('httpLogger')) {
        $app->add(new RequestResponseLoggerMiddleware($container->get('httpLogger')));
    }
    
    // ==================== CORS ====================
    
    // Add CORS middleware
    $app->add(function ($request, $handler) use ($corsConfig) {
        $response = $handler->handle($request);
        $allowedOrigins = $corsConfig['allowed_origins'];
        $allowCredentials = is_callable($corsConfig['allow_credentials']) 
            ? $corsConfig['allow_credentials']($allowedOrigins) 
            : $corsConfig['allow_credentials'];
            
        return $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigins)
            ->withHeader('Access-Control-Allow-Headers', $corsConfig['allowed_headers'])
            ->withHeader('Access-Control-Allow-Methods', $corsConfig['allowed_methods'])
            ->withHeader('Access-Control-Allow-Credentials', $allowCredentials)
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Access-Control-Max-Age', (string)$corsConfig['max_age']);
    });
    
    // Handle preflight OPTIONS requests
    $app->options('/{routes:.+}', function ($request, $response) use ($corsConfig) {
        $allowedOrigins = $corsConfig['allowed_origins'];
        $allowCredentials = is_callable($corsConfig['allow_credentials']) 
            ? $corsConfig['allow_credentials']($allowedOrigins) 
            : $corsConfig['allow_credentials'];
            
        return $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigins)
            ->withHeader('Access-Control-Allow-Headers', $corsConfig['allowed_headers'])
            ->withHeader('Access-Control-Allow-Methods', $corsConfig['allowed_methods'])
            ->withHeader('Access-Control-Allow-Credentials', $allowCredentials)
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Access-Control-Max-Age', (string)$corsConfig['max_age']);
    });
    
    // ==================== CONTENT LENGTH ====================
    
    $app->add(new ContentLengthMiddleware());
    
    return $app;
};
