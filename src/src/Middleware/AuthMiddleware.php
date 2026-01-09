<?php

namespace Vaulty\Middleware;

use Vaulty\Services\AuthService;
use Vaulty\Utils\Request;
use Vaulty\Utils\Response;

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function authenticate(): ?array
    {
        $token = Request::getBearerToken();
        
        if (!$token) {
            Response::unauthorized("Missing authentication token");
            return null;
        }

        try {
            $decoded = $this->authService->validateToken($token);
            if (!$decoded) {
                Response::unauthorized("Invalid token");
                return null;
            }
            return $decoded;
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
            return null;
        }
    }

    public function requireRole(string $requiredRole): ?array
    {
        $user = $this->authenticate();
        if (!$user) {
            return null;
        }

        if ($user['role'] !== $requiredRole && $user['role'] !== 'admin') {
            Response::forbidden("Insufficient permissions");
            return null;
        }

        return $user;
    }

    public function requireAdmin(): ?array
    {
        return $this->requireRole('admin');
    }
}