<?php

namespace Vaulty\Services;

use Vaulty\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class AuthService
{
    private User $userModel;
    private string $jwtSecret;
    private int $jwtExpiry;

    public function __construct()
    {
        $this->userModel = new User();
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'vaulty_secret_key_change_in_production';
        $this->jwtExpiry = (int)($_ENV['JWT_EXPIRY'] ?? 3600); // 1 hour
    }

    public function register(string $username, string $email, string $password): array
    {
        // Check if user exists
        if ($this->userModel->findByUsername($username)) {
            throw new \Exception("Username already exists");
        }

        if ($this->userModel->findByEmail($email)) {
            throw new \Exception("Email already exists");
        }

        // Validate password strength
        if (strlen($password) < 8) {
            throw new \Exception("Password must be at least 8 characters");
        }

        $passwordHash = password_hash($password, PASSWORD_ARGON2ID);
        $userId = $this->userModel->create($username, $email, $passwordHash);

        return [
            'success' => true,
            'message' => 'User registered successfully',
            'user_id' => $userId
        ];
    }

    public function login(string $username, string $password): array
    {
        $user = $this->userModel->findByUsername($username);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new \Exception("Invalid credentials");
        }

        $token = $this->generateToken($user);

        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ];
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array)$decoded;
        } catch (ExpiredException $e) {
            throw new \Exception("Token expired");
        } catch (SignatureInvalidException $e) {
            throw new \Exception("Invalid token");
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCurrentUser(string $token): ?array
    {
        $decoded = $this->validateToken($token);
        if (!$decoded) {
            return null;
        }

        return $this->userModel->findById($decoded['user_id']);
    }

    private function generateToken(array $user): string
    {
        $payload = [
            'iss' => 'vaulty',
            'iat' => time(),
            'exp' => time() + $this->jwtExpiry,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role']
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    public function refreshToken(string $token): string
    {
        $decoded = $this->validateToken($token);
        if (!$decoded) {
            throw new \Exception("Invalid token");
        }

        $user = $this->userModel->findById($decoded['user_id']);
        if (!$user) {
            throw new \Exception("User not found");
        }

        return $this->generateToken($user);
    }
}