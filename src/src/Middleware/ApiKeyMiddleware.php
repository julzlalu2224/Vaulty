<?php

namespace Vaulty\Middleware;

use Vaulty\Models\Project;
use Vaulty\Utils\Request;
use Vaulty\Utils\Response;

class ApiKeyMiddleware
{
    private Project $projectModel;

    public function __construct()
    {
        $this->projectModel = new Project();
    }

    public function authenticate(): ?array
    {
        $apiKey = Request::getApiKey();
        
        if (!$apiKey) {
            Response::unauthorized("Missing API key");
            return null;
        }

        $project = $this->projectModel->findByApiKey($apiKey);
        
        if (!$project) {
            Response::unauthorized("Invalid API key");
            return null;
        }

        return $project;
    }

    public function authenticateWithUser(): ?array
    {
        $project = $this->authenticate();
        if (!$project) {
            return null;
        }

        // Also get user info if token is provided
        $token = Request::getBearerToken();
        if ($token) {
            $authMiddleware = new AuthMiddleware();
            $user = $authMiddleware->authenticate();
            if ($user) {
                return [
                    'project' => $project,
                    'user' => $user
                ];
            }
        }

        return [
            'project' => $project,
            'user' => null
        ];
    }
}