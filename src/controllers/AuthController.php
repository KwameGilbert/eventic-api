<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use App\Helper\ResponseHelper;
use App\Services\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;
use Exception;

/**
 * AuthController
 * 
 * Handles all authentication endpoints:
 * - Register new users
 * - Login (email/password)
 * - Refresh tokens
 * - Logout
 * - Get current user info
 */
class AuthController
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     * POST /auth/register
     */
    public function register(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $metadata = $this->getRequestMetadata($request);

            // Validation
            $errors = $this->validateRegistration($data);
            if (!empty($errors)) {
                return ResponseHelper::error($response, 'Validation failed', 400, $errors);
            }

            // Check if user already exists
            $existingUser = User::where('email', $data['email'])->first();
            if ($existingUser) {
                return ResponseHelper::error($response, 'Account already exists with this email', 409);
            }

            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $this->authService->hashPassword($data['password']),
                'role' => $data['role'] ?? User::ROLE_ATTENDEE,
                'status' => User::STATUS_ACTIVE,
                'email_verified' => false,
                'first_login' => true
            ]);

            // Log registration event
            $this->authService->logAuditEvent($user->id, 'register', $metadata);

            // Generate tokens
            $userPayload = $this->authService->generateUserPayload($user);
            $accessToken = $this->authService->generateAccessToken($userPayload);
            $refreshToken = $this->authService->createRefreshToken($user->id, $metadata);

            return ResponseHelper::success($response, 'User registered successfully', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $this->authService->getTokenExpiry()
            ], 201);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Registration failed', 500, $e->getMessage());
        }
    }

    /**
     * Login user
     * POST /auth/login
     */
    public function login(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $metadata = $this->getRequestMetadata($request);

            // Validation
            if (empty($data['email']) || empty($data['password'])) {
                return ResponseHelper::error($response, 'Email and password are required', 400);
            }

            // Find user
            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                // Log failed login attempt (no user found)
                $this->authService->logAuditEvent(null, 'login_failed', array_merge($metadata, [
                    'extra' => ['reason' => 'user_not_found', 'email' => $data['email']]
                ]));
                return ResponseHelper::error($response, 'Invalid credentials', 401);
            }

            // Verify password
            if (!$this->authService->verifyPassword($data['password'], $user->password)) {
                // Log failed login attempt (wrong password)
                $this->authService->logAuditEvent($user->id, 'login_failed', array_merge($metadata, [
                    'extra' => ['reason' => 'invalid_password']
                ]));
                return ResponseHelper::error($response, 'Invalid credentials', 401);
            }

            // Check if user is active
            if ($user->status !== User::STATUS_ACTIVE) {
                // Log suspended account login attempt
                $this->authService->logAuditEvent($user->id, 'login_failed', array_merge($metadata, [
                    'extra' => ['reason' => 'account_suspended']
                ]));
                return ResponseHelper::error($response, 'Account is suspended', 403);
            }

            // Generate tokens
            $userPayload = $this->authService->generateUserPayload($user);
            $accessToken = $this->authService->generateAccessToken($userPayload);
            $refreshToken = $this->authService->createRefreshToken($user->id, $metadata);

            // Update first_login flag and last login info
            if ($user->first_login) {
                $user->update([
                    'first_login' => false,
                    'last_login_at' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $metadata['ip_address']
                ]);
            } else {
                $user->update([
                    'last_login_at' => date('Y-m-d H:i:s'),
                    'last_login_ip' => $metadata['ip_address']
                ]);
            }

            // Log successful login event
            $this->authService->logAuditEvent($user->id, 'login', $metadata);

            return ResponseHelper::success($response, 'Login successful', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'first_login' => false
                ],
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $this->authService->getTokenExpiry()
            ], 200);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Login failed', 500, $e->getMessage());
        }
    }

    /**
     * Refresh access token
     * POST /auth/refresh
     */
    public function refresh(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (empty($data['refresh_token'])) {
                return ResponseHelper::error($response, 'Refresh token is required', 400);
            }

            $metadata = $this->getRequestMetadata($request);
            $tokens = $this->authService->refreshAccessToken($data['refresh_token'], $metadata);

            if (!$tokens) {
                return ResponseHelper::error($response, 'Invalid or expired refresh token', 401);
            }

            return ResponseHelper::success($response, 'Token refreshed successfully', $tokens, 200);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Token refresh failed', 500, $e->getMessage());
        }
    }

    /**
     * Get current authenticated user
     * GET /auth/me
     */
    public function me(Request $request, Response $response): Response
    {
        try {
            // User data is added by AuthMiddleware
            $userData = $request->getAttribute('user');

            if (!$userData) {
                return ResponseHelper::error($response, 'User not authenticated', 401);
            }

            // Fetch fresh user data from database
            $user = User::find($userData->id);

            if (!$user) {
                return ResponseHelper::error($response, 'User not found', 404);
            }

            return ResponseHelper::success($response, 'User details fetched successfully', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'status' => $user->status,
                'email_verified' => $user->email_verified,
                'created_at' => $user->created_at
            ], 200);

        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch user data', 500, $e->getMessage());
        }
    }

    /**
     * Logout (Revoke refresh token)
     * POST /auth/logout
     */
    public function logout(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            if (!empty($data['refresh_token'])) {
                $this->authService->revokeRefreshToken($data['refresh_token']);
            }
            
            return ResponseHelper::success($response, 'Logged out successfully', [], 200);
        } catch (Exception $e) {
            // Even if revocation fails, we return success to the client
            return ResponseHelper::success($response, 'Logged out successfully', [], 200);
        }
    }

    /**
     * Validate registration data
     */
    private function validateRegistration(array $data): array
    {
        $errors = [];

        if (empty($data['name']) || !v::stringType()->length(2, 255)->validate($data['name'])) {
            $errors['name'] = 'Name must be between 2 and 255 characters';
        }

        if (empty($data['email']) || !v::email()->validate($data['email'])) {
            $errors['email'] = 'Valid email is required';
        }

        if (empty($data['password']) || !v::stringType()->length(8, null)->validate($data['password'])) {
            $errors['password'] = 'Password must be at least 8 characters';
        }

        if (isset($data['role']) && !in_array($data['role'], [
            User::ROLE_ADMIN, 
            User::ROLE_ORGANIZER, 
            User::ROLE_ATTENDEE, 
            User::ROLE_POS, 
            User::ROLE_SCANNER
        ])) {
            $errors['role'] = 'Invalid role';
        }

        return $errors;
    }

    /**
     * Extract metadata from request
     */
    private function getRequestMetadata(Request $request): array
    {
        $serverParams = $request->getServerParams();
        
        return [
            'ip_address' => $serverParams['REMOTE_ADDR'] ?? null,
            'user_agent' => $request->getHeaderLine('User-Agent'),
            'device_name' => $request->getHeaderLine('X-Device-Name') // Optional custom header
        ];
    }
}
