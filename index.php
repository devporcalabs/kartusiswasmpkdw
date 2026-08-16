<?php
/* index.php – Kartu Pelajar SMPN 1 Kedungwaringin Dashboard */
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Generator Kartu Pelajar – SMPN 1 Kedungwaringin</title>
  <meta name="description" content="Generator Kartu Pelajar SMPN 1 Kedungwaringin – cetak kartu pelajar siswa dengan QR Code dari NISN." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  
  <!-- QR Code library -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <!-- html2canvas for capturing elements -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <!-- JSZip for packaging files -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  
  <link rel="stylesheet" href="style.css" />
</head>
<body>

<!-- ===== APP SHELL ===== -->
<div class="app-shell">

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <img src="LogoSMPN1KDW.webp" alt="Logo SMPN 1 KDW" class="sidebar-logo" />
      <div>
        <div class="sidebar-title">SMPN 1</div>
        <div class="sidebar-subtitle">Kedungwaringin</div>
      </div>
    </div>

    <div class="sidebar-section">
      <label class="sidebar-label">Cari Siswa</label>
      <div class="search-box">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" id="searchInput" placeholder="Nama atau NISN…" class="search-input" />
      </div>
    </div>

    <div class="sidebar-section">
      <label class="sidebar-label">Filter Kelas</label>
      <select id="kelasFilter" class="filter-select">
        <option value="" disabled selected>-- Pilih Kelas --</option>
        <?php
        try {
            $classes = $pdo->query("SELECT DISTINCT `kelas` FROM `students` WHERE `kelas` IS NOT NULL AND `kelas` != '' ORDER BY `kelas` ASC")->fetchAll(PDO::FETCH_COLUMN);
            foreach ($classes as $cls) {
                echo "<option value='" . htmlspecialchars($cls) . "'>" . htmlspecialchars($cls) . "</option>";
            }
        } catch (PDOException $e) {
            // Jika database belum di-setup, lewati dropdown
        }
        ?>
        <?php
        try {
            $incompleteCount = $pdo->query("SELECT COUNT(*) FROM `students` WHERE nomor_ortu IS NULL OR nomor_ortu = '' OR tempat_tanggal_lahir IS NULL OR tempat_tanggal_lahir = '' OR alamat_lengkap IS NULL OR alamat_lengkap = ''")->fetchColumn();
            if ($incompleteCount > 0) {
                echo "<option disabled>──────────────</option>";
                echo "<option value='__TIDAK_LENGKAP__' style='color: #ef4444; font-weight: bold;'>⚠ Data Tidak Lengkap ($incompleteCount)</option>";
            }
        } catch (PDOException $e) {
            // Lewati jika error
        }
        ?>
      </select>
    </div>

    <!-- Stats -->
    <div class="sidebar-section">
      <div class="stats-grid">
        <div class="stat-card">
          <span class="stat-num" id="statTotal">–</span>
          <span class="stat-lbl">Total Siswa</span>
        </div>
        <div class="stat-card">
          <span class="stat-num" id="statFiltered">–</span>
          <span class="stat-lbl">Ditampilkan</span>
        </div>
        <div class="stat-card stat-card--warning">
          <span class="stat-num" id="statIncomplete">–</span>
          <span class="stat-lbl">Tdk Lengkap</span>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="sidebar-section" style="display: flex; flex-direction: column; gap: 10px;">
      
      <button id="btnPrintAll" class="btn-sidebar btn-primary">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Unduh Semua (ZIP)
      </button>
      
      <button id="btnPrintSelected" class="btn-sidebar btn-outline" style="display:none">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Unduh Terpilih (<span id="selectedCount">0</span>)
      </button>
    </div>

    <div class="sidebar-footer">
      <span>Generator Kartu Pelajar PHP</span>
      <span class="sidebar-version">v2.0</span>
    </div>
  </aside>

  <!-- Main content -->
  <main class="main-content">

    <!-- Top bar -->
    <div class="topbar">
      <div class="topbar-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="topbar-icon"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
        Generator Kartu Pelajar (Database)
      </div>
      <div class="topbar-actions">
        <a href="setup.php" class="btn-setup" style="margin-right: 15px; font-size: 13px; color: #3b82f6; text-decoration: none; display: flex; align-items: center; gap: 4px;">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
          Inisialisasi Database
        </a>
        <label class="toggle-label">
          <input type="checkbox" id="showBack" /> Tampilkan Sisi Belakang
        </label>
        <label class="toggle-label">
          <input type="checkbox" id="selectMode" /> Mode Pilih
        </label>
      </div>
    </div>

    <!-- Loading -->
    <div id="loadingState" class="loading-state" style="display:none">
      <div class="loader"></div>
      <p>Memuat data siswa dari database…</p>
    </div>

    <!-- Select Class state -->
    <div id="selectClassState" class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--blue-main); width: 64px; height: 64px; margin-bottom: 16px;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/></svg>
      <p style="font-weight: 600; color: var(--gray-600);">Silakan pilih filter kelas terlebih dahulu untuk menampilkan kartu pelajar.</p>
    </div>

    <!-- Empty state -->
    <div id="emptyState" class="empty-state" style="display:none">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <p>Tidak ada siswa ditemukan</p>
    </div>

    <!-- Cards grid -->
    <div class="cards-grid" id="cardsGrid"></div>

  </main>
