<?php

declare(strict_types=1);

namespace MediBook\Models;

class ScheduleException
{
    public static function create(array $data): string
    {
        $collection = getCollection('scheduleExceptions');
        $data['createdAt'] = new \MongoDB\BSON\UTCDateTime();
        $result = $collection->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public static function findByDoctorAndDate(string $doctorId, string $date): ?array
    {
        $collection = getCollection('scheduleExceptions');
        return $collection->findOne([
            'doctorId' => $doctorId,
            'date' => $date,
        ]);
    }

    public static function findAllForDoctor(string $doctorId): array
    {
        $collection = getCollection('scheduleExceptions');
        return $collection->find(
            ['doctorId' => $doctorId],
            ['sort' => ['date' => 1]]
        )->toArray();
    }

    public static function delete(string $id): bool
    {
        $collection = getCollection('scheduleExceptions');
        $result = $collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        return $result->getDeletedCount() > 0;
    }
}
