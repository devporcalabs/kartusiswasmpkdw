<?php
// Script to sync FixData.csv NISNs with DataMuridSMPN1KDW_Rapi.csv,
// preserve manual session updates, and blank out duplicate NISNs.

$rapiFile = '_tools_and_raw_data/DataMuridSMPN1KDW_Rapi.csv';
$csvFile = 'FixData.csv';

// 1. Load DataMuridSMPN1KDW_Rapi.csv
$rapiStudents = [];
if (($handle = fopen($rapiFile, 'r')) !== false) {
    $headers = fgetcsv($handle);
    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 5) continue;
        $name = trim($row[1]);
        $nisn = trim($row[4]);
        if (!empty($name) && !empty($nisn)) {
            $normName = preg_replace('/[^A-Z0-9]/', '', strtoupper($name));
            $rapiStudents[$normName] = $nisn;
        }
    }
    fclose($handle);
}
echo "Loaded " . count($rapiStudents) . " students from DataMuridSMPN1KDW_Rapi.csv\n";

// 2. Load current FixData.csv
$lines = file($csvFile);
$header = array_shift($lines);
$students = [];
$seenNis = [];

foreach ($lines as $line) {
    $row = str_getcsv($line);
    if (count($row) < 3) continue;
    $nis = trim($row[0]);
    if (isset($seenNis[$nis])) {
        echo "Duplicate student record found for NIS $nis. Skipping.\n";
        continue;
    }
    $seenNis[$nis] = true;
    $students[] = [
        'nis' => $nis,
        'nisn' => trim($row[1]),
        'name' => trim($row[2]),
        'row' => $row
    ];
}

// 3. Define manual updates from our session
$sessionUpdates = [
    '262707249' => '3143346213', // Muhamad Raihan
    '262707346' => '3129327435', // Rayyan Lail Asawal
    '262707092' => '0139170214', // Cintani SeptIara Setiawan
    '262707029' => '0148858662', // Alika Naila Putri (7.05)
    '262707432' => '3147685200', // Yuki Yanuar
    '262707343' => '0138926726', // Ratu Andrean
    '262707199' => '3130830930', // Kanaya Putri
    '262707287' => '3146155359', // Najwa Aulia
    '262707050' => '3148098380', // Aprillita Putri
    '262707368' => '0132084301', // Sahrul Ramadhan
    '262707171' => '3141270607'  // Heskel Leffie Arshavin
];

// 4. Sync NISNs
$syncedFromRapi = 0;
$syncedFromSession = 0;

foreach ($students as &$s) {
    $nis = $s['nis'];
    $normName = preg_replace('/[^A-Z0-9]/', '', strtoupper($s['name']));
    
    if (isset($sessionUpdates[$nis])) {
        // Protect session manual updates
        $s['nisn'] = $sessionUpdates[$nis];
        $s['row'][1] = $sessionUpdates[$nis];
        $syncedFromSession++;
    } else if (isset($rapiStudents[$normName])) {
        // Update to Rapi.csv NISN
        $s['nisn'] = $rapiStudents[$normName];
        $s['row'][1] = $rapiStudents[$normName];
        $syncedFromRapi++;
    }
}
unset($s);

echo "Synced $syncedFromRapi NISNs from Rapi.csv\n";
echo "Preserved $syncedFromSession manual session NISN updates\n";

// 5. Detect and clear duplicate NISNs
$nisnCounts = [];
foreach ($students as $s) {
    $nisn = $s['nisn'];
    if (!empty($nisn) && $nisn != '-') {
        if (!isset($nisnCounts[$nisn])) $nisnCounts[$nisn] = 0;
        $nisnCounts[$nisn]++;
    }
}

$clearedCount = 0;
$seenNisns = [];

foreach ($students as &$s) {
    $nisn = $s['nisn'];
    if (!empty($nisn) && $nisn != '-') {
        if (isset($seenNisns[$nisn])) {
            // This is a duplicate! Set to empty as requested by user
            echo "Duplicate NISN '{$nisn}' found. Blanking out for {$s['name']} (NIS: {$s['nis']})\n";
            $s['nisn'] = '';
            $s['row'][1] = '';
            $clearedCount++;
        } else {
            $seenNisns[$nisn] = true;
        }
    }
}
unset($s);

echo "Blanked out $clearedCount duplicate NISNs.\n";

// 6. Save back to FixData.csv
$newLines = [$header];
foreach ($students as $s) {
    $fields = [];
    foreach ($s['row'] as $f) {
        if (strpos($f, ',') !== false || strpos($f, '"') !== false) {
            $fields[] = '"' . str_replace('"', '""', $f) . '"';
        } else {
            $fields[] = $f;
        }
    }
    $newLines[] = implode(',', $fields) . "\n";
}
file_put_contents($csvFile, implode('', $newLines));
echo "FixData.csv saved successfully.\n";

// 7. Regenerate photo_map.json based on current unique NISNs
$photoMap = [];
foreach ($students as $s) {
    $nisn = $s['nisn'];
    if (empty($nisn) || $nisn == '-') continue;
    
    // Find photo path
    $className = str_replace('KELAS ', '', trim($s['row'][8]));
    $photoFile = "PAS_FOTO_SMPN_1_KEDUNGWARINGIN/{$className}/" . $s['name'] . ".webp";
    $photoFileClean = str_replace("'", "", $photoFile);
    
    if (file_exists($photoFileClean)) {
        $photoMap[$nisn] = $photoFileClean;
    } else {
        // Try SUSULAN
        $photoFileSusulan = "PAS_FOTO_SMPN_1_KEDUNGWARINGIN/SUSULAN/" . $s['name'] . ".webp";
        if (file_exists($photoFileSusulan)) {
            $photoMap[$nisn] = $photoFileSusulan;
        } else {
            // Try fuzzy search
            $dir = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN';
            $di = new RecursiveDirectoryIterator($dir);
            foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
                if ($file->isFile()) {
                    $baseName = pathinfo($filename, PATHINFO_FILENAME);
                    if (stripos($baseName, str_replace("'", "", $s['name'])) !== false || stripos($s['name'], $baseName) !== false) {
                        $photoMap[$nisn] = str_replace('\\', '/', $filename);
                        break;
                    }
                }
            }
        }
    }
}

// Special check for Sahrul and Heskel
if (isset($sessionUpdates['262707368'])) {
    $photoMap['0132084301'] = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/SUSULAN/Sahrul Ramadhan.webp';
}
if (isset($sessionUpdates['262707171'])) {
    $photoMap['3141270607'] = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/SUSULAN/heskhel leffie arsavin.webp';
}

$photoMapFile = 'photo_map.json';
file_put_contents($photoMapFile, json_encode($photoMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "photo_map.json saved successfully. Total maps: " . count($photoMap) . "\n";
?>
