<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Services\AuthService;
use App\Helper\ResponseHelper;

/**
 * Authentication Middleware
 *
 * Validates Bearer tokens in Authorization header and protects routes.
 * Adds authenticated user data to request attributes for use in controllers.
 */
class AuthMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Process incoming request and validate authentication
     *
     * @param Request $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Get Authorization header
        $authHeader = $request->getHeaderLine('Authorization');

        if (empty($authHeader)) {
            return $this->createUnauthorizedResponse('Authorization header is required');
        }

        // Check if it's a Bearer token
        $token = $this->authService->extractTokenFromHeader($authHeader);

        if (!$token) {
            return $this->createUnauthorizedResponse('Invalid authorization header format. Expected: Bearer <token>');
        }

        // Validate the JWT token
        $decoded = $this->authService->validateToken($token);

        if ($decoded === null) {
            return $this->createUnauthorizedResponse('Invalid or expired token');
        }

        // Add user data to request attributes for use in controllers
        $request = $request->withAttribute('user', $decoded->data);

        // Continue with the request
        return $handler->handle($request);
    }

    /**
     * Create an unauthorized response
     *
     * @param string $message
     * @return Response
     */
    private function createUnauthorizedResponse(string $message): Response
    {
        $response = new \Slim\Psr7\Response();
        return ResponseHelper::error($response, $message, 401);
    }
}