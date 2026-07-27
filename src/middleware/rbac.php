<?php

declare(strict_types=1);

function requireRole(string $role): array
{
    $payload = requireAuth();

    $roles = [
        'admin' => ['admin'],
        'front_desk' => ['front_desk', 'admin'],
    ];

    $allowed = $roles[$role] ?? [$role];
    if (!in_array($payload['role'] ?? '', $allowed)) {
        http_response_code(403);
        echo json_encode(['error' => 'Insufficient permissions']);
        exit;
    }
    return $payload;
}
