<?php
require_once '../auth/session.php';
if ($_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Accès non autorisé']));
}

require_once '../config/db.php';

// Function to check if student is online (active in last 30 minutes)
function is_student_online($last_activity) {
    if (!$last_activity) {
        return false;
    }
    $last_activity_time = strtotime($last_activity);
    $current_time = time();
    $timeout = 30 * 60; // 30 minutes
    
    return ($current_time - $last_activity_time) < $timeout;
}

// Fetch all students with their activity status
$students = $pdo->query("
    SELECT id, last_activity, last_login
    FROM students
    ORDER BY id ASC
")->fetchAll();

// Build response with online status
$response = [];
foreach ($students as $s) {
    $response[] = [
        'id' => $s['id'],
        'online' => is_student_online($s['last_activity']),
        'last_login' => $s['last_login']
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>
