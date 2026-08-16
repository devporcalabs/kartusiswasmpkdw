<?php
require_once 'config.php';

$hizkiel_nisn = '3138393310';
$heskel_nisn = '3141270607';

$photoMap = json_decode(file_get_contents('photo_map.json'), true);

echo "Hizkiel NISN map: " . ($photoMap[$hizkiel_nisn] ?? 'NOT FOUND') . "\n";
echo "Heskel NISN map: " . ($photoMap[$heskel_nisn] ?? 'NOT FOUND') . "\n";

$file1 = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/7.9/HESKEL LEFFIE ARSHAVIN.webp';
$file2 = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/7.1/HIZKIEL MA_ARIF VIRLIANSAH.webp';
$file3 = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/7.9/HIZKIEL MA_ARIF VIRLIANSAH.webp';

echo "File 1 (Heskel 7.9) exists: " . (file_exists($file1) ? "YES" : "NO") . "\n";
echo "File 2 (Hizkiel 7.1) exists: " . (file_exists($file2) ? "YES" : "NO") . "\n";
echo "File 3 (Hizkiel 7.9) exists: " . (file_exists($file3) ? "YES" : "NO") . "\n";
?>
