import csv, re, os

baru_path    = r'C:\laragon\www\KartuPelajarSMPN1KDW\DataMuridBaru.csv'
absensi_path = r'C:\laragon\www\KartuPelajarSMPN1KDW\AbsensiMurid.csv'
temp_path    = r'C:\laragon\www\KartuPelajarSMPN1KDW\_temp_nis2.csv'

# Buat lookup NISN -> NIM dan NAMA -> {NIM, NISN}
absensi_nisn = {}
absensi_nama = {}
with open(absensi_path, 'r', encoding='utf-8-sig') as f:
    for row in csv.DictReader(f):
        nisn = row['NISN'].strip()
        nim  = row['NIM'].strip()
        nama = row['NAMA MURID'].strip().upper()
        if nisn and nim:
            absensi_nisn[nisn] = {'nim': nim, 'nama': nama}
        if nama and nim:
            absensi_nama[nama] = {'nim': nim, 'nisn': nisn}

def is_valid_nis(nis):
    return bool(re.fullmatch(r'2627\d{5}', nis.strip()))

def clean_name(n):
    # Hapus prefix nomor seperti "04. "
    return re.sub(r'^\d+\.\s*', '', n).strip().upper()

with open(baru_path, 'r', encoding='utf-8-sig') as f:
    rows = list(csv.DictReader(f))

fieldnames = list(rows[0].keys())

fixed_nisn = 0
fixed_nama = 0
still_notfound = []

for r in rows:
    nis  = r['NIS'].strip()
    nisn = r['NISN'].strip()
    nama = clean_name(r['Nama Lengkap'])

    if not is_valid_nis(nis):
        # Coba cocok NISN dulu
        if nisn in absensi_nisn:
            nim = absensi_nisn[nisn]['nim']
            print(f"FIXED(NISN): [{nis}] -> [{nim}] | Nama={nama}")
            r['NIS'] = nim
            fixed_nisn += 1
        # Coba cocok nama
        elif nama in absensi_nama:
            entry = absensi_nama[nama]
            print(f"FIXED(NAMA): [{nis}] -> [{entry['nim']}] | NISN baru={entry['nisn']} | Nama={nama}")
            r['NIS']  = entry['nim']
            r['NISN'] = entry['nisn']  # koreksi NISN juga
            fixed_nama += 1
        else:
            print(f"NOTFOUND   : NIS=[{nis}] | NISN={nisn} | Nama={nama}")
            still_notfound.append(nama)

print(f'\nFixed via NISN  : {fixed_nisn}')
print(f'Fixed via Nama  : {fixed_nama}')
print(f'Masih tidak ada : {len(still_notfound)}')
if still_notfound:
    print('Daftar yang tidak ditemukan:')
    for n in still_notfound:
        print(f'  - {n}')

with open(temp_path, 'w', encoding='utf-8-sig', newline='') as f:
    writer = csv.DictWriter(f, fieldnames=fieldnames)
    writer.writeheader()
    writer.writerows(rows)

os.replace(temp_path, baru_path)
print('\nFile disimpan.')