</div>

<!-- ===== PRINT AREA ===== -->
<div id="printArea" class="print-area"></div>

<!-- ===== MODAL CRUD ===== -->
<div id="crudModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTitle">Tambah Siswa Baru</h3>
      <span class="modal-close">&times;</span>
    </div>
    <form id="studentForm" enctype="multipart/form-data">
      <input type="hidden" name="id" id="studentId" />
      
      <div class="form-grid">
        <div class="form-group">
          <label for="studentNis">Nomor Induk Siswa (NIS)</label>
          <input type="text" name="nis" id="studentNis" placeholder="Contoh: 262707018" />
        </div>
        
        <div class="form-group">
          <label for="studentNisn">NISN <span class="required">*</span></label>
          <input type="text" name="nisn" id="studentNisn" placeholder="Contoh: 3134122158" required />
        </div>
      </div>

      <div class="form-group">
        <label for="studentNama">Nama Lengkap <span class="required">*</span></label>
        <input type="text" name="nama_lengkap" id="studentNama" placeholder="Contoh: AILA AZZHARIEN RAMADHANI" required />
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="studentJk">Jenis Kelamin</label>
          <select name="jenis_kelamin" id="studentJk">
            <option value="">Pilih...</option>
            <option value="Laki-Laki">Laki-Laki</option>
            <option value="Perempuan">Perempuan</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="studentAgama">Agama</label>
          <input type="text" name="agama" id="studentAgama" placeholder="Contoh: Islam" />
        </div>
      </div>

      <div class="form-grid">
        <div class="form-group">
          <label for="studentTtl">Tempat, Tanggal Lahir</label>
          <input type="text" name="tempat_tanggal_lahir" id="studentTtl" placeholder="Contoh: BEKASI, 26 JUNI 2013" />
        </div>
        
        <div class="form-group">
          <label for="studentKelas">Kelas</label>
          <input type="text" name="kelas" id="studentKelas" placeholder="Contoh: KELAS 7.01" />
        </div>
      </div>

      <div class="form-group">
        <label for="studentOrtu">Nomor HP Orang Tua (wajib diawali 62)</label>
        <input type="text" name="nomor_ortu" id="studentOrtu" placeholder="Contoh: 628951234567" />
      </div>

      <div class="form-group">
        <label for="studentAlamat">Alamat Lengkap</label>
        <textarea name="alamat_lengkap" id="studentAlamat" rows="3" placeholder="Nama kampung, RT/RW, kelurahan, kecamatan..."></textarea>
      </div>

      <div class="form-group">
        <label>Foto Siswa</label>
        <div class="photo-upload-container">
          <div class="photo-preview-box">
            <img id="photoPreview" src="" alt="Pratinjau Foto" style="display: none;" />
            <div id="photoPlaceholder" class="photo-placeholder">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
              <span>Belum Ada Foto</span>
            </div>
          </div>
          <div class="photo-upload-input-wrap">
            <input type="file" name="photo" id="studentPhoto" accept="image/*" />
            <p class="help-text">Format: JPG, JPEG, PNG, WEBP. Maks 2MB.</p>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-outline modal-cancel">Batal</button>
        <button type="submit" class="btn btn-primary" id="btnSubmitForm">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script src="app.js"></script>
</body>
</html>
