/* app.js – Kartu Pelajar SMPN 1 Kedungwaringin (Database Version) */

// ── CONFIG ──────────────────────────────────────────────────────────────
const SCHOOL_INFO = {
  gov: 'PEMERINTAH KABUPATEN BEKASI',
  dept: 'DINAS PENDIDIKAN',
  name: 'SMP NEGERI 1 KEDUNGWARINGIN',
  addr: 'Jl. Raya Kedungwaringin No.7A, Kab.Bekasi, Jawa Barat 17540',
  npsn: '20228903',
  telp: '(021) 1234567',
  web: 'smpn1kedungwaringin.sch.id',
  email: 'ksmpnsatu@ymail.com',
  kepala: 'Abdul Gofur, S.Pd',
  nip: 'Pembina TK.I/IV.b\nNIP 19701110 199903 1005',
};

// ── STATE ────────────────────────────────────────────────────────────────
let filteredStudents = [];
let selectedNisns = new Set();
let selectMode = false;
let showBack = false;
let qrCache = {};      // nisn → data-url

// ── COMPLETENESS CHECK ───────────────────────────────────────────────────
function isComplete(s) {
  const noHp  = (s.nomor_ortu || '').trim();
  const ttl   = (s.tempat_tanggal_lahir || '').trim();
  const alamat = (s.alamat_lengkap || '').trim();
  return noHp !== '' && ttl !== '' && alamat !== '';
}

// ── DOM REFS ─────────────────────────────────────────────────────────────
const cardsGrid = document.getElementById('cardsGrid');
const loadingState = document.getElementById('loadingState');
const emptyState = document.getElementById('emptyState');
const searchInput = document.getElementById('searchInput');
const kelasFilter = document.getElementById('kelasFilter');
const statTotal = document.getElementById('statTotal');
const statFiltered = document.getElementById('statFiltered');
const statIncomplete = document.getElementById('statIncomplete');
const btnPrintAll = document.getElementById('btnPrintAll');
const btnPrintSelected = document.getElementById('btnPrintSelected');
const selectedCountEl = document.getElementById('selectedCount');
const selectModeChk = document.getElementById('selectMode');
const showBackChk = document.getElementById('showBack');
const printArea = document.getElementById('printArea');

// Modal CRUD DOM Refs
const crudModal = document.getElementById('crudModal');
const studentForm = document.getElementById('studentForm');
const modalTitle = document.getElementById('modalTitle');
const studentId = document.getElementById('studentId');
const studentNis = document.getElementById('studentNis');
const studentNisn = document.getElementById('studentNisn');
const studentNama = document.getElementById('studentNama');
const studentJk = document.getElementById('studentJk');
const studentAgama = document.getElementById('studentAgama');
const studentTtl = document.getElementById('studentTtl');
const studentKelas = document.getElementById('studentKelas');
const studentOrtu = document.getElementById('studentOrtu');
const studentAlamat = document.getElementById('studentAlamat');
const studentPhoto = document.getElementById('studentPhoto');
const photoPreview = document.getElementById('photoPreview');
const photoPlaceholder = document.getElementById('photoPlaceholder');

// ── INIT & FETCH ─────────────────────────────────────────────────────────
fetchStudents();

function fetchStudents() {
  const searchVal = searchInput.value.trim();
  const kelasVal = kelasFilter.value;
  
  const selectClassState = document.getElementById('selectClassState');
  
  if (!kelasVal) {
    loadingState.style.display = 'none';
    emptyState.style.display = 'none';
    cardsGrid.innerHTML = '';
    if (selectClassState) selectClassState.style.display = 'flex';
    
    statTotal.textContent = '–';
    statFiltered.textContent = '0';
    if (statIncomplete) statIncomplete.textContent = '–';
    return;
  }
  
  if (selectClassState) selectClassState.style.display = 'none';
  loadingState.style.display = 'flex';
  emptyState.style.display = 'none';
  
  const url = `students_api.php?search=${encodeURIComponent(searchVal)}&kelas=${encodeURIComponent(kelasVal)}`;
  
  fetch(url)
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        filteredStudents = res.data;
        statTotal.textContent = res.stats.total;
        statFiltered.textContent = filteredStudents.length;
        if (statIncomplete) statIncomplete.textContent = res.stats.incomplete;
        
        renderCards();
      } else {
        alert("Gagal memuat data: " + res.message);
      }
      loadingState.style.display = 'none';
    })
    .catch(err => {
      console.error(err);
      loadingState.innerHTML = `<p style="color:#c62828">Gagal memuat data dari database: ${err.message}</p>`;
    });
}

