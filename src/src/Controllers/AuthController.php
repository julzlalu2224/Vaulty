<?php

namespace Vaulty\Controllers;

use Vaulty\Services\AuthService;
use Vaulty\Utils\Request;
use Vaulty\Utils\Response;

class AuthController
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function register(): void
    {
        if (!Request::validateMethod('POST')) {
            Response::error("Method not allowed", 405);
        }

        $data = Request::getJson();

        $required = ['username', 'email', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::validationError([$field => "Required field"]);
            }
        }

        try {
            $result = $this->authService->register(
                $data['username'],
                $data['email'],
                $data['password']
            );
            Response::success("User registered successfully", $result, 201);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function login(): void
    {
        if (!Request::validateMethod('POST')) {
            Response::error("Method not allowed", 405);
        }

        $data = Request::getJson();

        $required = ['username', 'password'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::validationError([$field => "Required field"]);
            }
        }

        try {
            $result = $this->authService->login($data['username'], $data['password']);
            Response::success("Login successful", $result);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function me(): void
    {
        if (!Request::validateMethod('GET')) {
            Response::error("Method not allowed", 405);
        }

        $token = Request::getBearerToken();
        if (!$token) {
            Response::unauthorized();
        }

        try {
            $user = $this->authService->getCurrentUser($token);
            if (!$user) {
                Response::unauthorized();
            }

            Response::success("User info", [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function refresh(): void
    {
        if (!Request::validateMethod('POST')) {
            Response::error("Method not allowed", 405);
        }

        $token = Request::getBearerToken();
        if (!$token) {
            Response::unauthorized();
        }

        try {
            $newToken = $this->authService->refreshToken($token);
            Response::success("Token refreshed", ['token' => $newToken]);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }
}