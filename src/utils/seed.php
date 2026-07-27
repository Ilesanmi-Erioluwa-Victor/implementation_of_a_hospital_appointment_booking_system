<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

echo "Seeding MediBook database...\n";

$db = getMongoDB();
$db->drop();

$deptIds = [];

$departments = [
    ['name' => 'Cardiology', 'description' => 'Heart and cardiovascular system specialists'],
    ['name' => 'Pediatrics', 'description' => 'Medical care for infants, children, and adolescents'],
    ['name' => 'General Outpatient', 'description' => 'General medical consultations and treatments'],
    ['name' => 'Orthopedics', 'description' => 'Bone, joint, and muscle specialists'],
    ['name' => 'Obstetrics & Gynecology', 'description' => 'Women\'s health and reproductive medicine'],
    ['name' => 'Dermatology', 'description' => 'Skin, hair, and nail specialists'],
    ['name' => 'Neurology', 'description' => 'Brain and nervous system specialists'],
    ['name' => 'Radiology', 'description' => 'Medical imaging and diagnosis'],
];

foreach ($departments as $d) {
    $id = MediBook\Models\Department::create($d);
    $deptIds[$d['name']] = $id;
    echo "  Department: {$d['name']} ($id)\n";
}

$doctors = [
    [
        'firstName' => 'Chukwudi', 'lastName' => 'Okonkwo',
        'email' => 'chukwudi.okonkwo@medibook.app', 'phone' => '08010000001',
        'departmentId' => $deptIds['Cardiology'],
        'bio' => 'Senior cardiologist with 15 years of experience in interventional cardiology.',
        'slotDurationMinutes' => 30,
        'isActive' => true,
        'workingHours' => [
            ['dayOfWeek' => 1, 'startTime' => '08:00', 'endTime' => '16:00'],
            ['dayOfWeek' => 2, 'startTime' => '08:00', 'endTime' => '16:00'],
            ['dayOfWeek' => 3, 'startTime' => '08:00', 'endTime' => '16:00'],
            ['dayOfWeek' => 4, 'startTime' => '08:00', 'endTime' => '16:00'],
            ['dayOfWeek' => 5, 'startTime' => '08:00', 'endTime' => '14:00'],
        ],
    ],
    [
        'firstName' => 'Amara', 'lastName' => 'Okafor',
        'email' => 'amara.okafor@medibook.app', 'phone' => '08010000002',
        'departmentId' => $deptIds['Pediatrics'],
        'bio' => 'Consultant pediatrician specializing in neonatal care and childhood developmental disorders.',
        'slotDurationMinutes' => 20,
        'isActive' => true,
        'workingHours' => [
            ['dayOfWeek' => 1, 'startTime' => '09:00', 'endTime' => '17:00'],
            ['dayOfWeek' => 2, 'startTime' => '09:00', 'endTime' => '17:00'],
            ['dayOfWeek' => 3, 'startTime' => '09:00', 'endTime' => '17:00'],
            ['dayOfWeek' => 4, 'startTime' => '09:00', 'endTime' => '17:00'],
            ['dayOfWeek' => 5, 'startTime' => '09:00', 'endTime' => '15:00'],
        ],
    ],
    [
        'firstName' => 'Kelechi', 'lastName' => 'Nwachukwu',
        'email' => 'kelechi.nwachukwu@medibook.app', 'phone' => '08010000003',
        'departmentId' => $deptIds['General Outpatient'],
        'bio' => 'General practitioner providing comprehensive primary care services.',
        'slotDurationMinutes' => 15,
        'isActive' => true,
        'workingHours' => [
            ['dayOfWeek' => 0, 'startTime' => '10:00', 'endTime' => '14:00'],
            ['dayOfWeek' => 1, 'startTime' => '08:00', 'endTime' => '18:00'],
            ['dayOfWeek' => 2, 'startTime' => '08:00', 'endTime' => '18:00'],
            ['dayOfWeek' => 3, 'startTime' => '08:00', 'endTime' => '18:00'],
            ['dayOfWeek' => 4, 'startTime' => '08:00', 'endTime' => '18:00'],
            ['dayOfWeek' => 5, 'startTime' => '08:00', 'endTime' => '16:00'],
        ],
    ],
    [
        'firstName' => 'Ngozi', 'lastName' => 'Eze',
        'email' => 'ngozi.eze@medibook.app', 'phone' => '08010000004',
        'departmentId' => $deptIds['Orthopedics'],
        'bio' => 'Orthopedic surgeon focused on sports injuries and joint replacement.',
        'slotDurationMinutes' => 30,
        'isActive' => true,
        'workingHours' => [
            ['dayOfWeek' => 1, 'startTime' => '09:00', 'endTime' => '15:00'],
            ['dayOfWeek' => 2, 'startTime' => '09:00', 'endTime' => '15:00'],
            ['dayOfWeek' => 3, 'startTime' => '09:00', 'endTime' => '15:00'],
            ['dayOfWeek' => 4, 'startTime' => '09:00', 'endTime' => '15:00'],
        ],
    ],
];

foreach ($doctors as $d) {
    $id = MediBook\Models\Doctor::create($d);
    echo "  Doctor: Dr. {$d['firstName']} {$d['lastName']} ($id)\n";
}

$staffUsers = [
    [
        'name' => 'Admin User',
        'email' => 'admin@medibook.app',
        'password' => 'admin123',
        'role' => 'admin',
    ],
    [
        'name' => 'Front Desk User',
        'email' => 'frontdesk@medibook.app',
        'password' => 'front123',
        'role' => 'front_desk',
    ],
];

foreach ($staffUsers as $s) {
    $id = MediBook\Models\StaffUser::create($s);
    echo "  Staff: {$s['name']} ({$s['role']}) - $id\n";
}

echo "\nSeed complete!\n";
echo "Staff logins:\n";
echo "  Admin: admin@medibook.app / admin123\n";
echo "  Front Desk: frontdesk@medibook.app / front123\n";