// ── SEARCH DEBOUNCE & FILTER LISTENERS ──────────────────────────────────
let searchTimeout = null;
searchInput.addEventListener('input', () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(fetchStudents, 300);
});

kelasFilter.addEventListener('change', fetchStudents);

// ── SELECT MODE ───────────────────────────────────────────────────────────
selectModeChk.addEventListener('change', () => {
  selectMode = selectModeChk.checked;
  document.body.classList.toggle('select-mode', selectMode);
  btnPrintSelected.style.display = selectMode ? 'flex' : 'none';
  if (!selectMode) {
    selectedNisns.clear();
    document.querySelectorAll('.card-select-cb').forEach(cb => cb.checked = false);
    document.querySelectorAll('.card-set').forEach(el => el.classList.remove('selected'));
    updateSelectedCount();
  }
});

function updateSelectedCount() {
  selectedCountEl.textContent = selectedNisns.size;
}

// ── SHOW BACK ─────────────────────────────────────────────────────────────
showBackChk.addEventListener('change', () => {
  showBack = showBackChk.checked;
  document.querySelectorAll('.card-back').forEach(el => {
    el.style.display = showBack ? 'block' : 'none';
  });
});

// ── PHOTO PATH ────────────────────────────────────────────────────────────
function photoPath(student) {
  if (student.photo_path) {
    return student.photo_path;
  }
  // Fallback default
  return `PAS_FOTO_SMPN_1_KEDUNGWARINGIN/SUSULAN/${student.nama_lengkap.trim().toUpperCase()}.webp`;
}

// ── QR CODE ───────────────────────────────────────────────────────────────
function generateQR(nisn, container) {
  container.innerHTML = '';
  if (qrCache[nisn]) {
    const img = document.createElement('img');
    img.src = qrCache[nisn];
    img.style.cssText = 'width:94px;height:94px;';
    container.appendChild(img);
    return;
  }
  const qr = new QRCode(container, {
    text: String(nisn),
    width: 94, height: 94,
    colorDark: '#000000', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.M,
  });
  // cache setelah sedikit delay
  setTimeout(() => {
    const canvas = container.querySelector('canvas');
    if (canvas) qrCache[nisn] = canvas.toDataURL('image/png');
  }, 200);
}

// ── FORMAT HELPERS ────────────────────────────────────────────────────────
function formatDate(raw) {
  if (!raw) return '';
  return raw.trim().toUpperCase();
}

function truncateAddr(addr) {
  if (!addr) return '';
  return addr.replace(/\s+/g, ' ').trim().toUpperCase();
}

