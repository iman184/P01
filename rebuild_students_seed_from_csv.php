<?php
require_once __DIR__ . '/config/db.php';

$csvPath = __DIR__ . '/L2_ISIL_C_students.csv';
$outPath = __DIR__ . '/students_seed_clean.sql';

if (!is_file($csvPath)) {
    die("CSV not found: $csvPath\n");
}

$fp = fopen($csvPath, 'r');
if ($fp === false) {
    die("Cannot open CSV\n");
}

$header = fgetcsv($fp);
if ($header === false) {
    fclose($fp);
    die("CSV is empty\n");
}

$idx = array_flip(array_map('trim', $header));
$required = ['FirstName', 'LastName', 'Email', 'StudentNumber', 'Password'];
foreach ($required as $col) {
    if (!isset($idx[$col])) {
        fclose($fp);
        die("Missing column: $col\n");
    }
}

$values = [];
while (($row = fgetcsv($fp)) !== false) {
    $first = trim((string)($row[$idx['FirstName']] ?? ''));
    $last = trim((string)($row[$idx['LastName']] ?? ''));
    $email = trim((string)($row[$idx['Email']] ?? ''));
    $num = trim((string)($row[$idx['StudentNumber']] ?? ''));
    $pw = (string)($row[$idx['Password']] ?? '');

    if ($first === '' || $last === '' || $email === '' || $num === '' || $pw === '') {
        continue;
    }

    // Keep SQL import robust in phpMyAdmin by transliterating to ASCII for seed file only.
    $firstAscii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $first);
    $lastAscii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $last);
    if ($firstAscii === false) {
        $firstAscii = $first;
    }
    if ($lastAscii === false) {
        $lastAscii = $last;
    }

    $firstSql = str_replace("'", "''", $firstAscii);
    $lastSql = str_replace("'", "''", $lastAscii);
    $emailSql = str_replace("'", "''", $email);
    $numSql = str_replace("'", "''", $num);

    $hash = password_hash($pw, PASSWORD_BCRYPT);
    $hashSql = str_replace("'", "''", $hash);

    $values[] = "('$firstSql', '$lastSql', '$emailSql', '$numSql', NULL, '$hashSql', 0, 1)";
}
fclose($fp);

$sql = [];
$sql[] = "DELETE FROM students WHERE email LIKE 'student%@class.local';";
$sql[] = "";
$sql[] = "INSERT INTO students (first_name, last_name, email, student_number, birth_date, password_hash, must_change_password, is_active)";
$sql[] = "VALUES";
$sql[] = '    ' . implode(",\n    ", $values);
$sql[] = "ON DUPLICATE KEY UPDATE";
$sql[] = "    first_name = VALUES(first_name),";
$sql[] = "    last_name = VALUES(last_name),";
$sql[] = "    student_number = VALUES(student_number),";
$sql[] = "    birth_date = VALUES(birth_date),";
$sql[] = "    password_hash = VALUES(password_hash),";
$sql[] = "    must_change_password = VALUES(must_change_password),";
$sql[] = "    is_active = VALUES(is_active);";

file_put_contents($outPath, implode("\n", $sql) . "\n");

echo "Generated: $outPath\n";
echo "Rows: " . count($values) . "\n";
