<?php
$file = __DIR__ . '/L2 ISIL C (Liste Affichage).xlsx';

if (!file_exists($file)) {
    die("❌ File not found: $file\n");
}

try {
    // Open XLSX as ZIP
    $zip = new ZipArchive();
    if ($zip->open($file) !== true) {
        die("❌ Could not open Excel file\n");
    }

    // Read sheet data
    $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $zip->close();

    if (!$xml) {
        die("❌ Could not read sheet data\n");
    }

    // Parse XML
    $doc = new SimpleXMLElement($xml);
    
    // Register namespace
    $ns = $doc->getNamespaces();
    $doc->registerXPathNamespace('main', key($ns));

    $rows = $doc->xpath('//main:row');
    
    echo "=== L2 ISIL C Students ===\n\n";
    $count = 0;
    
    foreach ($rows as $row) {
        $cells = $row->xpath('.//main:c');
        $data = [];
        
        foreach ($cells as $cell) {
            $v = $cell->v;
            $data[] = (string)$v;
        }
        
        if (!empty(array_filter($data))) {
            echo implode(" | ", $data) . "\n";
            $count++;
        }
    }
    
    echo "\n✅ Total rows: $count\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