// ── BUILD CARD HTML ───────────────────────────────────────────────────────
function buildCardFrontEl(student, index) {
  const nisn = student.nisn || '–';
  const nis = student.nis || '–';
  const nama = (student.nama_lengkap || '').toUpperCase();
  const ttl = formatDate(student.tempat_tanggal_lahir);
  const jk = (student.jenis_kelamin || '').toUpperCase();
  const alamat = truncateAddr(student.alamat_lengkap);
  const kelas = student.kelas || '';

  const front = document.createElement('div');
  front.className = 'card-front';
  front.innerHTML = `
    <!-- Watermark -->
    <div class="card-watermark"><span>SMPN 1 KDW</span></div>

    <!-- Header -->
    <div class="card-header">
      <img src="Logo-KabBekasi.png" alt="Logo Kab Bekasi" class="card-header-logo" onerror="this.style.display='none'" />
      <div class="card-header-text">
        <div class="card-header-gov">${SCHOOL_INFO.gov}</div>
        <div class="card-header-dept">${SCHOOL_INFO.dept}</div>
        <div class="card-header-school">${SCHOOL_INFO.name}</div>
        <div class="card-header-addr">${SCHOOL_INFO.addr}</div>
      </div>
      <img src="LogoSMPN1KDW.webp" alt="Logo SMPN 1 KDW" class="card-header-logo2" onerror="this.style.display='none'" />
    </div>

    <!-- Body -->
    <div class="card-body">
      <div class="card-title-band">KARTU PELAJAR</div>

      <!-- Info area: foto kiri, data kanan -->
      <div class="card-info-area">

        <!-- Photo -->
        <div class="card-photo-wrap">
          <img class="card-photo" src="${photoPath(student)}"
               alt="Foto ${nama}" loading="lazy"
               onerror="this.outerHTML='<div class=\\'card-photo-placeholder\\'><svg viewBox=\\'0 0 24 24\\' fill=\\'none\\' stroke=\\'currentColor\\' stroke-width=\\'1.5\\'><circle cx=\\'12\\' cy=\\'8\\' r=\\'4\\'/><path d=\\'M4 20c0-4 3.6-7 8-7s8 3 8 7\\'/></svg></div>'" />
        </div>

        <div class="card-data">
          <div class="data-row name-row"><span class="data-key">NAMA</span><span class="data-sep">:</span><span class="data-val">${nama}</span></div>
          <div class="data-row"><span class="data-key">NISN</span><span class="data-sep">:</span><span class="data-val">${nisn}</span></div>
          <div class="data-row"><span class="data-key">NIS</span><span class="data-sep">:</span><span class="data-val">${nis}</span></div>
          <div class="data-row"><span class="data-key">TTL</span><span class="data-sep">:</span><span class="data-val">${ttl}</span></div>
          <div class="data-row"><span class="data-key">JENIS KELAMIN</span><span class="data-sep">:</span><span class="data-val">${jk}</span></div>
          <div class="data-row address-row"><span class="data-key">ALAMAT</span><span class="data-sep">:</span><span class="data-val">${alamat}</span></div>
        </div>

      </div>

      <!-- Bottom row: RFID Icon | Contact Info | QR Code | TTD Kepsek -->
      <div class="card-bottom">
        <div class="card-rfid-icon-wrap">
          <img src="rfidicon.png" class="card-rfid-icon" alt="RFID" />
        </div>
        <div class="card-contact-wrap">
          <div class="contact-line">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="contact-icon"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <span>ksmpnsatu@ymail.com</span>
          </div>
          <div class="contact-line">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="contact-icon"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            <span>www.smpn1kedungwaringin.sch.id</span>
          </div>
        </div>
        <div class="card-qr-wrap">
          <div class="card-qr-box" id="qr-front-${index}"></div>
        </div>
        <div class="card-sig">
          <div class="sig-date">Bekasi, Juli 2026</div>
          <div class="sig-title">Kepala Sekolah</div>
          <img src="CAPttdKepsek.png" alt="TTD Kepsek" class="sig-img" onerror="this.style.display='none'" />
          <div class="sig-line"></div>
          <div class="sig-name">${SCHOOL_INFO.kepala}</div>
          <div class="sig-nip">${SCHOOL_INFO.nip.replace('\n', '<br>')}</div>
        </div>
      </div>

    </div>
  `;

  // Generate QR
  setTimeout(() => {
    const qrBox = front.querySelector(`#qr-front-${index}`);
    if (qrBox) generateQR(nisn, qrBox);
  }, 50);

  return front;
}

