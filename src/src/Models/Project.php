<?php

namespace Vaulty\Models;

use Vaulty\Config\Database;
use PDO;

class Project
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByApiKey(string $apiKey): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE api_key = ?");
        $stmt->execute([$apiKey]);
        return $stmt->fetch() ?: null;
    }

    public function findByOwner(int $ownerId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE owner_id = ?");
        $stmt->execute([$ownerId]);
        return $stmt->fetchAll();
    }

    public function create(string $name, int $ownerId, string $description = '', bool $isPublic = false): int
    {
        $apiKey = $this->generateApiKey();
        $stmt = $this->db->prepare(
            "INSERT INTO projects (name, description, owner_id, is_public, api_key) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$name, $description, $ownerId, $isPublic, $apiKey]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'description', 'is_public'];
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE projects SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function hasAccess(int $projectId, int $userId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM projects WHERE id = ? AND owner_id = ?");
        $stmt->execute([$projectId, $userId]);
        return $stmt->fetch() !== false;
    }

    private function generateApiKey(): string
    {
        return bin2hex(random_bytes(32));
    }
}