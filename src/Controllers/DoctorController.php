<?php

declare(strict_types=1);

namespace MediBook\Controllers;

use MediBook\Models\Doctor;
use MediBook\Models\ScheduleException;
use MediBook\Services\AvailabilityService;

class DoctorController
{
    public static function index(): void
    {
        $departmentId = $_GET['department'] ?? null;
        $filter = $departmentId ? ['departmentId' => $departmentId] : [];
        $doctors = Doctor::findActive($filter);
        $result = array_map(function ($d) {
            $d['_id'] = (string) $d['_id'];
            return $d;
        }, $doctors);
        echo json_encode($result);
    }

    public static function store(): void
    {
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $required = ['firstName', 'lastName', 'email', 'departmentId'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field '$field' is required"]);
                return;
            }
        }
        $id = Doctor::create($input);
        http_response_code(201);
        echo json_encode(['message' => 'Doctor added', 'id' => $id]);
    }

    public static function show(string $id): void
    {
        $doctor = Doctor::findById($id);
        if (!$doctor) {
            http_response_code(404);
            echo json_encode(['error' => 'Doctor not found']);
            return;
        }
        $doctor['_id'] = (string) $doctor['_id'];
        echo json_encode($doctor);
    }

    public static function update(string $id): void
    {
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        Doctor::update($id, $input);
        echo json_encode(['message' => 'Doctor updated']);
    }

    public static function getAvailability(string $id): void
    {
        $date = $_GET['date'] ?? '';
        if (!$date) {
            http_response_code(400);
            echo json_encode(['error' => 'Date parameter is required']);
            return;
        }
        $slots = AvailabilityService::getBookableSlots($id, $date);
        echo json_encode(['date' => $date, 'slots' => $slots]);
    }

    public static function addScheduleException(string $id): void
    {
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        $input['doctorId'] = $id;
        if (empty($input['date']) || empty($input['type'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Date and type are required']);
            return;
        }
        $exceptionId = ScheduleException::create($input);
        http_response_code(201);
        echo json_encode(['message' => 'Schedule exception created', 'id' => $exceptionId]);
    }

    public static function listAll(): void
    {
        requireAdmin();
        $doctors = Doctor::findAll();
        $result = array_map(function ($d) {
            $d['_id'] = (string) $d['_id'];
            return $d;
        }, $doctors);
        echo json_encode($result);
    }
}
