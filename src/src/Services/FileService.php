<?php

namespace Vaulty\Services;

use Vaulty\Models\File;
use Vaulty\Models\Project;
use Ramsey\Uuid\Uuid;

class FileService
{
    private File $fileModel;
    private Project $projectModel;
    private string $uploadDir;

    public function __construct()
    {
        $this->fileModel = new File();
        $this->projectModel = new Project();
        // Force local storage path relative to project root (src/uploads)
        // Using 'uploads' in root of src because 'storage' folder is root-owned and locked.
        $this->uploadDir = dirname(__DIR__, 2) . '/uploads';

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0775, true);
        }
    }

    public function upload(array $file, int $projectId, ?int $userId = null, array $metadata = []): array
    {
        // Validate project access
        if ($userId && !$this->projectModel->hasAccess($projectId, $userId)) {
            throw new \Exception("Access denied to project");
        }

        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception("File upload error: " . $file['error']);
        }

        $maxSize = (int)($_ENV['MAX_FILE_SIZE'] ?? 100 * 1024 * 1024); // 100MB default
        if ($file['size'] > $maxSize) {
            throw new \Exception("File size exceeds limit");
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uuid = Uuid::uuid4()->toString();
        $filename = $uuid . ($extension ? '.' . $extension : '');
        $storagePath = $this->uploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $storagePath)) {
            throw new \Exception("Failed to save file");
        }

        // Set proper permissions
        chmod($storagePath, 0664);

        // Get MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $storagePath);
        finfo_close($finfo);

        // Create database record
        $fileId = $this->fileModel->create(
            $projectId,
            $file['name'],
            $filename,
            $storagePath,
            $mimeType,
            $file['size'],
            $userId,
            $metadata
        );

        // Update generated columns if metadata was provided
        if (!empty($metadata)) {
            $this->fileModel->update($fileId, ['metadata' => $metadata]);
        }

        return [
            'success' => true,
            'file_id' => $fileId,
            'filename' => $filename,
            'original_name' => $file['name'],
            'size' => $file['size'],
            'mime_type' => $mimeType,
            'storage_path' => $storagePath
        ];
    }

    public function download(int $fileId, ?int $userId = null): array
    {
        $file = $this->fileModel->findById($fileId);
        if (!$file) {
            throw new \Exception("File not found");
        }

        // Check project access
        if ($userId && !$this->projectModel->hasAccess($file['project_id'], $userId)) {
            throw new \Exception("Access denied");
        }

        // Check if file exists
        if (!file_exists($file['storage_path'])) {
            throw new \Exception("File not found on disk");
        }

        // Increment download counter
        $this->fileModel->incrementDownload($fileId);

        return [
            'success' => true,
            'file' => $file,
            'content' => base64_encode(file_get_contents($file['storage_path']))
        ];
    }

    public function getPublicFile(string $filename): array
    {
        $file = $this->fileModel->findByFilenamePublic($filename);

        if (!$file) {
            throw new \Exception("File not found or not public");
        }

        if (!file_exists($file['storage_path'])) {
            throw new \Exception("File not found on disk");
        }

        $this->fileModel->incrementDownload($file['id']);

        return [
            'success' => true,
            'file' => $file,
            'content' => base64_encode(file_get_contents($file['storage_path']))
        ];
    }

    public function listFiles(int $projectId, ?int $userId = null, int $limit = 50, int $offset = 0): array
    {
        if ($userId && !$this->projectModel->hasAccess($projectId, $userId)) {
            throw new \Exception("Access denied to project");
        }

        $files = $this->fileModel->findByProject($projectId, $limit, $offset);

        return [
            'success' => true,
            'files' => $files,
            'count' => count($files)
        ];
    }

    public function searchFiles(int $projectId, string $query, ?int $userId = null): array
    {
        if ($userId && !$this->projectModel->hasAccess($projectId, $userId)) {
            throw new \Exception("Access denied to project");
        }

        $files = $this->fileModel->search($projectId, $query);

        return [
            'success' => true,
            'files' => $files,
            'count' => count($files)
        ];
    }

    public function deleteFile(int $fileId, ?int $userId = null): array
    {
        $file = $this->fileModel->findById($fileId);
        if (!$file) {
            throw new \Exception("File not found");
        }

        if ($userId && !$this->projectModel->hasAccess($file['project_id'], $userId)) {
            throw new \Exception("Access denied");
        }

        $success = $this->fileModel->delete($fileId);

        return [
            'success' => $success,
            'message' => $success ? 'File deleted successfully' : 'Failed to delete file'
        ];
    }

    public function updateMetadata(int $fileId, array $metadata, ?int $userId = null): array
    {
        $file = $this->fileModel->findById($fileId);
        if (!$file) {
            throw new \Exception("File not found");
        }

        if ($userId && !$this->projectModel->hasAccess($file['project_id'], $userId)) {
            throw new \Exception("Access denied");
        }

        $success = $this->fileModel->update($fileId, ['metadata' => $metadata]);

        return [
            'success' => $success,
            'message' => $success ? 'Metadata updated successfully' : 'Failed to update metadata'
        ];
    }
}
