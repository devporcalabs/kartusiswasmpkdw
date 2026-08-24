<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nisn',
        'nama_lengkap',
        'nomor_ortu',
        'jenis_kelamin',
        'tempat_tanggal_lahir',
        'agama',
        'alamat_lengkap',
        'kelas',
        'photo_path'
    ];

    /**
     * Tentukan apakah data siswa lengkap (Nama, NISN, NIS, TTL, Jenis Kelamin, dan Alamat terisi).
     */
    public function isComplete(): bool
    {
        $nis = trim($this->nis ?? '');
        $nisn = trim($this->nisn ?? '');
        $nama = trim($this->nama_lengkap ?? '');
        $ttl = trim($this->tempat_tanggal_lahir ?? '');
        $jk = trim($this->jenis_kelamin ?? '');
        $alamat = trim($this->alamat_lengkap ?? '');

        return $nis !== '' && $nisn !== '' && $nama !== '' && $ttl !== '' && $jk !== '' && $alamat !== '';
    }

    /**
     * Tentukan apakah siswa memiliki berkas foto fisik yang valid.
     */
    public function hasPhoto(): bool
    {
        if (empty($this->photo_path)) {
            return false;
        }

        return file_exists(public_path($this->photo_path));
    }
}
