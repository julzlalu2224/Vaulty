<?php

namespace Vaulty\Controllers;

use Vaulty\Models\Project;
use Vaulty\Middleware\AuthMiddleware;
use Vaulty\Utils\Request;
use Vaulty\Utils\Response;

class ProjectController
{
    private Project $projectModel;
    private AuthMiddleware $authMiddleware;

    public function __construct()
    {
        $this->projectModel = new Project();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function create(): void
    {
        if (!Request::validateMethod('POST')) {
            Response::error("Method not allowed", 405);
        }

        $user = $this->authMiddleware->authenticate();
        if (!$user) {
            return;
        }

        $data = Request::getJson();

        if (empty($data['name'])) {
            Response::validationError(['name' => 'Required field']);
        }

        try {
            $projectId = $this->projectModel->create(
                $data['name'],
                $user['user_id'],
                $data['description'] ?? '',
                $data['is_public'] ?? false
            );

            $project = $this->projectModel->findById($projectId);
            Response::success("Project created successfully", $project, 201);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function list(): void
    {
        if (!Request::validateMethod('GET')) {
            Response::error("Method not allowed", 405);
        }

        $user = $this->authMiddleware->authenticate();
        if (!$user) {
            return;
        }

        try {
            $projects = $this->projectModel->findByOwner($user['user_id']);
            Response::success("Projects retrieved", $projects);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function get(int $id): void
    {
        if (!Request::validateMethod('GET')) {
            Response::error("Method not allowed", 405);
        }

        $user = $this->authMiddleware->authenticate();
        if (!$user) {
            return;
        }

        $project = $this->projectModel->findById($id);
        if (!$project) {
            Response::notFound("Project not found");
        }

        if (!$this->projectModel->hasAccess($id, $user['user_id'])) {
            Response::forbidden("Access denied");
        }

        Response::success("Project retrieved", $project);
    }

    public function update(int $id): void
    {
        if (!Request::validateMethod('PUT')) {
            Response::error("Method not allowed", 405);
        }

        $user = $this->authMiddleware->authenticate();
        if (!$user) {
            return;
        }

        if (!$this->projectModel->hasAccess($id, $user['user_id'])) {
            Response::forbidden("Access denied");
        }

        $data = Request::getJson();
        try {
            $success = $this->projectModel->update($id, $data);
            if ($success) {
                Response::success("Project updated successfully");
            } else {
                Response::error("Failed to update project");
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        if (!Request::validateMethod('DELETE')) {
            Response::error("Method not allowed", 405);
        }

        $user = $this->authMiddleware->authenticate();
        if (!$user) {
            return;
        }

        if (!$this->projectModel->hasAccess($id, $user['user_id'])) {
            Response::forbidden("Access denied");
        }

        try {
            $success = $this->projectModel->delete($id);
            if ($success) {
                Response::success("Project deleted successfully");
            } else {
                Response::error("Failed to delete project");
            }
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }
}