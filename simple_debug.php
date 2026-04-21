<?php
$file = 'L2 ISIL C (Liste Affichage).xlsx';

$zip = new ZipArchive();
$zip->open($file);

// First, check sharedStrings
echo "=== SHARED STRINGS ===\n";
$xml = $zip->getFromName('xl/sharedStrings.xml');
$doc = new SimpleXMLElement($xml);

foreach ($doc->si as $si) {
    echo (string)$si->t . "\n";
}

echo "\n=== WORKSHEET XML (First 3000 chars) ===\n";
$sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
echo substr($sheet, 0, 3000);

$zip->close();
?>
