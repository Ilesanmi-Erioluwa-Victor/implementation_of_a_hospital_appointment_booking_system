<?php

declare(strict_types=1);

namespace MediBook\Services;

use MediBook\Models\Appointment;
use MediBook\Models\ReminderLog;
use MediBook\Models\Doctor;
use MediBook\Models\Patient;

class ReminderService
{
    public static function processReminders(): array
    {
        $results = ['sent' => 0, 'skipped' => 0, 'errors' => 0];

        $now = new \DateTime();
        $tomorrow = (clone $now)->modify('+1 day')->format('Y-m-d');
        $inTwoHours = (clone $now)->modify('+2 hours');

        $upcoming = Appointment::findAll([
            'status' => 'confirmed',
            'appointmentDate' => [
                '$gte' => $now->format('Y-m-d'),
            ],
        ]);

        foreach ($upcoming as $appointment) {
            $apptDate = $appointment['appointmentDate'];
            $timeSlot = $appointment['timeSlot'];
            $slotStart = explode('-', $timeSlot)[0];
            $apptDateTime = \DateTime::createFromFormat('Y-m-d H:i', "$apptDate $slotStart");

            if (!$apptDateTime) continue;

            $diff24 = $apptDateTime->diff($now);
            $hoursUntil = ($diff24->days * 24) + $diff24->h + ($diff24->i / 60);

            $doctor = Doctor::findById($appointment['doctorId']);
            $patient = Patient::findById($appointment['patientId']);
            if (!$doctor || !$patient) continue;

            if ($hoursUntil <= 24 && $hoursUntil > 23) {
                if (!ReminderLog::hasBeenSent((string) $appointment['_id'], '24h_before')) {
                    $sent = EmailService::sendAppointmentReminder(
                        $patient['email'],
                        $patient['firstName'],
                        "Dr. {$doctor['firstName']} {$doctor['lastName']}",
                        $apptDate,
                        $timeSlot
                    );
                    $status = $sent ? 'sent' : 'failed';
                    ReminderLog::create([
                        'appointmentId' => (string) $appointment['_id'],
                        'reminderType' => '24h_before',
                        'status' => $status,
                    ]);
                    if ($sent) $results['sent']++;
                    else $results['errors']++;
                } else {
                    $results['skipped']++;
                }
            }

            if ($hoursUntil <= 2 && $hoursUntil > 1) {
                if (!ReminderLog::hasBeenSent((string) $appointment['_id'], '2h_before')) {
                    $sent = EmailService::sendAppointmentReminder(
                        $patient['email'],
                        $patient['firstName'],
                        "Dr. {$doctor['firstName']} {$doctor['lastName']}",
                        $apptDate,
                        $timeSlot
                    );
                    $status = $sent ? 'sent' : 'failed';
                    ReminderLog::create([
                        'appointmentId' => (string) $appointment['_id'],
                        'reminderType' => '2h_before',
                        'status' => $status,
                    ]);
                    if ($sent) $results['sent']++;
                    else $results['errors']++;
                } else {
                    $results['skipped']++;
                }
            }
        }

        return $results;
    }
}
