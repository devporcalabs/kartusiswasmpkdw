import csv

# Cek di DataMuridBaru
print("--- DataMuridBaru.csv ---")
with open(r'C:\laragon\www\KartuPelajarSMPN1KDW\DataMuridBaru.csv', 'r', encoding='utf-8-sig') as f:
    rows = list(csv.DictReader(f))

matches = [(i+2, r) for i, r in enumerate(rows) if 'AQILA PUTRI' in r['Nama Lengkap'].upper()]
for line, r in matches:
    print(f"Baris {line}: NIS={r['NIS']} | NISN={r['NISN']} | Nama={r['Nama Lengkap']} | JK={r['Jenis Kelamin']}")

print()
print("--- AbsensiMurid.csv ---")
with open(r'C:\laragon\www\KartuPelajarSMPN1KDW\AbsensiMurid.csv', 'r', encoding='utf-8-sig') as f:
    for row in csv.DictReader(f):
        if 'AQILA PUTRI' in row['NAMA MURID'].upper():
            print(f"NIM={row['NIM']} | NISN={row['NISN']} | Nama={row['NAMA MURID']}")

print()
print("--- DataMuridSMPN1KDW_Rapi.csv ---")
with open(r'C:\laragon\www\KartuPelajarSMPN1KDW\DataMuridSMPN1KDW_Rapi.csv', 'r', encoding='utf-8-sig') as f:
    for row in csv.DictReader(f):
        if 'AQILA PUTRI' in row.get('Nama Murid', '').upper():
            print(f"NISN={row.get('NISN','')} | Nama={row.get('Nama Murid','')}")
