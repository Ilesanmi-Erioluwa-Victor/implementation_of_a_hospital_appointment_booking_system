<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

use MongoDB\Client;
use MongoDB\Database;
use MongoDB\Collection;

function getMongoDB(): Database
{
    static $db = null;
    if ($db === null) {
        if (empty(MONGODB_URI)) {
            jsonError(500, 'MongoDB URI not configured');
        }
        try {
            $client = new Client(MONGODB_URI, [], [
                'typeMap' => [
                    'array' => 'array',
                    'document' => 'array',
                    'root' => 'array',
                ],
            ]);
            $db = $client->selectDatabase('medibook');
            $db->command(['ping' => 1]);
        } catch (\Throwable $e) {
            jsonError(500, 'Database connection failed: ' . $e->getMessage());
        }
    }
    return $db;
}

function getCollection(string $name): Collection
{
    return getMongoDB()->selectCollection($name);
}

function jsonError(int $status, string $message): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(['error' => $message]);
    exit;
}
