import csv
import os
import re

csv_files = [
    'AbsensiMurid.csv',
    'DataMuridKelas7_TP2026_2027.csv',
    'DataMuridSMPN1KDW.csv',
    'DataMuridSMPN1KDW_Rapi.csv',
    'Kelas/Kelas7-01.csv',
    'Kelas/Kelas7-02.csv',
    'Kelas/Kelas7-03.csv',
    'Kelas/Kelas7-04.csv',
    'Kelas/Kelas7-06.csv',
]

target_names = [
    'TINI LESTARI',
    'ASSIFA AFNI NURAENI',
    'INDAH NURMAIDA',
    'FAEYZA RAQILA RASYID',
    'AHMAD AVIV SIDQI',
    'MUHAMAD REZA',
    'AINUN LESTARI',
    'MUHAMAD TARIM',
    'SALSABILA AZIZAH REZKI M. H',
    'INDRA NURJATI',
    'HADDYAR DUMADI',
    'AMIRA RAFA LATIFAH',
    'MUHAMMAD RAFA RIZKY',
    'CINGKA PUTRA PRATAMA',
    'TIO HERMAWAN',
    'ELISA SALSA BILA',
    'ICA PUTRI MAULIDINA',
    'CINTANI SEPTIARA SETIAWAN',
    'ARGA RAMHAZ RUKMANA',
    'KIANDRA REISYA PUTRA',
    'SATRIA SANJAYA',
]

def clean_name(n):
    return re.sub(r'[^A-Z]', '', str(n).upper())

targets_clean = {clean_name(name): name for name in target_names}

print("=== PENCARIAN DATA SISWA DI SEMUA CSV ===")
for cf in csv_files:
    if not os.path.exists(cf):
        continue
    with open(cf, 'r', encoding='utf-8-sig') as f:
        rdr = csv.reader(f)
        try:
            header = next(rdr)
        except StopIteration:
            continue
            
        # Find column indices for Nama, NISN, NIS, Kelas
        name_idx, nisn_idx, nis_idx, kelas_idx = -1, -1, -1, -1
        for idx, col in enumerate(header):
            col_up = col.upper()
            if 'NAMA' in col_up or 'MURID' in col_up or 'SISWA' in col_up:
                if name_idx == -1: name_idx = idx
            elif 'NISN' in col_up:
                if nisn_idx == -1: nisn_idx = idx
            elif 'NIS' in col_up or 'NIM' in col_up:
                if nis_idx == -1: nis_idx = idx
            elif 'KELAS' in col_up:
                if kelas_idx == -1: kelas_idx = idx
                
        for row in rdr:
            if not row or len(row) <= max(name_idx, 0):
                continue
            name_val = row[name_idx].strip()
            cname = clean_name(name_val)
            if cname in targets_clean:
                nisn_val = row[nisn_idx].strip() if nisn_idx != -1 and len(row) > nisn_idx else 'N/A'
                nis_val = row[nis_idx].strip() if nis_idx != -1 and len(row) > nis_idx else 'N/A'
                kelas_val = row[kelas_idx].strip() if kelas_idx != -1 and len(row) > kelas_idx else 'N/A'
                print(f"File: {cf:35s} | Target: {targets_clean[cname]:25s} | Row Name: {name_val:25s} | NISN: {nisn_val:10s} | NIS: {nis_val:10s} | Kelas: {kelas_val}")