function buildCardBackEl(student) {
  const back = document.createElement('div');
  back.className = 'card-back';
  back.style.display = showBack ? 'block' : 'none';
  back.innerHTML = `
    <!-- Header -->
    <div class="card-header">
      <img src="Logo-KabBekasi.png" alt="Logo Kab Bekasi" class="card-header-logo" onerror="this.style.display='none'" />
      <div class="card-header-text">
        <div class="card-header-gov">${SCHOOL_INFO.gov}</div>
        <div class="card-header-dept">${SCHOOL_INFO.dept}</div>
        <div class="card-header-school">${SCHOOL_INFO.name}</div>
        <div class="card-header-addr">${SCHOOL_INFO.addr}</div>
      </div>
      <img src="LogoSMPN1KDW.webp" alt="Logo SMPN 1 KDW" class="card-header-logo2" onerror="this.style.display='none'" />
    </div>

    <div class="card-back-body">
      <div class="back-rules-title">KETENTUAN PENGGUNAAN KARTU</div>
      <div class="back-content">
        <div class="back-rules">
          <ol>
            <li>Kartu ini merupakan identitas resmi siswa dan wajib dibawa selama kegiatan sekolah.</li>
            <li>Kartu tidak dapat dipindahtangankan atau digunakan oleh orang lain.</li>
            <li>Kehilangan atau kerusakan kartu wajib segera dilaporkan kepada wali kelas.</li>
            <li>Penggantian kartu yang hilang atau rusak dapat dikenakan biaya.</li>
            <li>Kartu berlaku selama pemegang masih terdaftar sebagai siswa aktif.</li>
            <li>Penyalahgunaan kartu akan dikenakan sanksi sesuai tata tertib sekolah.</li>
            <li>Kartu digunakan untuk mengakses fasilitas dan layanan sekolah.</li>
            <li>Kartu berfungsi sebagai kartu identitas dan absensi berbasis RFID.</li>
          </ol>
        </div>
        <div class="back-contact">
          <div class="back-contact-item">
            <div class="back-contact-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
            </div>
            <div>
              <div class="back-contact-label">NPSN</div>
              <div class="back-contact-value">${SCHOOL_INFO.npsn}</div>
            </div>
          </div>
          <div class="back-contact-item">
            <div class="back-contact-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4 2 2 0 0 1 3.6 1.22h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 8.9a16 16 0 0 0 6 6l.86-.86a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.56 16.92z"/></svg>
            </div>
            <div>
              <div class="back-contact-label">Telepon</div>
              <div class="back-contact-value">${SCHOOL_INFO.telp}</div>
            </div>
          </div>
          <div class="back-contact-item">
            <div class="back-contact-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
            </div>
            <div>
              <div class="back-contact-label">Website</div>
              <div class="back-contact-value">${SCHOOL_INFO.web}</div>
            </div>
          </div>
          <div class="back-contact-item">
            <div class="back-contact-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <div>
              <div class="back-contact-label">Email</div>
              <div class="back-contact-value">${SCHOOL_INFO.email}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="back-footer">
        <div class="back-rfid-icon-wrap">
          <img src="rfidicon.png" class="back-rfid-icon" alt="RFID" />
        </div>
        <div class="back-credit">Kartu Pelajar<br>SMPN 1 Kedungwaringin</div>
      </div>
    </div>
  `;
  return back;
}

// ── RENDER CARDS ──────────────────────────────────────────────────────────
function renderCards() {
  cardsGrid.innerHTML = '';

  if (filteredStudents.length === 0) {
    emptyState.style.display = 'flex';
    return;
  }
  emptyState.style.display = 'none';

  filteredStudents.forEach((student, i) => {
    const nisn = student.nisn || i;
    const nama = (student.nama_lengkap || '').toUpperCase();
    const kelas = student.kelas || '';
    const complete = isComplete(student);

    const set = document.createElement('div');
    set.className = 'card-set' + (!complete ? ' card-set--incomplete' : '');
    set.dataset.nisn = nisn;

    // Tentukan field yang kosong untuk tooltip
    const missingFields = [];
    if (!(student.nomor_ortu || '').trim()) missingFields.push('No. HP');
    if (!(student.tempat_tanggal_lahir || '').trim()) missingFields.push('TTL');
    if (!(student.alamat_lengkap || '').trim()) missingFields.push('Alamat');

    const badgeHtml = !complete
      ? `<span class="incomplete-badge" title="Data belum lengkap: ${missingFields.join(', ')}">⚠ ${missingFields.join(' · ')}</span>`
      : '';

    // Header row
    const hdr = document.createElement('div');
    hdr.className = 'card-set-header';
    hdr.innerHTML = `
      <input type="checkbox" class="card-select-cb" id="cb-${i}" />
      <label for="cb-${i}" style="display:flex;align-items:center;gap:8px;cursor:pointer;flex-wrap:wrap;flex:1;">
        <span class="card-set-name">${nama}</span>
        <span class="card-set-class">${kelas}</span>
        ${badgeHtml}
      </label>
      
      <div class="card-actions-wrapper">
        <button class="btn-action-edit" data-id="${student.id}" title="Edit Siswa">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        </button>
        <button class="btn-action-delete" data-id="${student.id}" title="Hapus Siswa">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
        </button>
      </div>
    `;

    // Action listeners
    hdr.querySelector('.btn-action-edit').addEventListener('click', (e) => {
      e.stopPropagation();
      e.preventDefault();
      openEditModal(student.id);
    });

    hdr.querySelector('.btn-action-delete').addEventListener('click', (e) => {
      e.stopPropagation();
      e.preventDefault();
      deleteStudent(student.id, nama);
    });

    // Checkbox logic
    const cb = hdr.querySelector('.card-select-cb');
    cb.addEventListener('change', () => {
      if (cb.checked) {
        selectedNisns.add(nisn);
        set.classList.add('selected');
      } else {
        selectedNisns.delete(nisn);
        set.classList.remove('selected');
      }
      updateSelectedCount();
    });

    set.appendChild(hdr);
    set.appendChild(buildCardFrontEl(student, i));
    set.appendChild(buildCardBackEl(student));
    cardsGrid.appendChild(set);
  });
}

