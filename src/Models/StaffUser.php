<?php

declare(strict_types=1);

namespace MediBook\Models;

class StaffUser
{
    public static function create(array $data): string
    {
        $collection = getCollection('staffUsers');
        $data['passwordHash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['createdAt'] = new \MongoDB\BSON\UTCDateTime();
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        unset($data['password']);

        $result = $collection->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public static function findByEmail(string $email): ?array
    {
        $collection = getCollection('staffUsers');
        return $collection->findOne(['email' => $email]);
    }

    public static function findById(string $id): ?array
    {
        $collection = getCollection('staffUsers');
        return $collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }

    public static function update(string $id, array $data): void
    {
        $collection = getCollection('staffUsers');
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => $data]
        );
    }
}
