<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';

use MongoDB\Client;
use MongoDB\Database;

function getMongoDB(): Database
{
    static $db = null;
    if ($db === null) {
        $client = new Client(MONGODB_URI, [], [
            'typeMap' => [
                'array' => 'array',
                'document' => 'array',
                'root' => 'array',
            ],
        ]);
        $db = $client->selectDatabase('medibook');
    }
    return $db;
}

function getCollection(string $name): MongoDB\Collection
{
    return getMongoDB()->selectCollection($name);
}