// ── MODAL CRUD INTERACTION ────────────────────────────────────────────────
function closeModal() {
  crudModal.classList.remove('show');
}

document.querySelector('.modal-close').addEventListener('click', closeModal);
document.querySelector('.modal-cancel').addEventListener('click', closeModal);

// Photo preview on change
studentPhoto.addEventListener('change', (e) => {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = (event) => {
      photoPreview.src = event.target.result;
      photoPreview.style.display = 'block';
      photoPlaceholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
  }
});

// Open Edit modal
function openEditModal(id) {
  const student = filteredStudents.find(s => s.id == id);
  if (!student) return;

  studentId.value = student.id;
  studentNis.value = student.nis || '';
  studentNisn.value = student.nisn || '';
  studentNama.value = student.nama_lengkap || '';
  studentJk.value = student.jenis_kelamin || '';
  studentAgama.value = student.agama || '';
  studentTtl.value = student.tempat_tanggal_lahir || '';
  studentKelas.value = student.kelas || '';
  studentOrtu.value = student.nomor_ortu || '';
  studentAlamat.value = student.alamat_lengkap || '';

  if (student.photo_path) {
    photoPreview.src = student.photo_path + '?t=' + new Date().getTime(); // Anti cache
    photoPreview.style.display = 'block';
    photoPlaceholder.style.display = 'none';
  } else {
    photoPreview.style.display = 'none';
    photoPlaceholder.style.display = 'flex';
  }

  modalTitle.textContent = "Ubah Data Siswa";
  crudModal.classList.add('show');
}

