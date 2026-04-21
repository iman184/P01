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

    $cellData = [];
    
    foreach ($row->c as $cell) {
        $cellType = (string)$cell['t'];
        $value = (string)$cell->v;
        $ref = (string)$cell['r'];
        
        // Extract column letter
        preg_match('/([A-Z]+)/', $ref, $matches);
        $col = $matches[1];
        
        // Convert shared string index to actual value
        if ($cellType === 's') {
            $actualValue = $sharedStrings[$value] ?? '';
        } else {
            $actualValue = $value;
        }
        
        $cellData[$col] = $actualValue;
    }
    
    if (isset($cellData['E'])) { // Column E has matricule
        $students[] = $cellData;
    }
}

echo "=== Importing " . count($students) . " L2 ISIL C Students ===\n\n";

// Insert into database
$inserted = 0;
$errors = [];
$duplicates = 0;

foreach ($students as $i => $cellData) {
    // Extract data from columns
    $student_number = trim($cellData['E'] ?? '');
    $last_name = trim($cellData['F'] ?? '');
    $first_name = trim($cellData['G'] ?? '');
    
    if (empty($student_number)) {
        $errors[] = "Row " . ($i + 2) . ": Missing matricule";
        continue;
    }
    
    if (empty($first_name) || empty($last_name)) {
        $errors[] = "Row " . ($i + 2) . " ($student_number): Missing first or last name";
        continue;
    }
    
    // Generate email
    $email = strtolower(
        str_replace(' ', '.', $first_name) . '.' . 
        str_replace(' ', '.', $last_name)
    ) . '@student.university.dz';
    
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
        echo "✅ [$student_number] $last_name $first_name\n";
        
    } catch (PDOException $e) {
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, '1062') !== false || strpos($errorMsg, 'Duplicate') !== false) {
            $duplicates++;
        } else {
            $errors[] = "Row " . ($i + 2) . " ($student_number): " . $errorMsg;
            echo "❌ [$student_number]: " . $errorMsg . "\n";
        }
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 60) . "\n";
echo "✅ Successfully inserted: $inserted students\n";
if ($duplicates > 0) {
    echo "⚠️  Duplicates (already exist): $duplicates\n";
}
if (!empty($errors)) {
    echo "❌ Errors: " . count($errors) . "\n";
    foreach (array_slice($errors, 0, 10) as $err) {
        echo "  - $err\n";
    }
    if (count($errors) > 10) {
        echo "  ... and " . (count($errors) - 10) . " more errors\n";
    }
}

echo "\n✅ Students can now login with:\n";
echo "   - Matricule: (from column E)\n";
echo "   - Password: student123\n";
echo "   - They will be prompted to change password on first login\n";

// Verify in database
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM students");
$stmt->execute();
$result = $stmt->fetch();
echo "\nTotal students in database: " . $result['count'] . "\n";
?>
