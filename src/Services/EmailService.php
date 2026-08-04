<?php

declare(strict_types=1);

namespace MediBook\Services;

class EmailService
{
    private static function send(string $to, string $subject, string $htmlContent): bool
    {
        if (empty(BREVO_API_KEY)) {
            error_log('BREVO_API_KEY not configured; skipping email to ' . $to);
            return false;
        }

        $payload = json_encode([
            'sender' => ['name' => 'DESTH Appointment', 'email' => 'noreply@desth.app'],
            'to' => [['email' => $to]],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'api-key: ' . BREVO_API_KEY,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            error_log("Brevo email send failed (HTTP $httpCode): $response");
            return false;
        }

        return true;
    }

    public static function sendEmailVerification(string $email, string $firstName, string $token): bool
    {
        $link = APP_URL . '/verify-email?token=' . urlencode($token);
        $html = "<h2>Welcome to DESTH Appointment, $firstName!</h2>
                 <p>Please verify your email by clicking the link below:</p>
                 <p><a href=\"$link\">Verify Email Address</a></p>
                 <p>If you did not create an account, please ignore this email.</p>";
        return self::send($email, 'Verify your DESTH Appointment account', $html);
    }

    public static function sendPasswordReset(string $email, string $firstName, string $token): bool
    {
        $link = APP_URL . '/reset-password?token=' . urlencode($token);
        $html = "<h2>Password Reset Request</h2>
                 <p>Hi $firstName,</p>
                 <p>Click the link below to reset your password. This link expires in 1 hour.</p>
                 <p><a href=\"$link\">Reset Password</a></p>
                 <p>If you did not request this, please ignore this email.</p>";
        return self::send($email, 'Reset your DESTH Appointment password', $html);
    }

    public static function sendAppointmentConfirmation(
        string $email,
        string $patientName,
        string $doctorName,
        string $department,
        string $date,
        string $timeSlot,
        string $appointmentId
    ): bool {
        $cancelLink = APP_URL . "/cancel-appointment?id=" . urlencode($appointmentId);
        $html = "<h2>Appointment Confirmed</h2>
                 <p>Hi $patientName,</p>
                 <p>Your appointment has been booked successfully.</p>
                 <p><strong>Doctor:</strong> $doctorName</p>
                 <p><strong>Department:</strong> $department</p>
                 <p><strong>Date:</strong> $date</p>
                 <p><strong>Time:</strong> $timeSlot</p>
                 <p>If you need to cancel, please use the link below:</p>
                 <p><a href=\"$cancelLink\">Cancel Appointment</a></p>";
        return self::send($email, 'Appointment Confirmed - DESTH Appointment', $html);
    }

    public static function sendCancellationNotice(string $email, string $patientName, string $doctorName, string $date, string $timeSlot): bool
    {
        $html = "<h2>Appointment Cancelled</h2>
                 <p>Hi $patientName,</p>
                 <p>Your appointment with <strong>$doctorName</strong> on <strong>$date</strong> at <strong>$timeSlot</strong> has been cancelled.</p>
                 <p>Please log in to book a new appointment.</p>";
        return self::send($email, 'Appointment Cancelled - DESTH Appointment', $html);
    }

    public static function sendAppointmentReminder(string $email, string $patientName, string $doctorName, string $date, string $timeSlot): bool
    {
        $html = "<h2>Appointment Reminder</h2>
                 <p>Hi $patientName,</p>
                 <p>This is a reminder of your upcoming appointment.</p>
                 <p><strong>Doctor:</strong> $doctorName</p>
                 <p><strong>Date:</strong> $date</p>
                 <p><strong>Time:</strong> $timeSlot</p>
                 <p>Please arrive on time.</p>";
        return self::send($email, 'Appointment Reminder - DESTH Appointment', $html);
    }

    public static function sendBulkCancellation(string $email, string $patientName, string $doctorName, string $date): bool
    {
        $html = "<h2>Important: Appointment Cancelled</h2>
                 <p>Hi $patientName,</p>
                 <p>Due to unforeseen circumstances, all appointments with <strong>$doctorName</strong> on <strong>$date</strong> have been cancelled.</p>
                 <p>We apologise for the inconvenience. Please log in to book a new appointment.</p>";
        return self::send($email, 'Appointment Cancelled Due to Schedule Change - DESTH Appointment', $html);
    }
}
