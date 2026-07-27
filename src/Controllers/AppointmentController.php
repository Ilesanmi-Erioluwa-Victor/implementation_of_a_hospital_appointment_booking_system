<?php

declare(strict_types=1);

namespace MediBook\Controllers;

use MediBook\Models\Appointment;
use MediBook\Models\Doctor;
use MediBook\Models\Patient;
use MediBook\Models\Department;
use MediBook\Services\AvailabilityService;
use MediBook\Services\EmailService;

class AppointmentController
{
    public static function book(): void
    {
        $patient = requirePatient();
        $input = json_decode(file_get_contents('php://input'), true);

        $required = ['doctorId', 'departmentId', 'appointmentDate', 'timeSlot'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "Field '$field' is required"]);
                return;
            }
        }

        $error = AvailabilityService::validateSlot(
            $input['doctorId'],
            $input['appointmentDate'],
            $input['timeSlot']
        );

        if ($error) {
            http_response_code(409);
            echo json_encode(['error' => $error]);
            return;
        }

        $appointmentId = Appointment::create([
            'patientId' => $patient['id'],
            'doctorId' => $input['doctorId'],
            'departmentId' => $input['departmentId'],
            'appointmentDate' => $input['appointmentDate'],
            'timeSlot' => $input['timeSlot'],
            'reasonForVisit' => $input['reasonForVisit'] ?? '',
            'bookedByType' => 'patient',
            'status' => 'confirmed',
        ]);

        $doctor = Doctor::findById($input['doctorId']);
        $department = Department::findById($input['departmentId']);
        $patientData = Patient::findById($patient['id']);

        if ($doctor && $patientData && $department) {
            EmailService::sendAppointmentConfirmation(
                $patientData['email'],
                $patientData['firstName'],
                "Dr. {$doctor['firstName']} {$doctor['lastName']}",
                $department['name'],
                $input['appointmentDate'],
                $input['timeSlot'],
                $appointmentId
            );
        }

        http_response_code(201);
        echo json_encode([
            'message' => 'Appointment booked successfully',
            'id' => $appointmentId,
        ]);
    }

    public static function myAppointments(): void
    {
        $patient = requirePatient();
        $status = $_GET['status'] ?? null;
        $filter = [];
        if ($status) $filter['status'] = $status;

        $appointments = Appointment::findByPatient($patient['id'], $filter);
        $result = array_map(function ($a) {
            $a['_id'] = (string) $a['_id'];
            return $a;
        }, $appointments);
        echo json_encode($result);
    }

    public static function cancel(string $id): void
    {
        $patient = requirePatient();
        $appointment = Appointment::findById($id);

        if (!$appointment || $appointment['patientId'] !== $patient['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Appointment not found']);
            return;
        }

        if (in_array($appointment['status'], ['completed', 'cancelled', 'no_show'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Cannot cancel an appointment that is already ' . $appointment['status']]);
            return;
        }

        $apptDate = $appointment['appointmentDate'];
        $timeSlot = $appointment['timeSlot'];
        $slotStart = explode('-', $timeSlot)[0];
        $apptDateTime = \DateTime::createFromFormat('Y-m-d H:i', "$apptDate $slotStart");
        $now = new \DateTime();
        $hoursUntil = $now->diff($apptDateTime)->h + ($now->diff($apptDateTime)->days * 24);

        if ($hoursUntil < 2) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Cancellation is not allowed within 2 hours of the appointment time. Please call the hospital directly.',
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        Appointment::update($id, [
            'status' => 'cancelled',
            'cancelledAt' => new \MongoDB\BSON\UTCDateTime(),
            'cancelReason' => $input['reason'] ?? 'Cancelled by patient',
        ]);

        echo json_encode(['message' => 'Appointment cancelled successfully']);
    }

    public static function reschedule(string $id): void
    {
        $patient = requirePatient();
        $appointment = Appointment::findById($id);

        if (!$appointment || $appointment['patientId'] !== $patient['id']) {
            http_response_code(404);
            echo json_encode(['error' => 'Appointment not found']);
            return;
        }

        $apptDate = $appointment['appointmentDate'];
        $timeSlot = $appointment['timeSlot'];
        $slotStart = explode('-', $timeSlot)[0];
        $apptDateTime = \DateTime::createFromFormat('Y-m-d H:i', "$apptDate $slotStart");
        $now = new \DateTime();
        $hoursUntil = $now->diff($apptDateTime)->h + ($now->diff($apptDateTime)->days * 24);

        if ($hoursUntil < 2) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Cannot reschedule within 2 hours of the appointment. Please call the hospital directly.',
            ]);
            return;
        }

        Appointment::update($id, [
            'status' => 'cancelled',
            'cancelledAt' => new \MongoDB\BSON\UTCDateTime(),
            'cancelReason' => 'Rescheduled by patient',
        ]);

        $input = json_decode(file_get_contents('php://input'), true);

        if (!empty($input['appointmentDate']) && !empty($input['timeSlot'])) {
            $error = AvailabilityService::validateSlot(
                $appointment['doctorId'],
                $input['appointmentDate'],
                $input['timeSlot']
            );

            if ($error) {
                http_response_code(409);
                echo json_encode(['error' => $error]);
                return;
            }

            $newId = Appointment::create([
                'patientId' => $patient['id'],
                'doctorId' => $appointment['doctorId'],
                'departmentId' => $appointment['departmentId'],
                'appointmentDate' => $input['appointmentDate'],
                'timeSlot' => $input['timeSlot'],
                'reasonForVisit' => $input['reasonForVisit'] ?? $appointment['reasonForVisit'] ?? '',
                'bookedByType' => 'patient',
                'status' => 'confirmed',
            ]);

            echo json_encode([
                'message' => 'Appointment rescheduled successfully',
                'oldAppointmentId' => $id,
                'newAppointmentId' => $newId,
            ]);
        } else {
            echo json_encode([
                'message' => 'Old appointment cancelled. Please book a new appointment.',
                'cancelledId' => $id,
            ]);
        }
    }

    public static function complete(string $id): void
    {
        requireStaff();
        Appointment::update($id, [
            'status' => 'completed',
            'completedAt' => new \MongoDB\BSON\UTCDateTime(),
        ]);
        echo json_encode(['message' => 'Appointment marked as completed']);
    }

    public static function markNoShow(string $id): void
    {
        requireStaff();
        Appointment::update($id, [
            'status' => 'no_show',
            'completedAt' => new \MongoDB\BSON\UTCDateTime(),
        ]);
        echo json_encode(['message' => 'Appointment marked as no-show']);
    }

    public static function staffCancel(string $id): void
    {
        $staff = requireStaff();
        $appointment = Appointment::findById($id);
        if (!$appointment) {
            http_response_code(404);
            echo json_encode(['error' => 'Appointment not found']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        Appointment::update($id, [
            'status' => 'cancelled',
            'cancelledAt' => new \MongoDB\BSON\UTCDateTime(),
            'cancelReason' => $input['reason'] ?? 'Cancelled by staff',
            'cancelledByStaffId' => $staff['id'],
        ]);

        $patient = Patient::findById($appointment['patientId']);
        $doctor = Doctor::findById($appointment['doctorId']);
        if ($patient && $doctor) {
            EmailService::sendCancellationNotice(
                $patient['email'],
                $patient['firstName'],
                "Dr. {$doctor['firstName']} {$doctor['lastName']}",
                $appointment['appointmentDate'],
                $appointment['timeSlot']
            );
        }

        echo json_encode(['message' => 'Appointment cancelled, patient notified']);
    }

    public static function bulkCancel(): void
    {
        $staff = requireStaff();
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['doctorId']) || empty($input['date'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Doctor ID and date are required']);
            return;
        }

        $appointments = Appointment::findAll([
            'doctorId' => $input['doctorId'],
            'appointmentDate' => $input['date'],
            'status' => ['$nin' => ['cancelled', 'completed', 'no_show']],
        ]);

        $count = 0;
        foreach ($appointments as $a) {
            Appointment::update((string) $a['_id'], [
                'status' => 'cancelled',
                'cancelledAt' => new \MongoDB\BSON\UTCDateTime(),
                'cancelReason' => $input['reason'] ?? 'Doctor unavailable (bulk cancel)',
                'cancelledByStaffId' => $staff['id'],
            ]);

            $patient = Patient::findById($a['patientId']);
            $doctor = Doctor::findById($input['doctorId']);
            if ($patient && $doctor) {
                EmailService::sendBulkCancellation(
                    $patient['email'],
                    $patient['firstName'],
                    "Dr. {$doctor['firstName']} {$doctor['lastName']}",
                    $input['date']
                );
            }
            $count++;
        }

        echo json_encode([
            'message' => "$count appointment(s) cancelled, patients notified",
            'count' => $count,
        ]);
    }

    public static function adminList(): void
    {
        requireStaff();
        $filter = [];
        if (!empty($_GET['date'])) $filter['appointmentDate'] = $_GET['date'];
        if (!empty($_GET['doctorId'])) $filter['doctorId'] = $_GET['doctorId'];
        if (!empty($_GET['departmentId'])) $filter['departmentId'] = $_GET['departmentId'];
        if (!empty($_GET['status'])) $filter['status'] = $_GET['status'];
        if (!empty($_GET['patientName'])) {
            $regex = new \MongoDB\BSON\Regex(preg_quote($_GET['patientName']), 'i');
            $patients = getCollection('patients')->find([
                '$or' => [['firstName' => $regex], ['lastName' => $regex]],
            ])->toArray();
            $patientIds = array_map(fn($p) => (string) $p['_id'], $patients);
            $filter['patientId'] = ['$in' => $patientIds];
        }

        $appointments = Appointment::findAll($filter);
        $result = array_map(function ($a) {
            $a['_id'] = (string) $a['_id'];
            return $a;
        }, $appointments);
        echo json_encode($result);
    }

    public static function walkInBook(): void
    {
        $staff = requireStaff();
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['phone'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Patient phone is required']);
            return;
        }

        $patient = Patient::findByPhone($input['phone']);
        if (!$patient) {
            if (empty($input['firstName']) || empty($input['lastName'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Patient name required for new patient registration']);
                return;
            }
            $patientId = Patient::create([
                'firstName' => $input['firstName'],
                'lastName' => $input['lastName'],
                'email' => $input['email'] ?? $input['phone'] . '@walkin.medibook',
                'phone' => $input['phone'],
                'gender' => $input['gender'] ?? 'unspecified',
                'password' => bin2hex(random_bytes(8)),
                'isEmailVerified' => true,
            ]);
        } else {
            $patientId = (string) $patient['_id'];
        }

        $error = AvailabilityService::validateSlot(
            $input['doctorId'],
            $input['appointmentDate'],
            $input['timeSlot']
        );

        if ($error) {
            http_response_code(409);
            echo json_encode(['error' => $error]);
            return;
        }

        $appointmentId = Appointment::create([
            'patientId' => $patientId,
            'doctorId' => $input['doctorId'],
            'departmentId' => $input['departmentId'],
            'appointmentDate' => $input['appointmentDate'],
            'timeSlot' => $input['timeSlot'],
            'reasonForVisit' => $input['reasonForVisit'] ?? '',
            'bookedByType' => 'front_desk',
            'bookedByStaffId' => $staff['id'],
            'status' => 'confirmed',
        ]);

        http_response_code(201);
        echo json_encode([
            'message' => 'Walk-in appointment booked successfully',
            'id' => $appointmentId,
            'patientId' => $patientId,
        ]);
    }
}
