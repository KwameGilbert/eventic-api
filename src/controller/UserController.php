<?php

declare(strict_types=1);

require_once MODEL . 'User.php';
require_once MODEL . 'Product.php';

/**
 * UserController
 * Handles user-related operations using Eloquent ORM
 */
class UserController
{
    /**
     * Get all users
     */
    public function index($request, $response, $args)
    {
        try {
            $users = User::all();
            
            $data = [
                'success' => true,
                'data' => $users,
                'count' => $users->count()
            ];
            
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['success' => false, 'error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Get single user by ID
     */
    public function show($request, $response, $args)
    {
        try {
            $id = $args['id'];
            $user = User::find($id);
            
            if (!$user) {
                $data = ['success' => false, 'message' => 'User not found'];
                $response->getBody()->write(json_encode($data));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            $data = ['success' => true, 'data' => $user];
            $response->getBody()->write(json_encode($data));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['success' => false, 'error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Create new user
     */
    public function create($request, $response, $args)
    {
        try {
            $data = $request->getParsedBody();
            
            // Validate required fields
            if (!isset($data['name']) || !isset($data['email'])) {
                $error = ['success' => false, 'message' => 'Name and email are required'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
            }
            
            // Check if email already exists
            if (User::emailExists($data['email'])) {
                $error = ['success' => false, 'message' => 'Email already exists'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $user = User::create($data);
            
            $result = ['success' => true, 'data' => $user, 'message' => 'User created successfully'];
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } catch (\Exception $e) {
            $error = ['success' => false, 'error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Update user
     */
    public function update($request, $response, $args)
    {
        try {
            $id = $args['id'];
            $data = $request->getParsedBody();
            
            $user = User::find($id);
            
            if (!$user) {
                $error = ['success' => false, 'message' => 'User not found'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            // Check email uniqueness if email is being updated
            if (isset($data['email']) && User::emailExists($data['email'], $id)) {
                $error = ['success' => false, 'message' => 'Email already exists'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
            }
            
            // Hash password if provided
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $user->update($data);
            
            $result = ['success' => true, 'data' => $user, 'message' => 'User updated successfully'];
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['success' => false, 'error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }

    /**
     * Delete user
     */
    public function delete($request, $response, $args)
    {
        try {
            $id = $args['id'];
            $user = User::find($id);
            
            if (!$user) {
                $error = ['success' => false, 'message' => 'User not found'];
                $response->getBody()->write(json_encode($error));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            
            $user->delete();
            
            $result = ['success' => true, 'message' => 'User deleted successfully'];
            $response->getBody()->write(json_encode($result));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            $error = ['success' => false, 'error' => $e->getMessage()];
            $response->getBody()->write(json_encode($error));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
