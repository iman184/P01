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
    
    // Try to extract student data
    // Check which columns have meaningful data
    if (isset($cellData['A'])) {
        $students[] = $cellData;
    }
}

echo "=== Found " . count($students) . " students ===\n\n";
echo "Sample structure (first 5 students):\n";

for ($i = 0; $i < min(5, count($students)); $i++) {
    $s = $students[$i];
    echo "\nRow " . ($i + 2) . ":\n";
    foreach ($s as $col => $val) {
        echo "  [$col] => " . substr($val, 0, 50) . "\n";
    }
}

// Now insert students - need to figure out which columns have what
// Based on the shared strings I saw: student numbers, last names, first names
echo "\n\nLooking for actual student data pattern...\n";

// Find columns that seem to contain names (longer strings)
$firstStudentRow = $students[0] ?? [];
$dataColumns = [];
foreach ($firstStudentRow as $col => $val) {
    if (!empty($val)) {
        $dataColumns[$col] = $val;
        echo "$col: $val\n";
    }
}
?>
