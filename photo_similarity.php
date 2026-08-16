<?php
/* photo_similarity.php – Asisten Pencocokan Foto Siswa */
require_once 'config.php';

// Handle AJAX Approval Action
if (isset($_GET['action']) && $_GET['action'] === 'approve') {
    header('Content-Type: application/json');
    try {
        $nisn = trim($_POST['nisn'] ?? '');
        $photoPath = trim($_POST['photo_path'] ?? '');

        if ($nisn === '' || $photoPath === '') {
            throw new Exception("NISN atau Jalur Foto tidak valid.");
        }

        // 1. Update photo_map.json
        $photoMapFile = 'photo_map.json';
        $photoMap = [];
        if (file_exists($photoMapFile)) {
            $photoMap = json_decode(file_get_contents($photoMapFile), true) ?: [];
        }
        $photoMap[$nisn] = $photoPath;
        file_put_contents($photoMapFile, json_encode($photoMap, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // 2. Update Database MySQL
        $stmt = $pdo->prepare("UPDATE `students` SET `photo_path` = ? WHERE `nisn` = ?");
        $stmt->execute([$photoPath, $nisn]);

        echo json_encode(['success' => true, 'message' => 'Foto berhasil disetujui dan diperbarui.']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// 1. Scan all webp photo files recursively
$photoDir = 'PAS_FOTO_SMPN_1_KEDUNGWARINGIN';
$allPhotos = [];
if (is_dir($photoDir)) {
    $di = new RecursiveDirectoryIterator($photoDir);
    foreach (new RecursiveIteratorIterator($di) as $filename => $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'webp') {
            // Store relative path with forward slashes
            $allPhotos[] = str_replace('\\', '/', $filename);
        }
    }
}

// 2. Get students without photos from database
$students = $pdo->query("SELECT * FROM `students` WHERE `photo_path` IS NULL OR `photo_path` = '' ORDER BY `kelas` ASC, `nama_lengkap` ASC")->fetchAll();

// 3. Prepare recommendations list
$data = [];
foreach ($students as $s) {
    $name = $s['nama_lengkap'];
    $nisn = $s['nisn'];
    $class = $s['kelas'];

    $recommendations = [];
    foreach ($allPhotos as $photoPath) {
        $filename = pathinfo($photoPath, PATHINFO_FILENAME);
        
        // Clean strings for similarity computation
        $cleanName = preg_replace('/[^A-Z0-9]/', '', strtoupper($name));
        $cleanFile = preg_replace('/[^A-Z0-9]/', '', strtoupper($filename));

        similar_text($cleanName, $cleanFile, $percent);
        
        $recommendations[] = [
            'path' => $photoPath,
            'filename' => basename($photoPath),
            'percent' => round($percent, 1)
        ];
    }

    // Sort by similarity descending
    usort($recommendations, function($a, $b) {
        return $b['percent'] <=> $a['percent'];
    });

    $data[] = [
        'student' => $s,
        'recommendations' => array_slice($recommendations, 0, 3)
    ];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Asisten Pencocokan Foto Siswa – SMPN 1 Kedungwaringin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
      --glass-bg: rgba(30, 41, 59, 0.7);
      --glass-border: rgba(255, 255, 255, 0.08);
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --primary: #6366f1;
      --primary-hover: #4f46e5;
      --success: #10b981;
      --warning: #f59e0b;
      --danger: #ef4444;
      --card-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg-gradient);
      color: var(--text-main);
      min-height: 100vh;
      padding: 40px 20px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      border-bottom: 1px solid var(--glass-border);
      padding-bottom: 20px;
    }

    h1 {
      font-size: 24px;
      font-weight: 700;
      background: linear-gradient(to right, #818cf8, #e0e7ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--text-main);
      padding: 10px 20px;
      border-radius: 8px;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: rgba(255, 255, 255, 0.2);
    }

    .btn-primary {
      background: var(--primary);
      border-color: var(--primary);
    }

    .btn-primary:hover {
      background: var(--primary-hover);
      border-color: var(--primary-hover);
    }

    .stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-box {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      box-shadow: var(--card-shadow);
      backdrop-filter: blur(12px);
    }

    .stat-val {
      font-size: 32px;
      font-weight: 800;
      color: var(--primary);
      margin-bottom: 5px;
    }

    .stat-lbl {
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 500;
    }

    .search-section {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: 12px;
      padding: 15px 20px;
      margin-bottom: 20px;
      display: flex;
      gap: 15px;
      backdrop-filter: blur(12px);
    }

    .search-input {
      flex: 1;
      background: rgba(15, 23, 42, 0.6);
      border: 1px solid var(--glass-border);
      border-radius: 8px;
      padding: 10px 15px;
      color: var(--text-main);
      font-family: inherit;
      font-size: 14px;
      outline: none;
    }

    .search-input:focus {
      border-color: var(--primary);
    }

    .student-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .student-row {
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      padding: 20px;
      display: grid;
      grid-template-columns: 300px 1fr;
      gap: 30px;
      box-shadow: var(--card-shadow);
      backdrop-filter: blur(12px);
      transition: all 0.3s ease;
    }

    .student-row.approved {
      opacity: 0.15;
      transform: scale(0.97);
      pointer-events: none;
    }

    .student-info {
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .student-name {
      font-size: 18px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 8px;
    }

    .student-meta {
      font-size: 13px;
      color: var(--text-muted);
      line-height: 1.6;
    }

    .student-meta strong {
      color: var(--text-main);
    }

    .badge-class {
      display: inline-block;
      background: rgba(99, 102, 241, 0.15);
      color: #a5b4fc;
      padding: 3px 8px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
      margin-top: 5px;
      width: fit-content;
    }

    .recommendations-title {
      font-size: 13px;
      color: var(--text-muted);
      font-weight: 600;
      text-transform: uppercase;
      margin-bottom: 12px;
      letter-spacing: 0.05em;
    }

    .recommendations-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 15px;
    }

    .rec-card {
      background: rgba(15, 23, 42, 0.4);
      border: 1px solid rgba(255, 255, 255, 0.05);
      border-radius: 12px;
      padding: 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transition: all 0.2s ease;
      position: relative;
    }

    .rec-card:hover {
      border-color: rgba(255, 255, 255, 0.15);
      background: rgba(15, 23, 42, 0.6);
    }

    .photo-preview {
      width: 70px;
      height: 90px;
      border-radius: 6px;
      object-fit: cover;
      background: #0f172a;
      border: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 10px;
    }

    .rec-filename {
      font-size: 12px;
      font-weight: 600;
      color: var(--text-main);
      word-break: break-all;
      margin-bottom: 8px;
      max-height: 36px;
      overflow: hidden;
    }

    .similarity-bar-container {
      width: 100%;
      background: rgba(255, 255, 255, 0.05);
      height: 6px;
      border-radius: 3px;
      margin-bottom: 12px;
      overflow: hidden;
    }

    .similarity-bar {
      height: 100%;
      border-radius: 3px;
      transition: width 0.5s ease;
    }

    .similarity-info {
      font-size: 11px;
      font-weight: 700;
      margin-bottom: 12px;
    }

    .acc-btn {
      width: 100%;
      background: var(--success);
      border: none;
      color: #fff;
      padding: 8px;
      border-radius: 6px;
      font-weight: 700;
      font-size: 12px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      transition: background 0.2s ease;
    }

    .acc-btn:hover {
      background: #059669;
    }

    /* Similarity Colors */
    .color-high { background: var(--success); color: #34d399; }
    .color-med { background: var(--warning); color: #fbbf24; }
    .color-low { background: var(--danger); color: #f87171; }

    .empty-state {
      text-align: center;
      padding: 60px;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      color: var(--text-muted);
    }
  </style>
</head>
<body>

<div class="container">
  <header>
    <div>
      <h1>Asisten Pencocokan Foto Siswa</h1>
      <p style="color: var(--text-muted); font-size: 14px; margin-top: 5px;">Mencocokkan nama siswa dengan nama berkas foto menggunakan persentase kemiripan.</p>
    </div>
    <a href="index.php" class="btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
      Kembali ke Dashboard
    </a>
  </header>

  <div class="stats-row">
    <div class="stat-box">
      <div class="stat-val"><?= count($students) ?></div>
      <div class="stat-lbl">Siswa Belum Punya Foto</div>
    </div>
    <div class="stat-box">
      <div class="stat-val"><?= count($allPhotos) ?></div>
      <div class="stat-lbl">Total Berkas Foto di Server</div>
    </div>
    <div class="stat-box">
      <div class="stat-val"><?= count($students) > 0 ? 'Aktif' : 'Selesai' ?></div>
      <div class="stat-lbl">Status Asisten Pencocokan</div>
    </div>
  </div>

  <div class="search-section">
    <input type="text" id="filterInput" class="search-input" placeholder="Saring siswa berdasarkan nama atau kelas..." onkeyup="filterList()" />
  </div>

  <?php if (count($data) === 0): ?>
    <div class="empty-state">
      <h2>🎉 Semua Siswa Sudah Memiliki Foto!</h2>
      <p style="margin-top: 10px;">Tidak ada lagi siswa yang membutuhkan pencocokan foto.</p>
    </div>
  <?php else: ?>
    <div class="student-list" id="studentList">
      <?php foreach ($data as $item): ?>
        <?php 
        $s = $item['student']; 
        $recs = $item['recommendations'];
        ?>
        <div class="student-row" data-name="<?= htmlspecialchars(strtolower($s['nama_lengkap'])) ?>" data-class="<?= htmlspecialchars(strtolower($s['kelas'])) ?>">
          <div class="student-info">
            <div class="student-name"><?= htmlspecialchars(strtoupper($s['nama_lengkap'])) ?></div>
            <div class="student-meta">
              NISN: <strong><?= htmlspecialchars($s['nisn'] ?: '-') ?></strong><br>
              NIS: <strong><?= htmlspecialchars($s['nis'] ?: '-') ?></strong><br>
              TTL: <strong><?= htmlspecialchars($s['tempat_tanggal_lahir'] ?: '-') ?></strong>
            </div>
            <div class="badge-class"><?= htmlspecialchars($s['kelas']) ?></div>
          </div>
          <div>
            <div class="recommendations-title">Rekomendasi Foto Berdasarkan Persentase Kemiripan</div>
            <div class="recommendations-grid">
              <?php foreach ($recs as $r): ?>
                <?php
                // Choose color class based on percent
                $colorClass = 'color-low';
                if ($r['percent'] >= 75) {
                    $colorClass = 'color-high';
                } elseif ($r['percent'] >= 50) {
                    $colorClass = 'color-med';
                }
                ?>
                <div class="rec-card">
                  <img src="<?= htmlspecialchars($r['path']) ?>" alt="Preview" class="photo-preview" onerror="this.src='LogoSMPN1KDW.webp';">
                  <div class="rec-filename" title="<?= htmlspecialchars($r['filename']) ?>"><?= htmlspecialchars($r['filename']) ?></div>
                  
                  <div class="similarity-bar-container">
                    <div class="similarity-bar <?= $colorClass ?>" style="width: <?= $r['percent'] ?>%"></div>
                  </div>
                  <div class="similarity-info <?= $colorClass ?>-text">Kemiripan: <?= $r['percent'] ?>%</div>
                  
                  <button class="acc-btn" onclick="approvePhoto(this, '<?= htmlspecialchars($s['nisn']) ?>', '<?= htmlspecialchars($r['path']) ?>')">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    Setujui (ACC)
                  </button>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script>
function filterList() {
  const query = document.getElementById('filterInput').value.toLowerCase();
  const rows = document.querySelectorAll('.student-row');
  
  rows.forEach(row => {
    const name = row.getAttribute('data-name');
    const className = row.getAttribute('data-class');
    if (name.includes(query) || className.includes(query)) {
      row.style.display = 'grid';
    } else {
      row.style.display = 'none';
    }
  });
}

function approvePhoto(btn, nisn, photoPath) {
  if (!confirm("Apakah Anda yakin ingin menyetujui foto ini?")) return;

  btn.disabled = true;
  btn.innerHTML = 'Memproses...';

  const formData = new FormData();
  formData.append('nisn', nisn);
  formData.append('photo_path', photoPath);

  fetch('photo_similarity.php?action=approve', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      // Fade out the row
      const row = btn.closest('.student-row');
      row.classList.add('approved');
      setTimeout(() => {
        row.style.display = 'none';
      }, 500);
    } else {
      alert("Error: " + res.message);
      btn.disabled = false;
      btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Setujui (ACC)';
    }
  })
  .catch(err => {
    console.error(err);
    alert("Koneksi gagal: " + err.message);
    btn.disabled = false;
    btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Setujui (ACC)';
  });
}
</script>
</body>
</html>
