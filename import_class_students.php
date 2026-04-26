<?php
require_once __DIR__ . '/config/db.php';

$defaultCsvPath = __DIR__ . '/L2_ISIL_C_students.csv';
$csvPath = $defaultCsvPath;
$cleanupTemp = true;

if (PHP_SAPI === 'cli' && isset($argv[1]) && is_string($argv[1]) && $argv[1] !== '') {
    $csvPath = $argv[1];
    if (isset($argv[2])) {
        $cleanupTemp = ((string) $argv[2]) !== '0';
    }
} elseif (isset($_GET['file']) && is_string($_GET['file']) && $_GET['file'] !== '') {
    $csvPath = __DIR__ . '/' . basename($_GET['file']);
}

if (isset($_GET['cleanup_temp'])) {
    $cleanupTemp = ((string) $_GET['cleanup_temp']) !== '0';
}

header_if_web();

if (!is_file($csvPath)) {
    output("CSV file not found: {$csvPath}");
    exit(1);
}

$fp = fopen($csvPath, 'r');
if ($fp === false) {
    output("Unable to open CSV file: {$csvPath}");
    exit(1);
}

$header = fgetcsv($fp);
if ($header === false) {
    fclose($fp);
    output('CSV appears empty.');
    exit(1);
}

$columns = array_map('trim', $header);
$idx = array_flip($columns);

$required = ['FirstName', 'LastName', 'Email', 'StudentNumber', 'Password'];
foreach ($required as $col) {
    if (!isset($idx[$col])) {
        fclose($fp);
        output("Missing required column: {$col}");
        exit(1);
    }
}

$hasBirthDate = isset($idx['BirthDate']);

$sql = "INSERT INTO students (
            first_name,
            last_name,
            email,
            student_number,
            birth_date,
            password_hash,
            must_change_password,
            is_active
        ) VALUES (?, ?, ?, ?, ?, ?, 0, 1)
        ON DUPLICATE KEY UPDATE
            first_name = VALUES(first_name),
            last_name = VALUES(last_name),
            student_number = VALUES(student_number),
            birth_date = VALUES(birth_date),
            password_hash = VALUES(password_hash),
            must_change_password = VALUES(must_change_password),
            is_active = VALUES(is_active)";

$stmt = $pdo->prepare($sql);

$total = 0;
$insertedOrUpdated = 0;
$skipped = 0;
$skippedRows = [];
$removedTemp = 0;

try {
    $pdo->beginTransaction();

    if ($cleanupTemp) {
        // Remove old demo/generated students from previous seed scripts.
        $cleanupStmt = $pdo->prepare(
            "DELETE FROM students
             WHERE email LIKE 'student%@class.local'
                OR (first_name LIKE 'Student%' AND last_name = 'Class' AND email LIKE 'student%@class.%')"
        );
        $cleanupStmt->execute();
        $removedTemp = $cleanupStmt->rowCount();
    }

    while (($row = fgetcsv($fp)) !== false) {
        $total++;

        $firstName = trim((string) ($row[$idx['FirstName']] ?? ''));
        $lastName = trim((string) ($row[$idx['LastName']] ?? ''));
        $email = trim((string) ($row[$idx['Email']] ?? ''));
        $studentNumber = trim((string) ($row[$idx['StudentNumber']] ?? ''));
        $password = (string) ($row[$idx['Password']] ?? '');

        $birthDate = null;
        if ($hasBirthDate) {
            $value = trim((string) ($row[$idx['BirthDate']] ?? ''));
            if ($value !== '') {
                $birthDate = $value;
            }
        }

        if ($firstName === '' || $lastName === '' || $email === '' || $studentNumber === '' || $password === '') {
            $skipped++;
            $skippedRows[] = $total + 1;
            continue;
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->execute([
            $firstName,
            $lastName,
            $email,
            $studentNumber,
            $birthDate,
            $passwordHash,
        ]);

        $insertedOrUpdated++;
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fclose($fp);
    output('Import failed: ' . $e->getMessage());
    exit(1);
}

fclose($fp);

output('Import complete.');
output('CSV file: ' . $csvPath);
output('Temporary demo students removed: ' . $removedTemp);
output('Rows read: ' . $total);
output('Inserted/Updated: ' . $insertedOrUpdated);
output('Skipped: ' . $skipped);
if (!empty($skippedRows)) {
    output('Skipped CSV row numbers: ' . implode(', ', $skippedRows));
}

exit(0);

function header_if_web(): void {
    if (PHP_SAPI !== 'cli') {
        header('Content-Type: text/plain; charset=utf-8');
    }
}

function output(string $text): void {
    echo $text . PHP_EOL;
}
