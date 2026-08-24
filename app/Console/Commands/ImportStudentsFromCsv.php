<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use Illuminate\Support\Facades\File;

class ImportStudentsFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:import {--backup : Backup and remove CSV/JSON files after import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import students data from FixData.csv and photo_map.json into database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $csvPath = base_path('FixData.csv');
        $jsonPath = base_path('photo_map.json');

        if (!File::exists($csvPath)) {
            $this->error("Berkas FixData.csv tidak ditemukan di root project!");
            return Command::FAILURE;
        }

        $this->info("Memulai inisialisasi impor data...");

        // 1. Membaca photo_map.json
        $photoMap = [];
        if (File::exists($jsonPath)) {
            $jsonContent = File::get($jsonPath);
            $photoMap = json_decode($jsonContent, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $this->info("Berkas photo_map.json berhasil dimuat. Total pemetaan foto: " . count($photoMap));
            } else {
                $this->warn("Peringatan: Gagal membaca photo_map.json (format JSON tidak valid). Fallback ke path default.");
            }
        } else {
            $this->warn("Peringatan: photo_map.json tidak ditemukan. Fallback ke path default.");
        }

        // 2. Kosongkan tabel students
        $this->info("Mengosongkan tabel students untuk impor ulang data...");
        Student::truncate();

        // Helper resolve folder kelas
        $resolveClassFolder = function($kelas) {
            if (!$kelas || $kelas === 'Lainnya') return 'SUSULAN';
            if (preg_match('/(\d+)\.(\d+)/', $kelas, $m)) {
                return $m[1] . '.' . (int)$m[2];
            }
            if (preg_match('/(\d+)/', $kelas, $m2)) {
                return $m2[1];
            }
            return 'SUSULAN';
        };

        // 3. Impor data dari CSV
        if (($handle = fopen($csvPath, "r")) !== FALSE) {
            $headers = fgetcsv($handle, 1000, ",");
            
            // Bersihkan UTF-8 BOM jika ada
            if ($headers && substr($headers[0], 0, 3) == "\xEF\xBB\xBF") {
                $headers[0] = substr($headers[0], 3);
            }

            // Map header column names to indexes
            $colMap = [];
            foreach ($headers as $index => $name) {
                $colMap[strtolower(trim($name))] = $index;
            }

            $getCol = function($keys, $data) use ($colMap) {
                foreach ($keys as $k) {
                    $lk = strtolower(trim($k));
                    if (isset($colMap[$lk])) {
                        return trim($data[$colMap[$lk]]);
                    }
                }
                return '';
            };

            $insertedCount = 0;
            $rowNumber = 1;

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (empty($data) || count($data) < 2) continue;
                $rowNumber++;

                $nis = $getCol(['nis', 'NIS'], $data);
                $nisn = $getCol(['nisn', 'NISN'], $data);
                $nama = $getCol(['nama_lengkap', 'Nama Lengkap'], $data);
                $nomor_ortu = $getCol(['nomor_ortu', 'Nomor orang tua (wajib diawali 62)'], $data);
                $jk = $getCol(['jenis_kelamin', 'Jenis Kelamin'], $data);
                $ttl = $getCol(['tempat_tanggal_lahir', 'Tempat Tanggal Lahir'], $data);
                $agama = $getCol(['agama', 'Agama'], $data);
                $alamat = $getCol(['alamat_lengkap', 'Alamat Lengkap'], $data);
                $kelas = $getCol(['kelas', 'Kelas'], $data);

                if (empty($nama)) continue;

                // Tentukan path foto
                $photoPath = '';
                if (!empty($nisn) && isset($photoMap[$nisn])) {
                    $photoPath = $photoMap[$nisn];
                } elseif (isset($photoMap[$nama])) {
                    $photoPath = $photoMap[$nama];
                } else {
                    $kelasFolder = $resolveClassFolder($kelas);
                    $photoPath = "PAS_FOTO_SMPN_1_KEDUNGWARINGIN/{$kelasFolder}/" . strtoupper($nama) . ".webp";
                }

                try {
                    Student::create([
                        'nis' => !empty($nis) ? $nis : null,
                        'nisn' => !empty($nisn) ? $nisn : null,
                        'nama_lengkap' => $nama,
                        'nomor_ortu' => !empty($nomor_ortu) ? $nomor_ortu : null,
                        'jenis_kelamin' => !empty($jk) ? $jk : null,
                        'tempat_tanggal_lahir' => !empty($ttl) ? $ttl : null,
                        'agama' => !empty($agama) ? $agama : null,
                        'alamat_lengkap' => !empty($alamat) ? $alamat : null,
                        'kelas' => !empty($kelas) ? $kelas : null,
                        'photo_path' => !empty($photoPath) ? $photoPath : null,
                    ]);
                    $insertedCount++;
                } catch (\Exception $e) {
                    $this->error("Baris ke-{$rowNumber} (Nama: {$nama}): Gagal memasukkan data: " . $e->getMessage());
                }
            }
            fclose($handle);
            $this->info("✔ Impor selesai! Berhasil mengimpor {$insertedCount} data siswa ke database.");
        }

        // 4. Backup & Hapus File CSV/JSON jika diminta
        if ($this->option('backup')) {
            $backupDir = base_path('_tools_and_raw_data');
            if (!File::isDirectory($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }

            if (File::exists($csvPath)) {
                File::copy($csvPath, $backupDir . '/FixData.csv.bak');
                File::delete($csvPath);
                $this->info("✔ FixData.csv dipindahkan ke _tools_and_raw_data/FixData.csv.bak");
            }
            if (File::exists($jsonPath)) {
                File::copy($jsonPath, $backupDir . '/photo_map.json.bak');
                File::delete($jsonPath);
                $this->info("✔ photo_map.json dipindahkan ke _tools_and_raw_data/photo_map.json.bak");
            }
        }

        return Command::SUCCESS;
    }
}
