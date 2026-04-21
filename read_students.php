<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = __DIR__ . '/L2 ISIL C (Liste Affichage).xlsx';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

try {
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    
    echo "=== L2 ISIL C Students ===\n\n";
    
    foreach ($sheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        
        $cells = [];
        foreach ($cellIterator as $cell) {
            $cells[] = $cell->getValue();
        }
        
        // Skip empty rows and headers
        if (empty(array_filter($cells))) continue;
        
        echo implode(" | ", $cells) . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
