<?php

namespace Vaulty;

use Vaulty\Controllers\AuthController;
use Vaulty\Controllers\ProjectController;
use Vaulty\Controllers\FileController;
use Vaulty\Utils\Request;
use Vaulty\Utils\Response;

class Router
{
    private array $routes = [];

    public function __construct()
    {
        $this->setupRoutes();
    }

    private function setupRoutes(): void
    {
        // Auth routes
        $this->post('/api/auth/register', [AuthController::class, 'register']);
        $this->post('/api/auth/login', [AuthController::class, 'login']);
        $this->get('/api/auth/me', [AuthController::class, 'me']);
        $this->post('/api/auth/refresh', [AuthController::class, 'refresh']);

        // Project routes
        $this->post('/api/projects', [ProjectController::class, 'create']);
        $this->get('/api/projects', [ProjectController::class, 'list']);
        $this->get('/api/projects/{id}', [ProjectController::class, 'get']);
        $this->put('/api/projects/{id}', [ProjectController::class, 'update']);
        $this->delete('/api/projects/{id}', [ProjectController::class, 'delete']);

        // File routes
        $this->post('/api/files', [FileController::class, 'upload']);
        $this->get('/api/files/{id}', [FileController::class, 'download']);
        $this->get('/api/files/project/{projectId}', [FileController::class, 'list']);
        $this->get('/api/files/search/{projectId}', [FileController::class, 'search']);
        $this->delete('/api/files/{id}', [FileController::class, 'delete']);
        $this->put('/api/files/{id}/metadata', [FileController::class, 'updateMetadata']);

        // Public file access
        $this->get('/api/public/{filename}', [FileController::class, 'publicDownload']);
    }

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function put(string $path, array $handler): void
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function delete(string $path, array $handler): void
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function dispatch(): void
    {
        // Handle CORS preflight
        if (Request::isOptions()) {
            Response::success("OK");
        }

        $method = Request::getMethod();
        $path = Request::getPath();

        // Find matching route
        $handler = $this->findRoute($method, $path);

        if (!$handler) {
            Response::notFound("Route not found");
        }

        // Execute handler
        $controllerClass = $handler[0];
        $method = $handler[1];
        $params = $handler['params'] ?? [];

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            Response::error("Method not found", 500);
        }

        // Call method with parameters
        call_user_func_array([$controller, $method], $params);
    }

    private function findRoute(string $method, string $path): ?array
    {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = $this->routeToRegex($route);

            if (preg_match($pattern, $path, $matches)) {
                // Extract parameters
                // Remove the full match (index 0)
                array_shift($matches);

                $handler['params'] = array_values($matches);
                return $handler;
            }
        }

        return null;
    }

    private function routeToRegex(string $route): string
    {
        // Convert route pattern to regex
        // {id} becomes (\d+)
        // {filename} becomes ([^/]+)
        // {projectId} becomes (\d+)

        $pattern = preg_replace('/\{id\}/', '(\d+)', $route);
        $pattern = preg_replace('/\{projectId\}/', '(\d+)', $pattern);
        $pattern = preg_replace('/\{filename\}/', '([^/]+)', $pattern);

        return '#^' . $pattern . '$#';
    }
}
