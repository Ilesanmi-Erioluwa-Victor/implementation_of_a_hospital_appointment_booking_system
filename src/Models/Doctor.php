<?php

declare(strict_types=1);

namespace MediBook\Models;

class Doctor
{
    public static function create(array $data): string
    {
        $collection = getCollection('doctors');
        $data['isActive'] = $data['isActive'] ?? true;
        $data['createdAt'] = new \MongoDB\BSON\UTCDateTime();
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $result = $collection->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public static function findAll(array $filter = []): array
    {
        $collection = getCollection('doctors');
        $cursor = $collection->find($filter, ['sort' => ['lastName' => 1]]);
        return $cursor->toArray();
    }

    public static function findActive(array $filter = []): array
    {
        $collection = getCollection('doctors');
        $filter['isActive'] = true;
        $cursor = $collection->find($filter, ['sort' => ['lastName' => 1]]);
        return $cursor->toArray();
    }

    public static function findById(string $id): ?array
    {
        $collection = getCollection('doctors');
        return $collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }

    public static function update(string $id, array $data): void
    {
        $collection = getCollection('doctors');
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => $data]
        );
    }
}
