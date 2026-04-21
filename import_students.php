<?php
require_once 'config/db.php';

$file = 'L2 ISIL C (Liste Affichage).xlsx';

$zip = new ZipArchive();
$zip->open($file);

// Get shared strings
$sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
$doc = new SimpleXMLElement($sharedStringsXml);
$sharedStrings = [];
foreach ($doc->si as $si) {
    $sharedStrings[] = (string)$si->t;
}

// Get worksheet
$sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
$doc = new SimpleXMLElement($sheetXml);
$zip->close();

$students = [];
$rowNum = 0;

foreach ($doc->sheetData->row as $row) {
    $rowNum++;
    if ($rowNum == 1) continue; // Skip header
    
    $colNum = 0;
    $rowData = [];
    
    foreach ($row->c as $cell) {
        $cellType = (string)$cell['t'];
        $value = (string)$cell->v;
        
        // Convert shared string index to actual value
        if ($cellType === 's') {
            $rowData[] = $sharedStrings[$value] ?? '';
        } else {
            $rowData[] = $value;
        }
    }
    
    if (count($rowData) >= 3 && !empty($rowData[0])) {
        $students[] = $rowData;
    }
}

echo "=== Found " . count($students) . " students ===\n\n";

// Display first 10 students
echo "Sample of students to import:\n";
$displayLimit = min(10, count($students));
for ($i = 0; $i < $displayLimit; $i++) {
    $s = $students[$i];
    echo ($i+1) . ". " . implode(" | ", array_slice($s, 0, 4)) . "\n";
}

// Insert into database
$inserted = 0;
$errors = [];

echo "\n\nInserting students...\n";

foreach ($students as $i => $student) {
    $student_number = trim($student[0] ?? '');
    $last_name = trim($student[1] ?? '');
    $first_name = trim($student[2] ?? '');
    $email = trim($student[3] ?? 'no-email@university.com');
    
    if (empty($student_number) || empty($last_name)) {
        $errors[] = "Row " . ($i + 2) . ": Missing student number or name";
        continue;
    }
    
    // Generate email if not provided
    if (empty($email) || $email === 'no-email@university.com') {
        $email = strtolower(str_replace(' ', '.', $first_name . '.' . $last_name)) . '@university.com';
    }
    
    $password_hash = password_hash('student123', PASSWORD_BCRYPT);
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO students (student_number, first_name, last_name, email, password_hash, is_active, must_change_password)
            VALUES (?, ?, ?, ?, ?, 1, 1)
        ");
        
        $stmt->execute([
            $student_number,
            $first_name,
            $last_name,
            $email,
            $password_hash
        ]);
        
        $inserted++;
        echo "✅ $student_number - $first_name $last_name\n";
        
    } catch (PDOException $e) {
        $errors[] = "Row " . ($i + 2) . ": " . $e->getMessage();
        echo "❌ $student_number - Error: " . $e->getMessage() . "\n";
    }
}

echo "\n=== SUMMARY ===\n";
echo "✅ Successfully inserted: $inserted students\n";
if (!empty($errors)) {
    echo "❌ Errors: " . count($errors) . "\n";
    foreach ($errors as $err) {
        echo "  - $err\n";
    }
}
?>
