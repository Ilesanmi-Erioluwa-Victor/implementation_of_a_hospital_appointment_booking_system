<?php

declare(strict_types=1);

namespace MediBook\Controllers;

use MediBook\Models\Patient;
use MediBook\Models\StaffUser;

class AuthController
{
    public static function handlePatientRegister(): void
    {
        checkRateLimit('register:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 5, 60);

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid input']);
            return;
        }

        $required = ['firstName', 'lastName', 'email', 'phone', 'password', 'gender'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field '$field' is required"]);
                return;
            }
        }

        if (strlen($input['password']) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 8 characters']);
            return;
        }

        if (Patient::findByEmail($input['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already registered']);
            return;
        }

        if (Patient::findByPhone($input['phone'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Phone number already registered']);
            return;
        }

        $patientId = Patient::create($input);

        $patient = Patient::findById($patientId);

        $token = generateToken([
            'id' => $patientId,
            'email' => $input['email'],
            'type' => 'patient',
        ]);

        $verifyToken = bin2hex(random_bytes(32));
        Patient::update($patientId, ['emailVerificationToken' => $verifyToken]);

        \MediBook\Services\EmailService::sendEmailVerification(
            $input['email'],
            $input['firstName'],
            $verifyToken
        );

        http_response_code(201);
        echo json_encode([
            'message' => 'Registration successful. Please check your email to verify your account.',
            'token' => $token,
            'patient' => [
                'id' => $patientId,
                'firstName' => $input['firstName'],
                'lastName' => $input['lastName'],
                'email' => $input['email'],
                'isEmailVerified' => false,
            ],
        ]);
    }

    public static function handlePatientLogin(): void
    {
        checkRateLimit('login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 60);

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $patient = Patient::findByEmail($input['email']);
        if (!$patient || !password_verify($input['password'], $patient['passwordHash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
            return;
        }

        if (isset($patient['deletedAt'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Account has been closed. Please contact support.']);
            return;
        }

        $token = generateToken([
            'id' => (string) $patient['_id'],
            'email' => $patient['email'],
            'type' => 'patient',
        ]);

        echo json_encode([
            'message' => 'Login successful',
            'token' => $token,
            'patient' => [
                'id' => (string) $patient['_id'],
                'firstName' => $patient['firstName'],
                'lastName' => $patient['lastName'],
                'email' => $patient['email'],
                'isEmailVerified' => $patient['isEmailVerified'] ?? false,
            ],
        ]);
    }

    public static function handleStaffLogin(): void
    {
        checkRateLimit('login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 10, 60);

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['email']) || empty($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Email and password are required']);
            return;
        }

        $staff = StaffUser::findByEmail($input['email']);
        if (!$staff || !password_verify($input['password'], $staff['passwordHash'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
            return;
        }

        $token = generateToken([
            'id' => (string) $staff['_id'],
            'email' => $staff['email'],
            'type' => 'staff',
            'role' => $staff['role'],
        ]);

        echo json_encode([
            'message' => 'Login successful',
            'token' => $token,
            'staff' => [
                'id' => (string) $staff['_id'],
                'name' => $staff['name'],
                'email' => $staff['email'],
                'role' => $staff['role'],
            ],
        ]);
    }

    public static function handleVerifyEmail(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';

        if (!$token) {
            http_response_code(400);
            echo json_encode(['error' => 'Verification token is required']);
            return;
        }

        $collection = getCollection('patients');
        $patient = $collection->findOne(['emailVerificationToken' => $token]);

        if (!$patient) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or expired verification token']);
            return;
        }

        Patient::update((string) $patient['_id'], [
            'isEmailVerified' => true,
            'emailVerifiedAt' => new \MongoDB\BSON\UTCDateTime(),
            'emailVerificationToken' => null,
        ]);

        echo json_encode(['message' => 'Email verified successfully']);
    }

    public static function handleForgotPassword(): void
    {
        checkRateLimit('forgot-password:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 3, 300);

        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';

        if (!$email) {
            http_response_code(400);
            echo json_encode(['error' => 'Email is required']);
            return;
        }

        $patient = Patient::findByEmail($email);
        if (!$patient) {
            echo json_encode(['message' => 'If the email exists, a reset link has been sent']);
            return;
        }

        $resetToken = bin2hex(random_bytes(32));
        Patient::update((string) $patient['_id'], [
            'passwordResetToken' => $resetToken,
            'passwordResetExpiresAt' => date('Y-m-d H:i:s', time() + 3600),
        ]);

        \MediBook\Services\EmailService::sendPasswordReset(
            $email,
            $patient['firstName'],
            $resetToken
        );

        echo json_encode(['message' => 'If the email exists, a reset link has been sent']);
    }

    public static function handleResetPassword(): void
    {
        checkRateLimit('reset-password:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 3, 300);

        $input = json_decode(file_get_contents('php://input'), true);
        $token = $input['token'] ?? '';
        $password = $input['password'] ?? '';

        if (!$token || !$password) {
            http_response_code(400);
            echo json_encode(['error' => 'Token and password are required']);
            return;
        }

        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Password must be at least 8 characters']);
            return;
        }

        $collection = getCollection('patients');
        $patient = $collection->findOne([
            'passwordResetToken' => $token,
            'passwordResetExpiresAt' => ['$gte' => date('Y-m-d H:i:s')],
        ]);

        if (!$patient) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or expired reset token']);
            return;
        }

        Patient::update((string) $patient['_id'], [
            'passwordHash' => password_hash($password, PASSWORD_BCRYPT),
            'passwordResetToken' => null,
            'passwordResetExpiresAt' => null,
        ]);

        echo json_encode(['message' => 'Password reset successful']);
    }

    public static function handleLogout(): void
    {
        echo json_encode(['message' => 'Logged out successfully']);
    }
}
