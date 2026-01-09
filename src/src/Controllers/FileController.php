<?php

namespace Vaulty\Controllers;

use Vaulty\Services\FileService;
use Vaulty\Middleware\AuthMiddleware;
use Vaulty\Middleware\ApiKeyMiddleware;
use Vaulty\Utils\Request;
use Vaulty\Utils\Response;

class FileController
{
    private FileService $fileService;
    private AuthMiddleware $authMiddleware;
    private ApiKeyMiddleware $apiKeyMiddleware;

    public function __construct()
    {
        $this->fileService = new FileService();
        $this->authMiddleware = new AuthMiddleware();
        $this->apiKeyMiddleware = new ApiKeyMiddleware();
    }

    public function upload(): void
    {
        if (!Request::validateMethod('POST')) {
            Response::error("Method not allowed", 405);
        }

        // Try API key auth if key is present
        if (Request::getApiKey()) {
            $authResult = $this->apiKeyMiddleware->authenticateWithUser();
            if (!$authResult) {
                // If API key is invalid, response is already sent by middleware
                return;
            }
            $projectId = $authResult['project']['id'];
            $userId = $authResult['user'] ? $authResult['user']['user_id'] : null;
        } else {
            // Otherwise try token auth
            $user = $this->authMiddleware->authenticate();
            if (!$user) {
                return;
            }
            $projectId = Request::getParam('project_id');
            $userId = $user['user_id'];
        }

        if (empty($projectId)) {
            Response::validationError(['project_id' => 'Required field']);
        }

        if (!isset($_FILES['file'])) {
            Response::validationError(['file' => 'No file uploaded']);
        }

        $metadata = json_decode(Request::getParam('metadata', '[]'), true);

        try {
            $result = $this->fileService->upload(
                $_FILES['file'],
                $projectId,
                $userId,
                $metadata
            );
            Response::success("File uploaded successfully", $result, 201);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function download(int $fileId): void
    {
        if (!Request::validateMethod('GET')) {
            Response::error("Method not allowed", 405);
        }

        // Try API key auth if key is present
        $userId = null;
        if (Request::getApiKey()) {
            $authResult = $this->apiKeyMiddleware->authenticateWithUser();
            if ($authResult) {
                $userId = $authResult['user'] ? $authResult['user']['user_id'] : null;
            } else {
                return;
            }
        } else {
            $user = $this->authMiddleware->authenticate();
            if ($user) {
                $userId = $user['user_id'];
            } else {
                return;
            }
        }

        try {
            $result = $this->fileService->download($fileId, $userId);
            Response::success("File retrieved", $result);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function publicDownload(string $filename): void
    {
        if (!Request::validateMethod('GET')) {
            Response::error("Method not allowed", 405);
        }

        try {
            $result = $this->fileService->getPublicFile($filename);
            Response::success("File retrieved", $result);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function list(int $projectId): void
    {
        if (!Request::validateMethod('GET')) {
            Response::error("Method not allowed", 405);
        }

        // Try API key auth if key is present
        $userId = null;
        if (Request::getApiKey()) {
            $authResult = $this->apiKeyMiddleware->authenticateWithUser();
            if ($authResult) {
                $userId = $authResult['user'] ? $authResult['user']['user_id'] : null;
            } else {
                return;
            }
        } else {
            $user = $this->authMiddleware->authenticate();
            if ($user) {
                $userId = $user['user_id'];
            } else {
                return;
            }
        }

        $limit = (int)(Request::getParam('limit', 50));
        $offset = (int)(Request::getParam('offset', 0));

        try {
            $result = $this->fileService->listFiles($projectId, $userId, $limit, $offset);
            Response::success("Files retrieved", $result);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function search(int $projectId): void
    {
        if (!Request::validateMethod('GET')) {
            Response::error("Method not allowed", 405);
        }

        $query = Request::getParam('q');
        if (empty($query)) {
            Response::validationError(['q' => 'Search query required']);
        }

        // Try API key auth if key is present
        $userId = null;
        if (Request::getApiKey()) {
            $authResult = $this->apiKeyMiddleware->authenticateWithUser();
            if ($authResult) {
                $userId = $authResult['user'] ? $authResult['user']['user_id'] : null;
            } else {
                return;
            }
        } else {
            $user = $this->authMiddleware->authenticate();
            if ($user) {
                $userId = $user['user_id'];
            } else {
                return;
            }
        }

        try {
            $result = $this->fileService->searchFiles($projectId, $query, $userId);
            Response::success("Search results", $result);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function delete(int $fileId): void
    {
        if (!Request::validateMethod('DELETE')) {
            Response::error("Method not allowed", 405);
        }

        $user = $this->authMiddleware->authenticate();
        if (!$user) {
            return;
        }

        try {
            $result = $this->fileService->deleteFile($fileId, $user['user_id']);
            Response::success($result['message']);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }

    public function updateMetadata(int $fileId): void
    {
        if (!Request::validateMethod('PUT')) {
            Response::error("Method not allowed", 405);
        }

        $user = $this->authMiddleware->authenticate();
        if (!$user) {
            return;
        }

        $data = Request::getJson();
        $metadata = $data['metadata'] ?? [];

        try {
            $result = $this->fileService->updateMetadata($fileId, $metadata, $user['user_id']);
            Response::success($result['message']);
        } catch (\Exception $e) {
            Response::error($e->getMessage());
        }
    }
}
