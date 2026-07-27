<?php

declare(strict_types=1);

function checkRateLimit(string $key, int $maxRequests = 5, int $windowSeconds = 60): void
{
    $collection = getCollection('rateLimits');
    $windowStart = new \MongoDB\BSON\UTCDateTime((time() - $windowSeconds) * 1000);

    $count = $collection->countDocuments([
        'key' => $key,
        'createdAt' => ['$gte' => $windowStart],
    ]);

    if ($count >= $maxRequests) {
        http_response_code(429);
        echo json_encode([
            'error' => 'Too many requests. Please try again later.',
            'retryAfter' => $windowSeconds,
        ]);
        exit;
    }

    $collection->insertOne([
        'key' => $key,
        'createdAt' => new \MongoDB\BSON\UTCDateTime(),
    ]);
}
