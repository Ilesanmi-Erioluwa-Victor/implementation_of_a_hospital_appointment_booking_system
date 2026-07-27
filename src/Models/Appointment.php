<?php

declare(strict_types=1);

namespace MediBook\Models;

class Appointment
{
    public static function create(array $data): string
    {
        $collection = getCollection('appointments');
        $data['status'] = $data['status'] ?? 'pending';
        $data['createdAt'] = new \MongoDB\BSON\UTCDateTime();
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $result = $collection->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public static function findById(string $id): ?array
    {
        $collection = getCollection('appointments');
        return $collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }

    public static function findByPatient(string $patientId, array $filter = []): array
    {
        $collection = getCollection('appointments');
        $filter['patientId'] = $patientId;
        $cursor = $collection->find(
            $filter,
            ['sort' => ['appointmentDate' => -1]]
        );
        return $cursor->toArray();
    }

    public static function findAll(array $filter = [], array $options = []): array
    {
        $collection = getCollection('appointments');
        $options['sort'] = $options['sort'] ?? ['appointmentDate' => -1];
        $cursor = $collection->find($filter, $options);
        return $cursor->toArray();
    }

    public static function findConflicting(string $doctorId, string $date, string $timeSlot): ?array
    {
        $collection = getCollection('appointments');
        return $collection->findOne([
            'doctorId' => $doctorId,
            'appointmentDate' => $date,
            'timeSlot' => $timeSlot,
            'status' => ['$nin' => ['cancelled', 'no_show']],
        ]);
    }

    public static function update(string $id, array $data): void
    {
        $collection = getCollection('appointments');
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => $data]
        );
    }

    public static function updateMany(array $filter, array $data): int
    {
        $collection = getCollection('appointments');
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $result = $collection->updateMany($filter, ['$set' => $data]);
        return $result->getModifiedCount();
    }

    public static function count(array $filter): int
    {
        $collection = getCollection('appointments');
        return $collection->countDocuments($filter);
    }

    public static function aggregate(array $pipeline): array
    {
        $collection = getCollection('appointments');
        return $collection->aggregate($pipeline)->toArray();
    }
}
