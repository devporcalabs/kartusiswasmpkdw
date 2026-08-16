import csv, re, os

baru_path    = r'C:\laragon\www\KartuPelajarSMPN1KDW\DataMuridBaru.csv'
absensi_path = r'C:\laragon\www\KartuPelajarSMPN1KDW\AbsensiMurid.csv'
temp_path    = r'C:\laragon\www\KartuPelajarSMPN1KDW\_temp_nis.csv'

# Buat lookup NISN -> NIM dari AbsensiMurid
absensi_map = {}
with open(absensi_path, 'r', encoding='utf-8-sig') as f:
    for row in csv.DictReader(f):
        nisn = row['NISN'].strip()
        nim  = row['NIM'].strip()
        if nisn and nim:
            absensi_map[nisn] = nim

def is_valid_nis(nis):
    return bool(re.fullmatch(r'2627\d{5}', nis.strip()))

with open(baru_path, 'r', encoding='utf-8-sig') as f:
    rows = list(csv.DictReader(f))

fieldnames = list(rows[0].keys())

fixed     = 0
not_found = []

for r in rows:
    nis  = r['NIS'].strip()
    nisn = r['NISN'].strip()
    nama = r['Nama Lengkap']
    if not is_valid_nis(nis):
        nim = absensi_map.get(nisn, '')
        if nim:
            print(f"FIXED  : NIS [{nis}] -> [{nim}] | Nama={nama}")
            r['NIS'] = nim
            fixed += 1
        else:
            print(f"NOTFND : NIS [{nis}] | NISN={nisn} | Nama={nama}")
            not_found.append(nama)

print(f'\nTotal diperbaiki : {fixed}')
print(f'Tidak ditemukan  : {len(not_found)}')

with open(temp_path, 'w', encoding='utf-8-sig', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=fieldnames)
    writer.writeheader()
    writer.writerows(rows)

os.replace(temp_path, baru_path)
print('File disimpan.')
