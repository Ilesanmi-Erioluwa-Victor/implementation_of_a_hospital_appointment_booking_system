<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/env.php';
require_once __DIR__ . '/../src/config/db.php';
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../src/middleware/csrf.php';
require_once __DIR__ . '/../src/middleware/rateLimit.php';
require_once __DIR__ . '/../src/middleware/rbac.php';

ini_set('display_errors', '0');
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

$uri = rtrim($uri, '/');
if ($uri === '') $uri = '/';

// ========== API ROUTES ==========

// Auth routes
if (preg_match('#^/api/auth/patient/register$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AuthController::handlePatientRegister();
} elseif (preg_match('#^/api/auth/patient/login$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AuthController::handlePatientLogin();
} elseif (preg_match('#^/api/auth/staff/login$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AuthController::handleStaffLogin();
} elseif (preg_match('#^/api/auth/patient/verify-email$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AuthController::handleVerifyEmail();
} elseif (preg_match('#^/api/auth/forgot-password$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AuthController::handleForgotPassword();
} elseif (preg_match('#^/api/auth/reset-password$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AuthController::handleResetPassword();
} elseif (preg_match('#^/api/auth/logout$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AuthController::handleLogout();

// Department routes
} elseif (preg_match('#^/api/departments$#', $uri) && $method === 'GET') {
    MediBook\Controllers\DepartmentController::index();
} elseif (preg_match('#^/api/departments$#', $uri) && $method === 'POST') {
    MediBook\Controllers\DepartmentController::store();

// Doctor routes
} elseif (preg_match('#^/api/doctors$#', $uri) && $method === 'GET') {
    MediBook\Controllers\DoctorController::index();
} elseif (preg_match('#^/api/doctors/([a-f0-9]+)$#', $uri, $m) && $method === 'PATCH') {
    MediBook\Controllers\DoctorController::update($m[1]);
} elseif (preg_match('#^/api/doctors/([a-f0-9]+)/availability$#', $uri, $m) && $method === 'GET') {
    MediBook\Controllers\DoctorController::getAvailability($m[1]);

// Doctor exception routes
} elseif (preg_match('#^/api/doctors/([a-f0-9]+)/schedule-exceptions$#', $uri, $m) && $method === 'POST') {
    MediBook\Controllers\DoctorController::addScheduleException($m[1]);

// Department DELETE
} elseif (preg_match('#^/api/departments/([a-f0-9]+)$#', $uri, $m) && $method === 'DELETE') {
    MediBook\Controllers\DepartmentController::destroy($m[1]);

// Appointment routes (patient)
} elseif (preg_match('#^/api/appointments/mine$#', $uri) && $method === 'GET') {
    MediBook\Controllers\AppointmentController::myAppointments();
} elseif (preg_match('#^/api/appointments$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AppointmentController::book();
} elseif (preg_match('#^/api/appointments/([a-f0-9]+)/cancel$#', $uri, $m) && $method === 'PATCH') {
    MediBook\Controllers\AppointmentController::cancel($m[1]);
} elseif (preg_match('#^/api/appointments/([a-f0-9]+)/reschedule$#', $uri, $m) && $method === 'PATCH') {
    MediBook\Controllers\AppointmentController::reschedule($m[1]);

// Staff appointment routes
} elseif (preg_match('#^/api/admin/appointments$#', $uri) && $method === 'GET') {
    MediBook\Controllers\AppointmentController::adminList();
} elseif (preg_match('#^/api/admin/appointments$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AppointmentController::walkInBook();
} elseif (preg_match('#^/api/admin/appointments/([a-f0-9]+)/complete$#', $uri, $m) && $method === 'PATCH') {
    MediBook\Controllers\AppointmentController::complete($m[1]);
} elseif (preg_match('#^/api/admin/appointments/([a-f0-9]+)/no-show$#', $uri, $m) && $method === 'PATCH') {
    MediBook\Controllers\AppointmentController::markNoShow($m[1]);
} elseif (preg_match('#^/api/admin/appointments/([a-f0-9]+)/cancel$#', $uri, $m) && $method === 'PATCH') {
    MediBook\Controllers\AppointmentController::staffCancel($m[1]);
} elseif (preg_match('#^/api/admin/appointments/bulk-cancel$#', $uri) && $method === 'POST') {
    MediBook\Controllers\AppointmentController::bulkCancel();

// Report routes
} elseif (preg_match('#^/api/admin/reports/appointments-summary$#', $uri) && $method === 'GET') {
    requireAdmin();
    $start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
    $end = $_GET['end'] ?? date('Y-m-d');
    echo json_encode(MediBook\Services\ReportService::appointmentsSummary($start, $end));
} elseif (preg_match('#^/api/admin/reports/no-show-rate$#', $uri) && $method === 'GET') {
    requireAdmin();
    $start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
    $end = $_GET['end'] ?? date('Y-m-d');
    echo json_encode(MediBook\Services\ReportService::noShowRate($start, $end));
} elseif (preg_match('#^/api/admin/reports/export\.csv$#', $uri) && $method === 'GET') {
    requireStaff();
    $start = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
    $end = $_GET['end'] ?? date('Y-m-d');
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="appointments-report.csv"');
    echo MediBook\Services\ReportService::exportCsv($start, $end);
} elseif (preg_match('#^/api/admin/doctors$#', $uri) && $method === 'GET') {
    MediBook\Controllers\DoctorController::listAll();
} elseif (preg_match('#^/api/admin/doctors$#', $uri) && $method === 'POST') {
    MediBook\Controllers\DoctorController::store();

// ========== FRONTEND VIEWS ==========
} elseif ($uri === '/' || $uri === '/index' || $uri === '/home') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/home.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/login') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/login.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/register') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/register.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/doctors') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/doctors.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/book') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/book.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/my-appointments') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/my_appointments.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/admin') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/admin/dashboard.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/admin/appointments') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/admin/appointments.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/admin/doctors') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/admin/doctors.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/admin/departments') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/admin/departments.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/admin/reports') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/admin/reports.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/verify-email') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/verify_email.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/reset-password') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/reset_password.php';
    include __DIR__ . '/../src/views/footer.php';
} elseif ($uri === '/cancel-appointment') {
    header('Content-Type: text/html; charset=utf-8');
    include __DIR__ . '/../src/views/layout.php';
    include __DIR__ . '/../src/views/cancel_appointment.php';
    include __DIR__ . '/../src/views/footer.php';

// ========== CRON ==========
} elseif (preg_match('#^/cron/send-reminders$#', $uri)) {
    if (empty($_GET['secret']) || $_GET['secret'] !== CRON_SECRET) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid cron secret']);
        exit;
    }
    $result = MediBook\Services\ReminderService::processReminders();
    echo json_encode($result);

// Static assets
} elseif (preg_match('#\.(css|js|png|jpg|jpeg|gif|ico|svg)$#', $uri)) {
    $filePath = __DIR__ . $uri;
    if (file_exists($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'svg' => 'image/svg+xml',
        ];
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
        readfile($filePath);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'File not found']);
    }
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found', 'uri' => $uri]);
}
