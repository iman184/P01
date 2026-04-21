<?php
$file = __DIR__ . '/L2 ISIL C (Liste Affichage).xlsx';

try {
    $zip = new ZipArchive();
    if ($zip->open($file) !== true) {
        die("Could not open file\n");
    }

    // List all files in the archive
    echo "Files in XLSX:\n";
    for ($i = 0; $i < $zip->numFiles; $i++) {
        echo "  " . $zip->getNameIndex($i) . "\n";
    }

    // Try to read the XML
    $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
    if (!$xml) {
        echo "\nTrying alternative paths...\n";
        // List all XML files
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (strpos($name, '.xml') !== false && strpos($name, 'worksheet') !== false) {
                echo "Found: $name\n";
                $xml = $zip->getFromName($name);
                break;
            }
        }
    }

    if ($xml) {
        echo "\n✅ Successfully read XML\n";
        echo "First 500 characters:\n";
        echo substr($xml, 0, 500) . "\n";
    }

    $zip->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
