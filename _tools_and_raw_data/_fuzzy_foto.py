import os, csv, re
from thefuzz import process, fuzz

foto_dir = r'C:\laragon\www\KartuPelajarSMPN1KDW\PAS_FOTO_SMPN_1_KEDUNGWARINGIN'
csv_path = r'C:\laragon\www\KartuPelajarSMPN1KDW\FixData.csv'

THRESHOLD = 80  # skor minimal kecocokan (0-100)

def clean_name(n):
    # Hapus prefix nomor "06. " dsb, strip, uppercase
    return re.sub(r'^\d+\.\s*', '', n).strip().upper()

# Kumpulkan semua foto kelas 7
foto_map = {}  # nama_bersih_upper -> path relatif
kelas_dirs = ['7.1','7.2','7.3','7.4','7.5','7.6','7.7','7.8','7.9','7.10','7.11']
for kd in kelas_dirs:
    dirpath = os.path.join(foto_dir, kd)
    if not os.path.isdir(dirpath): continue
    for f in os.listdir(dirpath):
        if f.lower().endswith(('.jpg','.jpeg','.png','.webp')):
            key = clean_name(os.path.splitext(f)[0])
            foto_map[key] = os.path.join('PAS_FOTO_SMPN_1_KEDUNGWARINGIN', kd, f).replace('\\','/')

foto_keys = list(foto_map.keys())

with open(csv_path, 'r', encoding='utf-8-sig') as f:
    siswa = list(csv.DictReader(f))

hasil_ada    = []
hasil_fuzzy  = []
hasil_tidak  = []

for s in siswa:
    nama_asli = s['Nama Lengkap'].strip()
    nama = clean_name(nama_asli)

    # Exact match dulu
    if nama in foto_map:
        hasil_ada.append((nama_asli, foto_map[nama], 100, 'EXACT'))
        continue

    # Fuzzy match
    match = process.extractOne(nama, foto_keys, scorer=fuzz.token_sort_ratio)
    if match and match[1] >= THRESHOLD:
        hasil_fuzzy.append((nama_asli, foto_map[match[0]], match[1], match[0]))
    else:
        top = process.extractOne(nama, foto_keys, scorer=fuzz.token_sort_ratio)
        hasil_tidak.append((nama_asli, top[0] if top else '-', top[1] if top else 0))

print(f"Exact match   : {len(hasil_ada)}")
print(f"Fuzzy match   : {len(hasil_fuzzy)}")
print(f"Tidak ditemukan: {len(hasil_tidak)}")
print()
print("=== FUZZY MATCHES (perlu konfirmasi) ===")
for nama, path, skor, foto_nama in sorted(hasil_fuzzy, key=lambda x: x[2]):
    print(f"[{skor:3d}] CSV: {nama:40s} -> FOTO: {foto_nama}")

print()
print("=== TIDAK DITEMUKAN ===")
for nama, best, skor in hasil_tidak:
    print(f"[{skor:3d}] {nama:40s}  (kandidat terbaik: {best})")
