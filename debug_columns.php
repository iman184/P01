<?php
require_once 'config/db.php';

echo "<h3>Database Column Check</h3>";

// Check if columns exist
$result = $pdo->query('DESCRIBE students');
$columns = $result->fetchAll(PDO::FETCH_ASSOC);

echo "<h4>Tracking Columns:</h4>";
foreach ($columns as $col) {
    if (in_array($col['Field'], ['last_login', 'last_activity', 'profile_image'])) {
        echo $col['Field'] . ': ' . $col['Type'] . '<br>';
    }
}

// Check student data
echo "<h4>Student Last Login Data:</h4>";
$students = $pdo->query('SELECT id, first_name, last_name, last_login, last_activity FROM students LIMIT 5')->fetchAll();
foreach ($students as $s) {
    echo $s['first_name'] . ' ' . $s['last_name'] . ' - last_login: ' . ($s['last_login'] ?? 'NULL') . ' - last_activity: ' . ($s['last_activity'] ?? 'NULL') . '<br>';
}
?>
