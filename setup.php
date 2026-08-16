<?php
/* setup.php – Database initialization and data seeding */

require_once 'config.php';

// Atur limit waktu eksekusi agar tidak timeout saat memproses banyak data
set_time_limit(300);

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Inisialisasi Database Kartu Pelajar</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f1f5f9; padding: 40px; color: #1e293b; }
        .card { background: white; padding: 30px; border-radius: 12px; max-width: 600px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        h2 { color: #1565c0; margin-top: 0; }
        .log-box { background: #1e293b; color: #38bdf8; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; max-height: 300px; overflow-y: auto; margin-bottom: 20px; line-height: 1.6; }
        .btn { display: inline-block; background: #2563eb; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; transition: background 0.2s; }
        .btn:hover { background: #1d4ed8; }
        .success-text { color: #22c55e; font-weight: bold; }
        .error-text { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
<div class='card'>
    <h2>Inisialisasi Database Kartu Pelajar</h2>
    <div class='log-box' id='logBox'>";

try {
    echo "Memulai inisialisasi...<br>";

    // 1. Membuat Tabel Students
    $sql = "CREATE TABLE IF NOT EXISTS `students` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `nis` VARCHAR(50) DEFAULT NULL,
        `nisn` VARCHAR(50) UNIQUE DEFAULT NULL,
        `nama_lengkap` VARCHAR(255) NOT NULL,
        `nomor_ortu` VARCHAR(50) DEFAULT NULL,
        `jenis_kelamin` VARCHAR(50) DEFAULT NULL,
        `tempat_tanggal_lahir` VARCHAR(255) DEFAULT NULL,
        `agama` VARCHAR(50) DEFAULT NULL,
        `alamat_lengkap` TEXT DEFAULT NULL,
        `kelas` VARCHAR(50) DEFAULT NULL,
        `photo_path` VARCHAR(255) DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $pdo->exec($sql);
    $pdo->exec("ALTER TABLE `students` MODIFY `nisn` VARCHAR(50) UNIQUE DEFAULT NULL");
    echo "Tabel <span class='success-text'>students</span> berhasil diperiksa/dibuat.<br>";

    // Kosongkan tabel untuk menghindari duplikasi saat inisialisasi ulang
    $pdo->exec("TRUNCATE TABLE `students`");
    echo "Tabel dikosongkan untuk impor ulang data.<br>";

    // 2. Membaca photo_map.json
    $photoMap = [];
    $photoMapPath = 'photo_map.json';
    if (file_exists($photoMapPath)) {
        $jsonContent = file_get_contents($photoMapPath);
        $photoMap = json_decode($jsonContent, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "Berkas <span class='success-text'>photo_map.json</span> berhasil dimuat. Total pemetaan foto: " . count($photoMap) . "<br>";
        } else {
            echo "<span class='error-text'>Peringatan: Gagal membaca photo_map.json (format JSON tidak valid).</span> Fallback ke path default.<br>";
        }
    } else {
        echo "<span class='error-text'>Peringatan: photo_map.json tidak ditemukan.</span> Fallback ke path default.<br>";
    }

    // 3. Membaca FixData.csv dan Menyisipkannya ke Database
    $csvPath = 'FixData.csv';
    if (!file_exists($csvPath)) {
        throw new Exception("Berkas FixData.csv tidak ditemukan di root project!");
    }

    if (($handle = fopen($csvPath, "r")) !== FALSE) {
        // Ambil header
        $headers = fgetcsv($handle, 1000, ",");
        
        // Bersihkan UTF-8 BOM jika ada pada header pertama
        if ($headers && substr($headers[0], 0, 3) == "\xEF\xBB\xBF") {
            $headers[0] = substr($headers[0], 3);
        }

        // Cari index kolom
        // NIS,NISN,Nama Lengkap,Nomor orang tua (wajib diawali 62),Jenis Kelamin,Tempat Tanggal Lahir,Agama,Alamat Lengkap,Kelas
        $colMap = [];
        foreach ($headers as $index => $name) {
            $colMap[trim($name)] = $index;
        }

        $stmt = $pdo->prepare("INSERT INTO `students` (
            `nis`, `nisn`, `nama_lengkap`, `nomor_ortu`, `jenis_kelamin`, 
            `tempat_tanggal_lahir`, `agama`, `alamat_lengkap`, `kelas`, `photo_path`
        ) VALUES (
            :nis, :nisn, :nama_lengkap, :nomor_ortu, :jenis_kelamin,
            :tempat_tanggal_lahir, :agama, :alamat_lengkap, :kelas, :photo_path
        )");

        $insertedCount = 0;
        $rowNumber = 1;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rowNumber++;
            
            // Dapatkan value berdasarkan header map
            $nis = isset($colMap['NIS']) ? trim($data[$colMap['NIS']]) : '';
            $nisn = isset($colMap['NISN']) ? trim($data[$colMap['NISN']]) : '';
            $nama = isset($colMap['Nama Lengkap']) ? trim($data[$colMap['Nama Lengkap']]) : '';
            $nomor_ortu = isset($colMap['Nomor orang tua (wajib diawali 62)']) ? trim($data[$colMap['Nomor orang tua (wajib diawali 62)']]) : '';
            $jk = isset($colMap['Jenis Kelamin']) ? trim($data[$colMap['Jenis Kelamin']]) : '';
            $ttl = isset($colMap['Tempat Tanggal Lahir']) ? trim($data[$colMap['Tempat Tanggal Lahir']]) : '';
            $agama = isset($colMap['Agama']) ? trim($data[$colMap['Agama']]) : '';
            $alamat = isset($colMap['Alamat Lengkap']) ? trim($data[$colMap['Alamat Lengkap']]) : '';
            $kelas = isset($colMap['Kelas']) ? trim($data[$colMap['Kelas']]) : '';

            // Jika NISN kosong, ubah jadi NULL agar bisa masuk kolom UNIQUE
            if (empty($nisn)) {
                $nisn = null;
            }

            // Tentukan photo_path
            $photoPath = '';
            if (isset($photoMap[$nisn])) {
                $photoPath = $photoMap[$nisn];
            } else {
                // Fallback dinamis jika tidak ada di JSON map
                // Menggunakan folder kelas yang di-resolve
                $kelasFolder = 'SUSULAN';
                if (!empty($kelas) && $kelas !== 'Lainnya') {
                    if (preg_match('/(\d+)\.(\d+)/', $kelas, $m)) {
                        $kelasFolder = $m[1] . '.' . (int)$m[2];
                    } elseif (preg_match('/(\d+)/', $kelas, $m2)) {
                        $kelasFolder = $m2[1];
                    }
                }
                $photoPath = "PAS_FOTO_SMPN_1_KEDUNGWARINGIN/{$kelasFolder}/" . strtoupper($nama) . ".webp";
            }

            try {
                $stmt->execute([
                    ':nis' => $nis !== '' ? $nis : null,
                    ':nisn' => $nisn,
                    ':nama_lengkap' => $nama,
                    ':nomor_ortu' => $nomor_ortu !== '' ? $nomor_ortu : null,
                    ':jenis_kelamin' => $jk !== '' ? $jk : null,
                    ':tempat_tanggal_lahir' => $ttl !== '' ? $ttl : null,
                    ':agama' => $agama !== '' ? $agama : null,
                    ':alamat_lengkap' => $alamat !== '' ? $alamat : null,
                    ':kelas' => $kelas !== '' ? $kelas : null,
                    ':photo_path' => $photoPath !== '' ? $photoPath : null
                ]);
                $insertedCount++;
            } catch (PDOException $e) {
                echo "Baris ke-{$rowNumber} (NISN: {$nisn}): <span class='error-text'>Gagal memasukkan data: " . $e->getMessage() . "</span><br>";
            }
        }
        fclose($handle);
        echo "Proses impor selesai! Berhasil mengimpor <span class='success-text'>{$insertedCount}</span> baris data siswa.<br>";
    }

    echo "<br><span class='success-text'>✔ Setup Database Berhasil!</span><br>";

} catch (Exception $e) {
    echo "<br><span class='error-text'>✘ Error: " . $e->getMessage() . "</span><br>";
}

echo "  </div>
    <a href='index.php' class='btn'>Buka Aplikasi Utama</a>
</div>
<script>
    // Auto scroll ke bawah log box
    const logBox = document.getElementById('logBox');
    logBox.scrollTop = logBox.scrollHeight;
</script>
</body>
</html>";
?>
