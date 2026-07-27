<?php

declare(strict_types=1);

namespace MediBook\Services;

use MediBook\Models\Appointment;

class ReportService
{
    public static function appointmentsSummary(string $startDate, string $endDate): array
    {
        $pipeline = [
            ['$match' => [
                'appointmentDate' => ['$gte' => $startDate, '$lte' => $endDate],
            ]],
            ['$group' => [
                '_id' => ['doctorId' => '$doctorId', 'departmentId' => '$departmentId'],
                'total' => ['$sum' => 1],
                'confirmed' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'confirmed']], 1, 0]]],
                'completed' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'completed']], 1, 0]]],
                'cancelled' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'cancelled']], 1, 0]]],
                'no_show' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'no_show']], 1, 0]]],
                'pending' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'pending']], 1, 0]]],
            ]],
        ];

        return Appointment::aggregate($pipeline);
    }

    public static function noShowRate(string $startDate, string $endDate): array
    {
        $pipeline = [
            ['$match' => [
                'appointmentDate' => ['$gte' => $startDate, '$lte' => $endDate],
                'status' => ['$in' => ['completed', 'no_show']],
            ]],
            ['$group' => [
                '_id' => '$doctorId',
                'total' => ['$sum' => 1],
                'noShows' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'no_show']], 1, 0]]],
            ]],
            ['$addFields' => [
                'noShowRate' => [
                    '$cond' => [
                        ['$gt' => ['$total', 0]],
                        ['$multiply' => [['$divide' => ['$noShows', '$total']], 100]],
                        0,
                    ],
                ],
            ]],
            ['$sort' => ['noShowRate' => -1]],
        ];

        return Appointment::aggregate($pipeline);
    }

    public static function busiestSlots(string $startDate, string $endDate): array
    {
        $pipeline = [
            ['$match' => [
                'appointmentDate' => ['$gte' => $startDate, '$lte' => $endDate],
            ]],
            ['$group' => [
                '_id' => ['timeSlot' => '$timeSlot', 'appointmentDate' => '$appointmentDate'],
                'count' => ['$sum' => 1],
            ]],
            ['$sort' => ['count' => -1]],
            ['$limit' => 20],
        ];

        return Appointment::aggregate($pipeline);
    }

    public static function cancellationReasons(string $startDate, string $endDate): array
    {
        $pipeline = [
            ['$match' => [
                'appointmentDate' => ['$gte' => $startDate, '$lte' => $endDate],
                'status' => 'cancelled',
                'cancelReason' => ['$exists' => true, '$ne' => null],
            ]],
            ['$group' => [
                '_id' => '$cancelReason',
                'count' => ['$sum' => 1],
            ]],
            ['$sort' => ['count' => -1]],
        ];

        return Appointment::aggregate($pipeline);
    }

    public static function exportCsv(string $startDate, string $endDate): string
    {
        $appointments = Appointment::findAll([
            'appointmentDate' => ['$gte' => $startDate, '$lte' => $endDate],
        ]);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['Patient ID', 'Doctor ID', 'Department ID', 'Date', 'Time Slot', 'Status', 'Reason', 'Created At']);

        foreach ($appointments as $a) {
            fputcsv($output, [
                $a['patientId'] ?? '',
                $a['doctorId'] ?? '',
                $a['departmentId'] ?? '',
                $a['appointmentDate'] ?? '',
                $a['timeSlot'] ?? '',
                $a['status'] ?? '',
                $a['reasonForVisit'] ?? '',
                $a['createdAt'] ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
