<?php
$file = __DIR__ . '/L2 ISIL C (Liste Affichage).xlsx';

try {
    $zip = new ZipArchive();
    $zip->open($file);

    // Read shared strings (where text is stored)
    $sharedStrings = [];
    $xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($xml) {
        $doc = new SimpleXMLElement($xml);
        $ns = $doc->getNamespaces();
        $doc->registerXPathNamespace('main', key($ns));
        $strings = $doc->xpath('//main:si/main:t');
        foreach ($strings as $str) {
            $sharedStrings[] = (string)$str;
        }
    }

    // Read worksheet
    $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
    $doc = new SimpleXMLElement($xml);
    $ns = $doc->getNamespaces();
    $doc->registerXPathNamespace('main', key($ns));

    $rows = $doc->xpath('//main:row');
    
    echo "=== L2 ISIL C Students ===\n\n";
    
    $students = [];
    foreach ($rows as $row) {
        $cells = $row->xpath('.//main:c');
        $rowData = [];
        
        foreach ($cells as $cell) {
            $t = $cell->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
            $type = (string)$cell->attributes()->t;
            
            if ($type === 's') {
                // String stored in sharedStrings
                $idx = (int)$cell->v;
                $rowData[] = $sharedStrings[$idx] ?? '';
            } else {
                // Number or formula result
                $rowData[] = (string)($cell->v ?? '');
            }
        }
        
        if (!empty(array_filter($rowData))) {
            $line = implode(" | ", $rowData);
            echo $line . "\n";
            $students[] = $rowData;
        }
    }
    
    $zip->close();
    
    echo "\n✅ Total students: " . (count($students) - 1) . "\n"; // -1 for header
    
    // Return as JSON for later use
    echo "\nJSON: " . json_encode($students) . "\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
