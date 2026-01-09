<?php

namespace Vaulty\Models;

use Vaulty\Config\Database;
use PDO;

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $username, string $email, string $passwordHash, string $role = 'user'): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$username, $email, $passwordHash, $role]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['username', 'email', 'password_hash'];
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
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function validatePassword(int $id, string $password): bool
    {
        $user = $this->findById($id);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password_hash']);
    }
}