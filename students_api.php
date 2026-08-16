<?php
/* students_api.php – CRUD API for Student Data */

header("Content-Type: application/json");
require_once 'config.php';

// Menentukan fungsi pengecekan kelengkapan data (sama seperti JS)
function isStudentComplete($s) {
    $noHp = trim($s['nomor_ortu'] ?? '');
    $ttl = trim($s['tempat_tanggal_lahir'] ?? '');
    $alamat = trim($s['alamat_lengkap'] ?? '');
    return $noHp !== '' && $ttl !== '' && $alamat !== '';
}

// Fungsi resolve folder kelas untuk unggahan foto
function resolveClassFolder($kelas) {
    if (!$kelas || $kelas === 'Lainnya') return 'SUSULAN';
    if (preg_match('/(\d+)\.(\d+)/', $kelas, $m)) {
        return $m[1] . '.' . (int)$m[2];
    }
    if (preg_match('/(\d+)/', $kelas, $m2)) {
        return $m2[1];
    }
    return 'SUSULAN';
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Ambil parameter filter
            $search = trim($_GET['search'] ?? '');
            $kelas = trim($_GET['kelas'] ?? '');
            
            // Build Query
            $query = "SELECT * FROM `students` WHERE 1=1";
            $params = [];
            
            // Filter Pencarian
            if ($search !== '') {
                $query .= " AND (`nama_lengkap` LIKE :search_nama OR `nisn` LIKE :search_nisn OR `nis` LIKE :search_nis)";
                $params[':search_nama'] = '%' . $search . '%';
                $params[':search_nisn'] = '%' . $search . '%';
                $params[':search_nis'] = '%' . $search . '%';
            }
            
            // Filter Kelas & Kelengkapan Data
            if ($kelas === '__TIDAK_LENGKAP__') {
                // Tampilkan yang data kontaknya kurang
                $query .= " AND (nomor_ortu IS NULL OR nomor_ortu = '' 
                             OR tempat_tanggal_lahir IS NULL OR tempat_tanggal_lahir = '' 
                             OR alamat_lengkap IS NULL OR alamat_lengkap = '')";
            } elseif ($kelas !== '') {
                // Filter kelas tertentu, hanya yang LENGKAP
                $query .= " AND `kelas` = :kelas 
                            AND (nomor_ortu IS NOT NULL AND nomor_ortu != '')
                            AND (tempat_tanggal_lahir IS NOT NULL AND tempat_tanggal_lahir != '')
                            AND (alamat_lengkap IS NOT NULL AND alamat_lengkap != '')";
                $params[':kelas'] = $kelas;
            } else {
                // Semua kelas, hanya yang LENGKAP
                $query .= " AND (nomor_ortu IS NOT NULL AND nomor_ortu != '')
                            AND (tempat_tanggal_lahir IS NOT NULL AND tempat_tanggal_lahir != '')
                            AND (alamat_lengkap IS NOT NULL AND alamat_lengkap != '')";
            }
            
            $query .= " ORDER BY `nama_lengkap` ASC";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $students = $stmt->fetchAll();
            
            // Dapatkan statistik total dan tidak lengkap untuk sidebar stats
            // 1. Total Siswa
            $totalSiswa = $pdo->query("SELECT COUNT(*) FROM `students`")->fetchColumn();
            
            // 2. Total Incomplete Siswa
            $totalIncomplete = $pdo->query("SELECT COUNT(*) FROM `students` WHERE 
                nomor_ortu IS NULL OR nomor_ortu = '' 
                OR tempat_tanggal_lahir IS NULL OR tempat_tanggal_lahir = '' 
                OR alamat_lengkap IS NULL OR alamat_lengkap = ''
                OR nisn IS NULL OR nisn = ''
                OR photo_path IS NULL OR photo_path = ''")->fetchColumn();
                
            echo json_encode([
                'success' => true,
                'data' => $students,
                'stats' => [
                    'total' => (int)$totalSiswa,
                    'incomplete' => (int)$totalIncomplete,
                ]
            ]);
            break;

        case 'POST':
            // Tambah / Edit Siswa
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            if ($id === 0) {
                throw new Exception("Fitur menambah siswa baru dinonaktifkan.");
            }
            $nis = trim($_POST['nis'] ?? '');
            $nisn = trim($_POST['nisn'] ?? '');
            $nama = trim($_POST['nama_lengkap'] ?? '');
            $nomor_ortu = trim($_POST['nomor_ortu'] ?? '');
            $jk = trim($_POST['jenis_kelamin'] ?? '');
            $ttl = trim($_POST['tempat_tanggal_lahir'] ?? '');
            $agama = trim($_POST['agama'] ?? '');
            $alamat = trim($_POST['alamat_lengkap'] ?? '');
            $kelas = trim($_POST['kelas'] ?? '');
            
            if ($nisn === '') {
                $nisn = null;
            }
            if ($nama === '') {
                throw new Exception("Nama Lengkap wajib diisi.");
            }
            
            // Periksa apakah NISN duplikat (khusus jika NISN tidak kosong)
            if ($nisn !== null) {
                if ($id === 0) {
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `students` WHERE `nisn` = ?");
                    $checkStmt->execute([$nisn]);
                    if ($checkStmt->fetchColumn() > 0) {
                        throw new Exception("NISN '{$nisn}' sudah digunakan oleh siswa lain.");
                    }
                } else {
                    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM `students` WHERE `nisn` = ? AND `id` != ?");
                    $checkStmt->execute([$nisn, $id]);
                    if ($checkStmt->fetchColumn() > 0) {
                        throw new Exception("NISN '{$nisn}' sudah digunakan oleh siswa lain.");
                    }
                }
            }
            
            // Dapatkan path foto lama (jika edit)
            $photoPath = '';
            if ($id > 0) {
                $oldStmt = $pdo->prepare("SELECT `photo_path` FROM `students` WHERE `id` = ?");
                $oldStmt->execute([$id]);
                $photoPath = $oldStmt->fetchColumn() ?: '';
            }
            
            // Tangani Unggahan Foto
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['photo']['tmp_name'];
                $fileName = $_FILES['photo']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                
                // Izinkan ekstensi jpg/jpeg/png/webp
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                if (!in_array($fileExtension, $allowedExtensions)) {
                    throw new Exception("Format foto tidak valid. Gunakan JPG, JPEG, PNG, atau WEBP.");
                }
                
                // Kelompokkan folder kelas
                $kelasFolder = resolveClassFolder($kelas);
                $uploadDir = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN/' . $kelasFolder;
                
                // Buat folder jika belum ada
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Simpan foto dengan nama siswa berhuruf kapital agar rapi dan selalu berakhiran .webp
                $cleanNama = preg_replace('/[^A-Z0-9]/i', '_', strtoupper($nama));
                $newFileName = $cleanNama . '_' . $nisn . '.webp';
                $destPath = $uploadDir . '/' . $newFileName;
                
                // Konversi gambar ke webp jika bukan format webp asli
                if ($fileExtension === 'webp') {
                    if (!move_uploaded_file($fileTmpPath, $destPath)) {
                        throw new Exception("Gagal menyimpan berkas foto WebP yang diunggah.");
                    }
                } else {
                    // Konversi ke WebP
                    $info = getimagesize($fileTmpPath);
                    if ($info === false) {
                        throw new Exception("File yang diunggah bukan gambar valid.");
                    }
                    $mime = $info['mime'];
                    switch ($mime) {
                        case 'image/jpeg':
                            $image = imagecreatefromjpeg($fileTmpPath);
                            break;
                        case 'image/png':
                            $image = imagecreatefrompng($fileTmpPath);
                            imagepalettetotruecolor($image);
                            imagealphablending($image, true);
                            imagesavealpha($image, true);
                            break;
                        default:
                            throw new Exception("Tipe gambar tidak didukung untuk konversi.");
                    }
                    
                    if (!$image || !imagewebp($image, $destPath, 85)) {
                        if ($image) imagedestroy($image);
                        throw new Exception("Gagal mengonversi foto yang diunggah ke format WebP.");
                    }
                    imagedestroy($image);
                }
                
                $photoPath = $destPath;
            }
            
            // Jika foto kosong dan ini adalah data baru, kita tentukan fallback path
            if ($photoPath === '' && $id === 0) {
                $kelasFolder = resolveClassFolder($kelas);
                $photoPath = "PAS_FOTO_SMPN_1_KEDUNGWARINGIN/{$kelasFolder}/" . strtoupper($nama) . ".webp";
            }
            
            if ($id === 0) {
                // INSERT
                $stmt = $pdo->prepare("INSERT INTO `students` (
                    `nis`, `nisn`, `nama_lengkap`, `nomor_ortu`, `jenis_kelamin`, 
                    `tempat_tanggal_lahir`, `agama`, `alamat_lengkap`, `kelas`, `photo_path`
                ) VALUES (
                    :nis, :nisn, :nama_lengkap, :nomor_ortu, :jenis_kelamin,
                    :tempat_tanggal_lahir, :agama, :alamat_lengkap, :kelas, :photo_path
                )");
            } else {
                // UPDATE
                $stmt = $pdo->prepare("UPDATE `students` SET 
                    `nis` = :nis, 
                    `nisn` = :nisn, 
                    `nama_lengkap` = :nama_lengkap, 
                    `nomor_ortu` = :nomor_ortu, 
                    `jenis_kelamin` = :jenis_kelamin, 
                    `tempat_tanggal_lahir` = :tempat_tanggal_lahir, 
                    `agama` = :agama, 
                    `alamat_lengkap` = :alamat_lengkap, 
                    `kelas` = :kelas, 
                    `photo_path` = :photo_path
                    WHERE `id` = :id");
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            }
            
            $stmt->bindValue(':nis', $nis !== '' ? $nis : null);
            $stmt->bindValue(':nisn', $nisn);
            $stmt->bindValue(':nama_lengkap', $nama);
            $stmt->bindValue(':nomor_ortu', $nomor_ortu !== '' ? $nomor_ortu : null);
            $stmt->bindValue(':jenis_kelamin', $jk !== '' ? $jk : null);
            $stmt->bindValue(':tempat_tanggal_lahir', $ttl !== '' ? $ttl : null);
            $stmt->bindValue(':agama', $agama !== '' ? $agama : null);
            $stmt->bindValue(':alamat_lengkap', $alamat !== '' ? $alamat : null);
            $stmt->bindValue(':kelas', $kelas !== '' ? $kelas : null);
            $stmt->bindValue(':photo_path', $photoPath !== '' ? $photoPath : null);
            
            $stmt->execute();
            
            echo json_encode([
                'success' => true,
                'message' => $id === 0 ? "Data siswa berhasil ditambahkan." : "Data siswa berhasil diperbarui."
            ]);
            break;

        case 'DELETE':
            // Ambil ID dari raw input karena DELETE tidak mengirim format standard post
            $input = json_decode(file_get_contents("php://input"), true);
            $id = isset($input['id']) ? (int)$input['id'] : 0;
            
            if ($id <= 0) {
                throw new Exception("ID siswa tidak valid.");
            }
            
            // Hapus data
            $stmt = $pdo->prepare("DELETE FROM `students` WHERE `id` = ?");
            $stmt->execute([$id]);
            
            echo json_encode([
                'success' => true,
                'message' => "Siswa berhasil dihapus."
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Metode HTTP tidak didukung.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
