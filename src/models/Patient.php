<?php

declare(strict_types=1);

namespace MediBook\Models;

use MediBook\Database;

class Patient
{
    public static function create(array $data): string
    {
        $collection = getCollection('patients');
        $data['passwordHash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['isEmailVerified'] = false;
        $data['createdAt'] = new \MongoDB\BSON\UTCDateTime();
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        unset($data['password']);

        $result = $collection->insertOne($data);
        return (string) $result->getInsertedId();
    }

    public static function findByEmail(string $email): ?array
    {
        $collection = getCollection('patients');
        return $collection->findOne(['email' => $email]);
    }

    public static function findByPhone(string $phone): ?array
    {
        $collection = getCollection('patients');
        return $collection->findOne(['phone' => $phone]);
    }

    public static function findById(string $id): ?array
    {
        $collection = getCollection('patients');
        return $collection->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
    }

    public static function update(string $id, array $data): void
    {
        $collection = getCollection('patients');
        $data['updatedAt'] = new \MongoDB\BSON\UTCDateTime();
        $collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => $data]
        );
    }

    public static function softDelete(string $id): void
    {
        $collection = getCollection('patients');
        $collection->updateOne(
            ['_id' => new \MongoDB\BSON\ObjectId($id)],
            ['$set' => [
                'deletedAt' => new \MongoDB\BSON\UTCDateTime(),
                'updatedAt' => new \MongoDB\BSON\UTCDateTime(),
            ]]
        );
    }

    public static function search(string $term): array
    {
        $collection = getCollection('patients');
        $regex = new \MongoDB\BSON\Regex(preg_quote($term), 'i');
        $cursor = $collection->find([
            '$or' => [
                ['firstName' => $regex],
                ['lastName' => $regex],
                ['phone' => $regex],
                ['email' => $regex],
            ],
            'deletedAt' => null,
        ]);
        return $cursor->toArray();
    }
}