// Delete student
function deleteStudent(id, name) {
  if (confirm(`Apakah Anda yakin ingin menghapus data siswa "${name}"?`)) {
    fetch('students_api.php', {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(res => {
      if (res.success) {
        alert(res.message);
        fetchStudents();
      } else {
        alert("Gagal menghapus: " + res.message);
      }
    })
    .catch(err => {
      alert("Terjadi kesalahan koneksi: " + err.message);
    });
  }
}

// Submit Form (Create / Update)
studentForm.addEventListener('submit', (e) => {
  e.preventDefault();
  
  const formData = new FormData(studentForm);
  
  fetch('students_api.php', {
    method: 'POST',
    body: formData
  })
  .then(res => res.json())
  .then(res => {
    if (res.success) {
      alert(res.message);
      closeModal();
      fetchStudents();
    } else {
      alert("Gagal menyimpan data: " + res.message);
    }
  })
  .catch(err => {
    alert("Terjadi kesalahan koneksi: " + err.message);
  });
});

// ── EXPORT TO ZIP (JPG) ───────────────────────────────────────────────────
async function doExportZip(students) {
  if (!students || students.length === 0) {
    alert("Tidak ada siswa untuk diekspor!");
    return;
  }

  // Proteksi kata sandi untuk mengunduh ZIP
  const password = prompt("Masukkan password untuk mengunduh berkas ZIP:");
  if (password === null) {
    return; // Pengguna membatalkan
  }
  if (password !== "Aserd432!") {
    alert("Password salah! Akses ditolak.");
    return;
  }

  // 1. Tampilkan loading overlay premium
  const overlay = document.createElement('div');
  overlay.style.position = 'fixed';
  overlay.style.top = '0';
  overlay.style.left = '0';
  overlay.style.width = '100vw';
  overlay.style.height = '100vh';
  overlay.style.background = 'rgba(15, 23, 42, 0.85)';
  overlay.style.backdropFilter = 'blur(8px)';
  overlay.style.display = 'flex';
  overlay.style.flexDirection = 'column';
  overlay.style.alignItems = 'center';
  overlay.style.justifyContent = 'center';
  overlay.style.zIndex = '9999';
  overlay.style.color = '#fff';
  overlay.style.fontFamily = 'Inter, sans-serif';

  overlay.innerHTML = `
    <div style="background: #1e293b; padding: 32px; border-radius: 16px; width: 420px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); border: 1px solid #334155; text-align: center;">
      <div class="spinner" style="margin: 0 auto 20px; border: 4px solid #334155; border-top: 4px solid #3b82f6; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite;"></div>
      <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 700;">Mengekspor Kartu Pelajar</h3>
      <p id="exportProgressText" style="margin: 0 0 16px; font-size: 14px; color: #94a3b8;">Menyiapkan data...</p>
      <div style="width: 100%; height: 8px; background: #334155; border-radius: 4px; overflow: hidden;">
        <div id="exportProgressBar" style="width: 0%; height: 100%; background: #3b82f6; transition: width 0.2s;"></div>
      </div>
      <style>
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
      </style>
    </div>
  `;
  document.body.appendChild(overlay);

  const progressText = overlay.querySelector('#exportProgressText');
  const progressBar = overlay.querySelector('#exportProgressBar');

  const zip = new JSZip();
  const exportArea = document.createElement('div');
  exportArea.style.position = 'absolute';
  exportArea.style.left = '-9999px';
  exportArea.style.top = '-9999px';
  document.body.appendChild(exportArea);

  try {
    for (let i = 0; i < students.length; i++) {
      const student = students[i];
      const name = (student.nama_lengkap || '').trim().toUpperCase();
      const nisn = (student.nisn || '').trim();

      progressText.textContent = `Memproses ${i + 1} dari ${students.length}: ${name}`;
      progressBar.style.width = `${((i + 1) / students.length) * 100}%`;

      const cardContainer = document.createElement('div');
      cardContainer.style.position = 'relative';
      cardContainer.style.width = '505.5px';
      cardContainer.style.height = '319px';
      cardContainer.style.overflow = 'hidden';

      const cardEl = buildCardFrontEl(student, `exp-${i}`);
      // Matikan efek hover dan border radius browser print agar output gambar rata persegi tajam
      cardEl.style.transform = 'none';
      cardEl.style.boxShadow = 'none';
      cardEl.style.transition = 'none';

      cardContainer.appendChild(cardEl);
      exportArea.appendChild(cardContainer);

      // Beri sedikit jeda agar QR Code dan foto ter-render sempurna
      await new Promise(resolve => setTimeout(resolve, 150));

      const canvas = await html2canvas(cardEl, {
        scale: 2, // Menggunakan skala 2x untuk kualitas gambar cetak (HD)
        useCORS: true,
        logging: false,
        backgroundColor: null
      });

      const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.95));
      const safeName = name.replace(/[^A-Z0-9]/gi, '_');
      zip.file(`${safeName}_${nisn}.jpg`, blob);

      exportArea.removeChild(cardContainer);
    }

    progressText.textContent = "Mengompres file menjadi ZIP...";
    const zipBlob = await zip.generateAsync({ type: 'blob' });

    let zipName = 'Kartu_Pelajar.zip';
    const filterVal = kelasFilter.value;
    if (filterVal) {
      zipName = `Kartu_Pelajar_${filterVal.replace(/[^a-zA-Z0-9]/g, '_')}.zip`;
    }

    const link = document.createElement('a');
    link.href = URL.createObjectURL(zipBlob);
    link.download = zipName;
    link.click();

  } catch (error) {
    console.error(error);
    alert("Gagal mengekspor kartu: " + error.message);
  } finally {
    document.body.removeChild(overlay);
    document.body.removeChild(exportArea);
  }
}

// ── BUTTON LISTENERS ──────────────────────────────────────────────────────
btnPrintAll.addEventListener('click', () => {
  doExportZip(filteredStudents);
});

btnPrintSelected.addEventListener('click', () => {
  const sel = filteredStudents.filter(s => selectedNisns.has(s.nisn));
  if (sel.length === 0) { alert('Pilih minimal satu siswa!'); return; }
  doExportZip(sel);
});
