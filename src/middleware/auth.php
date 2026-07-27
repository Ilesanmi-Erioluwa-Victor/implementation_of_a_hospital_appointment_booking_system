<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function generateToken(array $payload): string
{
    $payload['iat'] = time();
    $payload['exp'] = time() + (24 * 60 * 60); // 24 hours
    return JWT::encode($payload, JWT_SECRET, 'HS256');
}

function decodeToken(string $token): ?array
{
    try {
        return (array) JWT::decode($token, new Key(JWT_SECRET, 'HS256'));
    } catch (\Exception $e) {
        return null;
    }
}

function getAuthToken(): ?string
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (preg_match('/Bearer\s+(.+)$/', $authHeader, $matches)) {
        return $matches[1];
    }
    return null;
}

function requireAuth(): array
{
    $token = getAuthToken();
    if (!$token) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    $payload = decodeToken($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }
    return $payload;
}

function getCurrentUser(): ?array
{
    $token = getAuthToken();
    if (!$token) return null;
    return decodeToken($token);
}

function requirePatient(): array
{
    $payload = requireAuth();
    if ($payload['type'] !== 'patient') {
        http_response_code(403);
        echo json_encode(['error' => 'Patient access required']);
        exit;
    }
    return $payload;
}

function requireStaff(): array
{
    $payload = requireAuth();
    if ($payload['type'] !== 'staff') {
        http_response_code(403);
        echo json_encode(['error' => 'Staff access required']);
        exit;
    }
    return $payload;
}

function requireAdmin(): array
{
    $payload = requireStaff();
    if ($payload['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        exit;
    }
    return $payload;
}
