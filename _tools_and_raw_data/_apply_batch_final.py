import csv
import os

fix_data_path = 'FixData.csv'
temp_path = '_temp_batch_final.csv'

updates = {
    'AMAL SALEH': {
        'NISN': '134628997',
        'Nomor orang tua (wajib diawali 62)': '6281316725934',
        'Agama': 'Islam',
        'Alamat Lengkap': 'Kp. Kedung gede RT/015  RW/005'
    },
    'WANDIRA REGITA RAMANIYA': {
        'Nomor orang tua (wajib diawali 62)': '6285714825720',
        'Tempat Tanggal Lahir': 'KARAWANG, 21 JUNI 2014',
        'Agama': 'Islam',
        'Alamat Lengkap': 'Perum TPI Blok i 19 no 15 Waringinjaya Kedungwaringin'
    },
    'AHMAD AVIV SIDQI': {
        'Nomor orang tua (wajib diawali 62)': '6289699256241',
        'Alamat Lengkap': 'Perum gcc 2 blok E 21 no 37 RT 027 Rw  007'
    },
    'ARGA RAMHAZ RUKMANA': {
        'Nomor orang tua (wajib diawali 62)': '6285894094194',
        'Alamat Lengkap': 'kp. kramat RT 21 RW 006 kecamatan kedung Waringin desa kedung Waringin kabupaten bekasi'
    },
    'ICA PUTRI MAULIDINA': {
        'Nomor orang tua (wajib diawali 62)': '6287804114397',
        'Alamat Lengkap': 'jalan Kedung Waringin RT 007 RW 003 kecamatan Kedung Waringin kabupaten Bekasi '
    },
    'ADAM ALEXI PRATAMA': {
        'NISN': '149359236',
        'Alamat Lengkap': 'Rawa Kuda RT 07 RW 04 no.10, Desa Karang Harum Kec. Kedungwaringin  Kab. Bekasi'
    },
    'AERUL AGUSTIAN': {
        'NISN': '132415524',
        'Alamat Lengkap': 'Kp. Gedung gede RT 03 RW 01  Desa Kedungwaringin Kecamatan Kedungwaringin Kab. Bekasi'
    },
    "DIANDRA NAFI'AH": {
        'Nomor orang tua (wajib diawali 62)': '6282114734228',
        'Alamat Lengkap': 'Perum GCC 2 blok E21 no 14 jalan gang Edelweiss  '
    },
    'KEYLA LINTANG NADHIRA': {
        'Nomor orang tua (wajib diawali 62)': '6281224465742',
        'Alamat Lengkap': 'Perum GCC 2 blok H35 No.19 Kedungwaringin Kab. Bekasi'
    },
    'SALMA SHAFIRA': {
        'Nomor orang tua (wajib diawali 62)': '6281318666746',
        'Alamat Lengkap': 'Perum GCC 2 blok H9 no.1 Gang Kencana RT 25 RW  07 Desa Kedungwaringin Kec. Kedungwaringin Kab. Bekasi'
    },
    'JIDAN NUR AKBAR': {
        'Nomor orang tua (wajib diawali 62)': '6285693407834',
        'Tempat Tanggal Lahir': 'BEKASI, 23 DESEMBER 2013',
        'Agama': 'Islam',
        'Alamat Lengkap': 'Babakan cau, RT 01 RW 03 Desa Kedungwaringin Kec. Kedungwaringin Kab. Bekasi'
    },
    'JAESEN ELIANIUS LIMBONG': {
        'NISN': '139974576',
        'Nomor orang tua (wajib diawali 62)': '6282211407614',
        'Tempat Tanggal Lahir': 'KARAWANG, 19 OKTOBER 2013',
        'Agama': 'Kristen',
        'Alamat Lengkap': 'Perum BWI 1 blok A1 no 28'
    },
    'ADHELIA FARANISA AZNI': {
        'Alamat Lengkap': 'Kp.kedung Kole rt06/03 desa karang mekar kec.kedung waringin.bekasi'
    },
    'RACHELLIA RAMADHAN': {
        'Nomor orang tua (wajib diawali 62)': '6283183756833',
        'Tempat Tanggal Lahir': 'BEKASI, 18 JULI 2013',
        'Agama': 'Islam',
        'Alamat Lengkap': 'kp,kedung gede Rt12/005 kec kedung waringin kab, Bekasi'
    },
    'RAISYA MELANI': {
        'Nomor orang tua (wajib diawali 62)': '6289693155595',
        'Tempat Tanggal Lahir': 'BEKASI, 14 MEI 2013',
        'Agama': 'Islam',
        'Alamat Lengkap': 'Kp. Kedung Gede, RT 003 RW 001, Kec. KedungWaringin, Desa. KedungWaringin, Bekasi, Jawa Barat'
    }
}

rows_updated = 0
with open(fix_data_path, 'r', encoding='utf-8-sig') as f:
    rdr = csv.DictReader(f)
    fieldnames = list(rdr.fieldnames)
    rows = list(rdr)

for r in rows:
    name = r['Nama Lengkap'].strip().upper()
    if name in updates:
        for k, v in updates[name].items():
            r[k] = v
        rows_updated += 1

with open(temp_path, 'w', encoding='utf-8-sig', newline='') as f:
    w = csv.DictWriter(f, fieldnames=fieldnames)
    w.writeheader()
    w.writerows(rows)

os.replace(temp_path, fix_data_path)

print(f"SELESAI UPDATE BATCH SISWA FINAL!")
print(f"  - Total siswa ter-update: {rows_updated}")
