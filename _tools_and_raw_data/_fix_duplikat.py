import csv, os

baru_path = r'C:\laragon\www\KartuPelajarSMPN1KDW\DataMuridBaru.csv'
temp_path = r'C:\laragon\www\KartuPelajarSMPN1KDW\_temp_fix.csv'

NISN_SALAH = '3134360295'
NISN_BENAR = '3134360294'
NIS_BENAR  = '262707052'

with open(baru_path, 'r', encoding='utf-8-sig') as f:
    rows = list(csv.DictReader(f))

fieldnames = list(rows[0].keys())

# Temukan kedua baris
row_salah = next((r for r in rows if r['NISN'].strip() == NISN_SALAH), None)
row_benar = next((r for r in rows if r['NISN'].strip() == NISN_BENAR), None)

print("Baris NISN salah (data lengkap):")
print({k: v for k, v in row_salah.items()})
print()
print("Baris NISN benar (data kurang):")
print({k: v for k, v in row_benar.items()})

# Gabungkan: gunakan data dari row_salah, koreksi NISN & NIS, bersihkan nama
merged = dict(row_salah)
merged['NISN']         = NISN_BENAR
merged['NIS']          = NIS_BENAR
# Bersihkan prefix "04. " dari nama
import re
merged['Nama Lengkap'] = re.sub(r'^\d+\.\s*', '', merged['Nama Lengkap']).strip()
# Ambil data yg lebih lengkap dari row_benar jika row_salah kosong
for k in fieldnames:
    if not merged[k].strip() and row_benar[k].strip():
        merged[k] = row_benar[k]

print()
print("Hasil gabungan:")
print({k: v for k, v in merged.items()})

# Tulis ulang: skip row_salah & row_benar, sisipkan merged di posisi row_salah
new_rows = []
inserted = False
for r in rows:
    nisn = r['NISN'].strip()
    if nisn == NISN_SALAH:
        new_rows.append(merged)   # ganti dengan merged
        inserted = True
    elif nisn == NISN_BENAR:
        pass                       # hapus duplikat
    else:
        new_rows.append(r)

with open(temp_path, 'w', encoding='utf-8-sig', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=fieldnames)
    writer.writeheader()
    writer.writerows(new_rows)

os.replace(temp_path, baru_path)
print(f'\nSelesai! Total baris: {len(new_rows)} siswa')
