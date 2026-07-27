<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$root = dirname(__DIR__, 2);
$dotenv = Dotenv::createImmutable($root);
$dotenv->safeLoad();

function env(string $key, string $default = ''): string {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

define('MONGODB_URI', env('MONGODB_URI'));
define('JWT_SECRET', env('JWT_SECRET'));
define('BREVO_API_KEY', env('BREVO_API_KEY'));
define('CRON_SECRET', env('CRON_SECRET'));
define('APP_URL', env('APP_URL', 'http://localhost:8000'));
define('APP_ENV', env('APP_ENV', 'development'));
