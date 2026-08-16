<?php
$dir = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN';
$di = new RecursiveDirectoryIterator($dir);
foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
    if (stripos($filename, 'heskel') !== false || stripos($filename, 'arshavin') !== false) {
        echo "$filename\n";
    }
}
?>
