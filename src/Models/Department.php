<?php

declare(strict_types=1);

namespace MediBook\Models;

class Department
{
    public static function create(array $data): string
    {
        $collection = getCollection('departments');
        $data['createdAt'] = new \MongoDB\BSON\UTCDateTime();
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $result = $collection->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public static function findAll(): array
    {
        $collection = getCollection('departments');
        return $collection->find()->toArray();
    }

    public static function findById(string $id): ?array
    {
        $collection = getCollection('departments');
        return $collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }

    public static function update(string $id, array $data): void
    {
        $collection = getCollection('departments');
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => $data]
        );
    }

    public static function delete(string $id): bool
    {
        $collection = getCollection('departments');
        $result = $collection->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        return $result->getDeletedCount() > 0;
    }
}
