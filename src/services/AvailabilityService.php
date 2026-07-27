<?php

declare(strict_types=1);

namespace MediBook\Services;

use MediBook\Models\Doctor;
use MediBook\Models\Appointment;
use MediBook\Models\ScheduleException;

class AvailabilityService
{
    public static function getBookableSlots(string $doctorId, string $date): array
    {
        $doctor = Doctor::findById($doctorId);
        if (!$doctor || !$doctor['isActive']) {
            return [];
        }

        $dateTime = new \DateTime($date);
        $today = new \DateTime('today');
        if ($dateTime < $today) {
            return [];
        }

        $dayOfWeek = (int) $dateTime->format('w');

        $exception = ScheduleException::findByDoctorAndDate($doctorId, $date);
        if ($exception && $exception['type'] === 'unavailable') {
            return [];
        }

        $workingHours = null;
        if ($exception && $exception['type'] === 'extra_availability') {
            $workingHours = [
                'dayOfWeek' => $dayOfWeek,
                'startTime' => $exception['overrideStartTime'],
                'endTime' => $exception['overrideEndTime'],
            ];
        } else {
            foreach ($doctor['workingHours'] as $wh) {
                if ($wh['dayOfWeek'] === $dayOfWeek) {
                    $workingHours = $wh;
                    break;
                }
            }
        }

        if (!$workingHours) {
            return [];
        }

        $slotDuration = $doctor['slotDurationMinutes'] ?? 30;
        $slots = self::generateTimeSlots($workingHours['startTime'], $workingHours['endTime'], $slotDuration);

        $existingAppointments = Appointment::findAll([
            'doctorId' => $doctorId,
            'appointmentDate' => $date,
            'status' => ['$nin' => ['cancelled', 'no_show']],
        ]);
        $bookedSlots = array_map(fn($a) => $a['timeSlot'], $existingAppointments);

        $slots = array_values(array_filter($slots, fn($slot) => !in_array($slot, $bookedSlots)));

        if ($date === $today->format('Y-m-d')) {
            $now = new \DateTime();
            $slots = array_values(array_filter($slots, function ($slot) use ($now) {
                $slotStart = explode('-', $slot)[0];
                $slotTime = \DateTime::createFromFormat('H:i', $slotStart);
                return $slotTime > $now;
            }));
        }

        return $slots;
    }

    private static function generateTimeSlots(string $startTime, string $endTime, int $durationMinutes): array
    {
        $slots = [];
        $start = \DateTime::createFromFormat('H:i', $startTime);
        $end = \DateTime::createFromFormat('H:i', $endTime);

        if (!$start || !$end) return [];

        while ($start < $end) {
            $slotEnd = clone $start;
            $slotEnd->modify("+{$durationMinutes} minutes");
            if ($slotEnd > $end) break;

            $slots[] = $start->format('H:i') . '-' . $slotEnd->format('H:i');
            $start = $slotEnd;
        }

        return $slots;
    }

    public static function validateSlot(string $doctorId, string $date, string $timeSlot): ?string
    {
        $doctor = Doctor::findById($doctorId);
        if (!$doctor) {
            return 'Doctor not found';
        }

        if (!$doctor['isActive']) {
            return 'Doctor is not currently accepting appointments';
        }

        $dateTime = new \DateTime($date);
        $today = new \DateTime('today');
        if ($dateTime < $today) {
            return 'Cannot book appointments in the past';
        }

        $dayOfWeek = (int) $dateTime->format('w');

        $exception = ScheduleException::findByDoctorAndDate($doctorId, $date);
        if ($exception && $exception['type'] === 'unavailable') {
            return 'Doctor is not available on this date';
        }

        $workingHours = null;
        if ($exception && $exception['type'] === 'extra_availability') {
            $workingHours = [
                'dayOfWeek' => $dayOfWeek,
                'startTime' => $exception['overrideStartTime'],
                'endTime' => $exception['overrideEndTime'],
            ];
        } else {
            foreach ($doctor['workingHours'] as $wh) {
                if ($wh['dayOfWeek'] === $dayOfWeek) {
                    $workingHours = $wh;
                    break;
                }
            }
        }

        if (!$workingHours) {
            return 'Doctor does not work on this day';
        }

        $slotDuration = $doctor['slotDurationMinutes'] ?? 30;
        $allSlots = self::generateTimeSlots($workingHours['startTime'], $workingHours['endTime'], $slotDuration);
        if (!in_array($timeSlot, $allSlots)) {
            return 'Invalid time slot';
        }

        if ($date === $today->format('Y-m-d')) {
            $now = new \DateTime();
            $slotStart = explode('-', $timeSlot)[0];
            $slotTime = \DateTime::createFromFormat('H:i', $slotStart);
            if ($slotTime <= $now) {
                return 'This time slot has already passed';
            }
        }

        $conflicting = Appointment::findConflicting($doctorId, $date, $timeSlot);
        if ($conflicting) {
            return 'This slot was just taken by another patient. Please choose another.';
        }

        return null;
    }
}
