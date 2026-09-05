<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Student;

$students = Student::orderBy('kelas')->orderBy('nama_lengkap')->get();
echo "Total data siswa: " . $students->count() . PHP_EOL;

$csvPath = __DIR__ . '/public/data_siswa_smpn1kdw.csv';
$csvFile = fopen($csvPath, 'w');

// UTF-8 BOM agar Excel dapat membuka karakter khusus/Indonesia dengan sempurna
fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));

// Header yang diminta: Nama, NISN, Jenis Kelamin, Tanggal Lahir, Agama, Nama Ayah, Nama Ibu, No HP, Kelas, Alamat
fputcsv($csvFile, [
    'Nama',
    'NISN',
    'Jenis Kelamin',
    'Tanggal Lahir',
    'Agama',
    'Nama Ayah',
    'Nama Ibu',
    'No HP',
    'Kelas',
    'Alamat'
]);

foreach ($students as $s) {
    fputcsv($csvFile, [
        $s->nama_lengkap ?? '',
        $s->nisn ? "'" . $s->nisn : '', // tambahkan kutip awal agar Excel tidak memformat NISN sebagai saintifik numerik
        $s->jenis_kelamin ?? '',
        $s->tempat_tanggal_lahir ?? '',
        $s->agama ?? '',
        '', // Nama Ayah (kosong jika tidak ada)
        '', // Nama Ibu (kosong jika tidak ada)
        $s->nomor_ortu ? "'" . $s->nomor_ortu : '', // tambahkan kutip awal agar nomor HP diawali 0/62 tidak hilang di Excel
        $s->kelas ?? '',
        $s->alamat_lengkap ?? ''
    ]);
}

fclose($csvFile);

// Salin juga ke direktori artifacts agar bisa diunduh langsung
$artifactPath = 'C:/Users/muham/.gemini/antigravity/brain/1fe16705-885f-4f06-8d1c-6504ba231949/data_siswa_smpn1kdw.csv';
copy($csvPath, $artifactPath);

echo "File CSV berhasil diekspor ke: " . $csvPath . PHP_EOL;
