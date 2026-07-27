<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

if (php_sapi_name() === 'cli' || empty($_GET['secret']) || $_GET['secret'] !== CRON_SECRET) {
    if (php_sapi_name() !== 'cli') {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid cron secret']);
        exit;
    }
}

$result = MediBook\Services\ReminderService::processReminders();

if (php_sapi_name() === 'cli') {
    echo "Reminders processed:\n";
    echo "  Sent: {$result['sent']}\n";
    echo "  Skipped (already sent): {$result['skipped']}\n";
    echo "  Errors: {$result['errors']}\n";
} else {
    echo json_encode($result);
}
