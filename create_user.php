<?php
require_once 'config/db.php';

// Create admin
$stmt = $pdo->prepare("INSERT INTO admin (username, password_hash) VALUES (?, ?)");
$stmt->execute([
    'admin',
    password_hash('admin123', PASSWORD_BCRYPT)
]);

// Create teachers
$stmt = $pdo->prepare("INSERT INTO teachers (last_name, first_name, email, subject, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Gheffar',
    'Amina',
    'amina.gheffar@gmail.com',
    'Database',
    password_hash('gheffaramina123', PASSWORD_BCRYPT),
    0
]);

$stmt = $pdo->prepare("INSERT INTO teachers (last_name, first_name, email, subject, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Abdellahoum',
    'Hamza',
    'abdellahoumhamza89@gmail.com',
    'Database',
    password_hash('abdellahoumhamza123', PASSWORD_BCRYPT),
    0
]);

$stmt = $pdo->prepare("INSERT INTO teachers (last_name, first_name, email, subject, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Laachemi',
    'Labde',
    'labde79@gmail.com',
    'Database',
    password_hash('laachemilabde123', PASSWORD_BCRYPT),
    0
]);

// Create students
$stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, student_number, email, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Zighed',
    'Imen',
    '232335330411',
    'zighedimen921@gmail.com',
    password_hash('zighedimen123', PASSWORD_BCRYPT),
    0
]);

$stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, student_number, email, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Lakhal',
    'Dekrah',
    '242431577219',
    'dekrah.lakhal@gmail.com',
    password_hash('lakhaldekrah123', PASSWORD_BCRYPT),
    0
]);

$stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, student_number, email, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Ramoul',
    'Meriem',
    '242431422801',
    'meriem.ramoul@gmail.com',
    password_hash('ramoulmeriem123', PASSWORD_BCRYPT),
    0
]);

$stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, student_number, email, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Bearcia',
    'Issam',
    '2323314125006',
    'issam.bearcia@gmail.com',
    password_hash('bearciaissam123', PASSWORD_BCRYPT),
    0
]);

echo "All users created successfully!";
