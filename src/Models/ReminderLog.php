<?php

declare(strict_types=1);

namespace MediBook\Models;

class ReminderLog
{
    public static function create(array $data): string
    {
        $collection = getCollection('reminderLogs');
        $data['sentAt'] = new \MongoDB\BSON\UTCDateTime();
        $result = $collection->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public static function hasBeenSent(string $appointmentId, string $reminderType): bool
    {
        $collection = getCollection('reminderLogs');
        $count = $collection->countDocuments([
            'appointmentId' => $appointmentId,
            'reminderType' => $reminderType,
            'status' => 'sent',
        ]);
        return $count > 0;
    }
}
