<?php

declare(strict_types=1);

use Dotenv\Dotenv;

$root = dirname(__DIR__, 2);
$dotenv = Dotenv::createImmutable($root);
$dotenv->safeLoad();

define('MONGODB_URI', $_ENV['MONGODB_URI'] ?? '');
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? '');
define('BREVO_API_KEY', $_ENV['BREVO_API_KEY'] ?? '');
define('CRON_SECRET', $_ENV['CRON_SECRET'] ?? '');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost:8000');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
