<?php
$host     = 'localhost';
$port     = 3306;
$dbname   = 'school_systems';
$username = 'root';
$password = '';


try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Backward-compatible schema guard for existing databases.
    $requiredStudentColumns = [
        'profile_image' => "ALTER TABLE students ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER email",
        'last_login' => "ALTER TABLE students ADD COLUMN last_login DATETIME DEFAULT NULL AFTER must_change_password",
        'last_activity' => "ALTER TABLE students ADD COLUMN last_activity DATETIME DEFAULT NULL AFTER last_login",
    ];

    $columnCheck = $pdo->prepare(
        "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'students' AND COLUMN_NAME = ?"
    );

    foreach ($requiredStudentColumns as $columnName => $alterSql) {
        $columnCheck->execute([$dbname, $columnName]);
        if ((int) $columnCheck->fetchColumn() === 0) {
            $pdo->exec($alterSql);
        }
    }

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}