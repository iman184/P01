<?php
require_once 'config/db.php';

// Create admin
/* $stmt = $pdo->prepare("INSERT INTO admin (username, email, password_hash) VALUES (?, ?, ?)");
$stmt->execute([
    'Admin',
    'admin@school.com',
    password_hash('admin123', PASSWORD_BCRYPT)
]);*/

// Create teachers (repeat for each of your 7)
/* $stmt = $pdo->prepare("INSERT INTO teachers (last_name ,first_name, email,subject, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Gheffar',
    'Amina',
    'amina.gheffar@gmail.com',
    'Database',
    password_hash('gheffaramina123', PASSWORD_BCRYPT),
    true
]);
 $stmt = $pdo->prepare("INSERT INTO teachers (last_name ,first_name, email,subject, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Abdellahoum',
    'Hamza',
    'abdellahoumhamza89@gmail.com',
    'Database',
    password_hash('abdellahoumhamza123', PASSWORD_BCRYPT),
    true
]);
 $stmt = $pdo->prepare("INSERT INTO teachers (last_name ,first_name, email,subject, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Laachemi',
    'labde',
    'labde79@gmail.com',
    'Database',
    password_hash('laachemilabde123', PASSWORD_BCRYPT),
    true
]);



// Create students (repeat for each student)
$stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, student_number, email, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Zighed',
    'Imen',
    '232335330411',
    'zighedimen921@gmail.com',
    password_hash('zighedimen123', PASSWORD_BCRYPT),
    true
]);
/*$stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, student_number, email, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Lakhal',
    'Dekrah',
    '242431577219',
    'dekrah.lakhal@gmail.com',
    password_hash('lakhaldekrah123', PASSWORD_BCRYPT),
    true
]);
$stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, student_number, email, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Ramoul',
    'Meriem',
    '242431422801',
    'meriem.ramoul@gmail.com',
    password_hash('ramoulmeriem123', PASSWORD_BCRYPT),
    true
]);*/
$stmt = $pdo->prepare("INSERT INTO students (last_name, first_name, student_number, email, password_hash, must_change_password) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([
    'Bearcia',
    'Issam',
    '2323314125006',
    'issam.bearcia@gmail.com',
    password_hash('bearciaissam123', PASSWORD_BCRYPT),
    true
]);


echo "All users created successfully!";
