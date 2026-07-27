<?php

declare(strict_types=1);

namespace MediBook\Controllers;

use MediBook\Models\Department;
use MediBook\Models\Doctor;

class DepartmentController
{
    public static function index(): void
    {
        $departments = Department::findAll();
        $result = array_map(function ($d) {
            $d['_id'] = (string) $d['_id'];
            return $d;
        }, $departments);
        echo json_encode($result);
    }

    public static function store(): void
    {
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input['name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name is required']);
            return;
        }
        $id = Department::create($input);
        http_response_code(201);
        echo json_encode(['message' => 'Department created', 'id' => $id]);
    }

    public static function update(string $id): void
    {
        requireAdmin();
        $input = json_decode(file_get_contents('php://input'), true);
        Department::update($id, $input);
        echo json_encode(['message' => 'Department updated']);
    }

    public static function destroy(string $id): void
    {
        requireAdmin();
        $doctors = Doctor::findAll(['departmentId' => $id]);
        if (!empty($doctors)) {
            http_response_code(409);
            echo json_encode(['error' => 'Cannot delete department with assigned doctors. Reassign doctors first.']);
            return;
        }
        Department::delete($id);
        echo json_encode(['message' => 'Department deleted']);
    }
}
