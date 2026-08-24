<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminStudentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $kelas = trim($request->input('kelas', ''));
        $status = trim($request->input('status', ''));

        $query = Student::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($kelas !== '') {
            $query->where('kelas', $kelas);
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

        if ($status === 'complete') {
            $query->where($condHasPhoto)->where($condDataComplete);
        } elseif ($status === 'no_photo_data_complete') {
            $query->where($condNoPhoto)->where($condDataComplete);
        } elseif ($status === 'has_photo_data_incomplete') {
            $query->where($condHasPhoto)->where($condDataIncomplete);
        } elseif ($status === 'no_photo_data_incomplete') {
            $query->where($condNoPhoto)->where($condDataIncomplete);
        }

        $students = $query->orderBy('nama_lengkap', 'asc')->paginate(20)->withQueryString();

        $classes = Student::select('kelas')
            ->whereNotNull('kelas')
            ->where('kelas', '!=', '')
            ->distinct()
            ->orderBy('kelas', 'asc')
            ->pluck('kelas');

        return view('admin.students.index', compact('students', 'classes', 'search', 'kelas', 'status'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nis' => 'nullable|string|max:50',
            'nisn' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|string',
            'tempat_tanggal_lahir' => 'nullable|string',
            'agama' => 'nullable|string',
            'alamat_lengkap' => 'nullable|string',
            'kelas' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        $nisn = $request->input('nisn');
        if (!empty($nisn)) {
            // Cek duplikasi NISN
            if (Student::where('nisn', $nisn)->exists()) {
                return back()->withErrors(['nisn' => "NISN '{$nisn}' sudah digunakan oleh siswa lain."])->withInput();
            }
        } else {
            $nisn = null;
        }

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

        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $extension = strtolower($file->getClientOriginalExtension());

            $kelasFolder = $resolveClassFolder($request->input('kelas'));
            $uploadDir = public_path('PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder);

            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $cleanNama = preg_replace('/[^A-Z0-9]/i', '_', strtoupper($request->input('nama_lengkap')));
            $newFileName = $cleanNama . '_' . ($nisn ?? 'TANPA_NISN') . '.webp';
            $destPath = $uploadDir . '/' . $newFileName;
            $relativeDestPath = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder . '/' . $newFileName;

            if ($extension === 'webp') {
                $file->move($uploadDir, $newFileName);
            } else {
                $tempPath = $file->getRealPath();
                $info = getimagesize($tempPath);
                if ($info !== false) {
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
                            $image = null;
                    }
                    if ($image && imagewebp($image, $destPath, 85)) {
                        imagedestroy($image);
                    } else {
                        if ($image) imagedestroy($image);
                        return back()->withErrors(['photo' => 'Gagal mengonversi gambar ke WebP.'])->withInput();
                    }
                }
            }
            $photoPath = $relativeDestPath;
        }

        if (empty($photoPath)) {
            $kelasFolder = $resolveClassFolder($request->input('kelas'));
            $photoPath = "PAS_FOTO_SMPN_1_KEDUNGWARINGIN/{$kelasFolder}/" . strtoupper($request->input('nama_lengkap')) . ".webp";
        }

        Student::create([
            'nis' => $request->input('nis'),
            'nisn' => $nisn,
            'nama_lengkap' => $request->input('nama_lengkap'),
            'nomor_ortu' => $request->input('nomor_ortu'),
            'jenis_kelamin' => $request->input('jenis_kelamin'),
            'tempat_tanggal_lahir' => $request->input('tempat_tanggal_lahir'),
            'agama' => $request->input('agama'),
            'alamat_lengkap' => $request->input('alamat_lengkap'),
            'kelas' => $request->input('kelas'),
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function edit(Student $student)
    {
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nis' => 'nullable|string|max:50',
            'nisn' => 'nullable|string|max:50',
            'jenis_kelamin' => 'nullable|string',
            'tempat_tanggal_lahir' => 'nullable|string',
            'agama' => 'nullable|string',
            'alamat_lengkap' => 'nullable|string',
            'kelas' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        $nisn = $request->input('nisn');
        if (!empty($nisn)) {
            // Cek duplikasi NISN
            if (Student::where('nisn', $nisn)->where('id', '!=', $student->id)->exists()) {
                return back()->withErrors(['nisn' => "NISN '{$nisn}' sudah digunakan oleh siswa lain."])->withInput();
            }
        } else {
            $nisn = null;
        }

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

        $photoPath = $student->photo_path;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $file = $request->file('photo');
            $extension = strtolower($file->getClientOriginalExtension());

            $kelasFolder = $resolveClassFolder($request->input('kelas'));
            $uploadDir = public_path('PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder);

            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true);
            }

            $cleanNama = preg_replace('/[^A-Z0-9]/i', '_', strtoupper($request->input('nama_lengkap')));
            $newFileName = $cleanNama . '_' . ($nisn ?? 'TANPA_NISN') . '.webp';
            $destPath = $uploadDir . '/' . $newFileName;
            $relativeDestPath = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder . '/' . $newFileName;

            if ($extension === 'webp') {
                $file->move($uploadDir, $newFileName);
            } else {
                $tempPath = $file->getRealPath();
                $info = getimagesize($tempPath);
                if ($info !== false) {
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
                            $image = null;
                    }
                    if ($image && imagewebp($image, $destPath, 85)) {
                        imagedestroy($image);
                    } else {
                        if ($image) imagedestroy($image);
                        return back()->withErrors(['photo' => 'Gagal mengonversi gambar ke WebP.'])->withInput();
                    }
                }
            }
            $photoPath = $relativeDestPath;
        }

        $student->update([
            'nis' => $request->input('nis'),
            'nisn' => $nisn,
            'nama_lengkap' => $request->input('nama_lengkap'),
            'nomor_ortu' => $request->input('nomor_ortu'),
            'jenis_kelamin' => $request->input('jenis_kelamin'),
            'tempat_tanggal_lahir' => $request->input('tempat_tanggal_lahir'),
            'agama' => $request->input('agama'),
            'alamat_lengkap' => $request->input('alamat_lengkap'),
            'kelas' => $request->input('kelas'),
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil dihapus.');
    }
}
