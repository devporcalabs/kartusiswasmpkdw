<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class StudentApiController extends Controller
{
    /**
     * Menangani seluruh request dari students_api.php (GET, POST, DELETE).
     */
    public function handle(Request $request)
    {
        $method = $request->method();

        try {
            switch ($method) {
                case 'GET':
                    return $this->handleGet($request);
                case 'POST':
                    return $this->handlePost($request);
                case 'DELETE':
                    return $this->handleDelete($request);
                default:
                    return response()->json(['success' => false, 'message' => 'Metode HTTP tidak didukung.'], 405);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function handleGet(Request $request)
    {
        $search = trim($request->input('search', ''));
        $kelas = trim($request->input('kelas', ''));

        $query = Student::query();

        // Filter Pencarian
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Definisi kondisi kelengkapan data
        $condDataComplete = function ($q) {
            $q->whereNotNull('tempat_tanggal_lahir')->where('tempat_tanggal_lahir', '!=', '')
              ->whereNotNull('alamat_lengkap')->where('alamat_lengkap', '!=', '')
              ->whereNotNull('nisn')->where('nisn', '!=', '');
        };

        $condDataIncomplete = function ($q) {
            $q->whereNull('tempat_tanggal_lahir')->orWhere('tempat_tanggal_lahir', '')
              ->orWhereNull('alamat_lengkap')->orWhere('alamat_lengkap', '')
              ->orWhereNull('nisn')->orWhere('nisn', '');
        };

        $condHasPhoto = function ($q) {
            $q->whereNotNull('photo_path')->where('photo_path', '!=', '');
        };

        $condNoPhoto = function ($q) {
            $q->whereNull('photo_path')->orWhere('photo_path', '');
        };

        // Filter Kelas & Kelengkapan Data
        if ($kelas === '__NO_PHOTO_DATA_COMPLETE__') {
            $query->where($condNoPhoto)->where($condDataComplete);
        } elseif ($kelas === '__HAS_PHOTO_DATA_INCOMPLETE__') {
            $query->where($condHasPhoto)->where($condDataIncomplete);
        } elseif ($kelas === '__NO_PHOTO_DATA_INCOMPLETE__') {
            $query->where($condNoPhoto)->where($condDataIncomplete);
        } elseif ($kelas !== '') {
            $query->where('kelas', $kelas)
                  ->where($condHasPhoto)
                  ->where($condDataComplete);
        } else {
            // Semua kelas, hanya yang LENGKAP
            $query->where($condHasPhoto)->where($condDataComplete);
        }

        $students = $query->orderBy('nama_lengkap', 'asc')->get();

        // Hitung statistik untuk sidebar stats
        $totalSiswa = Student::count();

        // 1. Foto (-) , Data (+)
        $c1 = Student::where($condNoPhoto)->where($condDataComplete)->count();

        // 2. Foto (+) , Data (-)
        $c2 = Student::where($condHasPhoto)->where($condDataIncomplete)->count();

        // 3. Foto (-) , Data (-)
        $c3 = Student::where($condNoPhoto)->where($condDataIncomplete)->count();

        $totalIncomplete = $c1 + $c2 + $c3;

        return response()->json([
            'success' => true,
            'data' => $students,
            'stats' => [
                'total' => $totalSiswa,
                'incomplete' => $totalIncomplete,
                'no_photo_data_complete' => $c1,
                'has_photo_data_incomplete' => $c2,
                'no_photo_data_incomplete' => $c3
            ]
        ]);
    }

    private function handlePost(Request $request)
    {
        $id = (int)$request->input('id', 0);
        $nis = trim($request->input('nis', ''));
        $nisn = trim($request->input('nisn', ''));
        $nama = trim($request->input('nama_lengkap', ''));
        $nomor_ortu = trim($request->input('nomor_ortu', ''));
        $jk = trim($request->input('jenis_kelamin', ''));
        $ttl = trim($request->input('tempat_tanggal_lahir', ''));
        $agama = trim($request->input('agama', ''));
        $alamat = trim($request->input('alamat_lengkap', ''));
        $kelas = trim($request->input('kelas', ''));

        if ($nisn === '') {
            $nisn = null;
        }
        if ($nama === '') {
            throw new \Exception("Nama Lengkap wajib diisi.");
        }

        // Cek duplikasi NISN
        if ($nisn !== null) {
            $dupQuery = Student::where('nisn', $nisn);
            if ($id > 0) {
                $dupQuery->where('id', '!=', $id);
            }
            if ($dupQuery->exists()) {
                throw new \Exception("NISN '{$nisn}' sudah digunakan oleh siswa lain.");
            }
        }

        $student = $id > 0 ? Student::find($id) : new Student();
        if ($id > 0 && !$student) {
            throw new \Exception("Data siswa tidak ditemukan.");
        }

        // Tentukan folder tujuan foto berdasarkan kelas
        $resolveClassFolder = function ($kelas) {
            if (!$kelas || $kelas === 'Lainnya') return 'SUSULAN';
            if (preg_match('/(\d+)\.(\d+)/', $kelas, $m)) {
                return $m[1] . '.' . (int)$m[2];
            }
            if (preg_match('/(\d+)/', $kelas, $m2)) {
                return $m2[1];
            }
            return 'SUSULAN';
        };

        $photoPath = $student->photo_path ?? '';

        // Tangani upload berkas foto
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $extension = strtolower($file->getClientOriginalExtension());

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array($extension, $allowed)) {
                throw new \Exception("Format foto tidak valid. Gunakan JPG, JPEG, PNG, atau WEBP.");
            }

            $kelasFolder = $resolveClassFolder($kelas);
            $uploadDir = public_path('PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder);

            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $cleanNama = preg_replace('/[^A-Z0-9]/i', '_', strtoupper($nama));
            $newFileName = $cleanNama . '_' . ($nisn ?? 'TANPA_NISN') . '.webp';
            $destPath = $uploadDir . '/' . $newFileName;
            $relativeDestPath = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder . '/' . $newFileName;

            // Jika formatnya sudah webp, pindahkan langsung
            if ($extension === 'webp') {
                $file->move($uploadDir, $newFileName);
            } else {
                // Konversi gambar ke format WebP menggunakan GD
                $tempPath = $file->getRealPath();
                $info = getimagesize($tempPath);
                if ($info === false) {
                    throw new \Exception("Berkas yang diunggah bukan gambar valid.");
                }

                $mime = $info['mime'];
                switch ($mime) {
                    case 'image/jpeg':
                        $image = imagecreatefromjpeg($tempPath);
                        break;
                    case 'image/png':
                        $image = imagecreatefrompng($tempPath);
                        imagepalettetotruecolor($image);
                        imagealphablending($image, true);
                        imagesavealpha($image, true);
                        break;
                    default:
                        throw new \Exception("Tipe gambar tidak didukung untuk konversi.");
                }

                if (!$image || !imagewebp($image, $destPath, 85)) {
                    if ($image) imagedestroy($image);
                    throw new \Exception("Gagal mengonversi foto yang diunggah ke format WebP.");
                }
                imagedestroy($image);
            }

            $photoPath = $relativeDestPath;
        }

        // Tentukan fallback photo path jika baru dan foto kosong
        if (empty($photoPath)) {
            $kelasFolder = $resolveClassFolder($kelas);
            $photoPath = "PAS_FOTO_SMPN_1_KEDUNGWARINGIN/{$kelasFolder}/" . strtoupper($nama) . ".webp";
        }

        // Isi data
        $student->nis = !empty($nis) ? $nis : null;
        $student->nisn = $nisn;
        $student->nama_lengkap = $nama;
        $student->nomor_ortu = !empty($nomor_ortu) ? $nomor_ortu : null;
        $student->jenis_kelamin = !empty($jk) ? $jk : null;
        $student->tempat_tanggal_lahir = !empty($ttl) ? $ttl : null;
        $student->agama = !empty($agama) ? $agama : null;
        $student->alamat_lengkap = !empty($alamat) ? $alamat : null;
        $student->kelas = !empty($kelas) ? $kelas : null;
        $student->photo_path = $photoPath;
        $student->save();

        return response()->json([
            'success' => true,
            'message' => $id === 0 ? "Data siswa berhasil ditambahkan." : "Data siswa berhasil diperbarui."
        ]);
    }

    private function handleDelete(Request $request)
    {
        $id = (int)$request->input('id', 0);
        if ($id <= 0) {
            throw new \Exception("ID siswa tidak valid.");
        }

        $student = Student::find($id);
        if (!$student) {
            throw new \Exception("Data siswa tidak ditemukan.");
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => "Siswa berhasil dihapus."
        ]);
    }
}
