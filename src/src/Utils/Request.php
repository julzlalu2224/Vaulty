<?php

namespace Vaulty\Utils;

class Request
{
    public static function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public static function getPath(): string
    {
        // Try PATH_INFO first
        if (isset($_SERVER['PATH_INFO'])) {
            return rtrim($_SERVER['PATH_INFO'], '/');
        }

        // Try REQUEST_URI (includes query string)
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            // Remove query string
            $path = parse_url($uri, PHP_URL_PATH);
            return rtrim($path, '/');
        }

        // Fallback
        return '/';
    }

    public static function getJson(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    public static function getParam(string $key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }

    public static function getHeader(string $name): ?string
    {
        // Special handling for Authorization header
        if (strcasecmp($name, 'Authorization') === 0) {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                return $_SERVER['HTTP_AUTHORIZATION'];
            }
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }
            if (function_exists('apache_request_headers')) {
                $headers = apache_request_headers();
                if (isset($headers['Authorization'])) {
                    return $headers['Authorization'];
                }
                // Check case-insensitive
                $headers = array_change_key_case($headers, CASE_LOWER);
                if (isset($headers['authorization'])) {
                    return $headers['authorization'];
                }
            }
        }

        $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$name] ?? null;
    }

    public static function getBearerToken(): ?string
    {
        $auth = self::getHeader('Authorization');
        if ($auth && preg_match('/Bearer\s+(.+)/', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }

    public static function getApiKey(): ?string
    {
        // Check header
        $apiKey = self::getHeader('X-API-Key');
        if ($apiKey) {
            return $apiKey;
        }

        // Check query parameter
        return self::getParam('api_key');
    }

    public static function getClientIP(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Handle proxies
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            $ip = $_SERVER['HTTP_X_REAL_IP'];
        }

        return $ip;
    }

    public static function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    }

    public static function validateMethod(string $expected): bool
    {
        return self::getMethod() === $expected;
    }

    public static function isOptions(): bool
    {
        return self::getMethod() === 'OPTIONS';
    }
}
