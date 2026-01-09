<?php

namespace Vaulty\Models;

use Vaulty\Config\Database;
use PDO;
use Ramsey\Uuid\Uuid;

class File
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM files WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByProject(int $projectId, int $limit = 50, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM files WHERE project_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$projectId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function findByHash(string $hash): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM files WHERE file_hash = ?");
        $stmt->execute([$hash]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $projectId, string $originalName, string $filename, string $storagePath, string $mimeType, int $size, ?int $uploadedBy = null, array $metadata = []): int
    {
        $hash = hash_file('sha256', $storagePath);

        $stmt = $this->db->prepare(
            "INSERT INTO files (project_id, filename, original_name, mime_type, file_size, file_hash, storage_path, uploaded_by, metadata) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $metadataJson = json_encode($metadata);
        $stmt->execute([$projectId, $filename, $originalName, $mimeType, $size, $hash, $storagePath, $uploadedBy, $metadataJson]);

        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['filename', 'metadata', 'is_public'];
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                if ($key === 'metadata') {
                    $value = json_encode($value);
                }
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE files SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $file = $this->findById($id);
        if (!$file) {
            return false;
        }

        // Delete physical file
        if (file_exists($file['storage_path'])) {
            unlink($file['storage_path']);
        }

        $stmt = $this->db->prepare("DELETE FROM files WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function incrementDownload(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE files SET download_count = COALESCE(download_count, 0) + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function search(int $projectId, string $query, int $limit = 50): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM files 
             WHERE project_id = ? AND (
                filename LIKE ? OR 
                original_name LIKE ? OR 
                metadata_description LIKE ? OR 
                metadata_tags LIKE ?
             )
             ORDER BY created_at DESC LIMIT ?"
        );
        $searchTerm = "%{$query}%";
        $stmt->execute([$projectId, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit]);
        return $stmt->fetchAll();
    }

    public function findByFilenamePublic(string $filename): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT f.* FROM files f 
             JOIN projects p ON f.project_id = p.id 
             WHERE f.filename = ? AND f.is_public = 1 AND p.is_public = 1"
        );
        $stmt->execute([$filename]);
        return $stmt->fetch() ?: null;
    }
}
