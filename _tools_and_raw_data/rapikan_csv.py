import csv
import re

INPUT_FILE  = r"C:\laragon\www\KartuPelajarSMPN1KDW\DataMuridSMPN1KDW_Rapi.csv"
OUTPUT_FILE = r"C:\laragon\www\KartuPelajarSMPN1KDW\DataMuridSMPN1KDW_Rapi.csv"

def normalize_name(name: str) -> str:
    """Hilangkan nomor urut di depan nama (mis. '07. ATHIFA' -> 'ATHIFA'),
    lalu uppercase & strip whitespace."""
    name = re.sub(r'^\d+\.\s*', '', name.strip())
    return name.upper().strip()

def count_filled(row: list) -> int:
    """Hitung jumlah field yang tidak kosong (selain Timestamp & Kelas)."""
    return sum(1 for cell in row[2:] if cell.strip())

def main():
    with open(INPUT_FILE, encoding="utf-8-sig", newline='') as f:
        reader = csv.reader(f)
        header = next(reader)
        rows   = list(reader)

    print(f"Total baris masuk : {len(rows)}")

    # ----------------------------------------------------------------
    # Kelompokkan berdasarkan kunci: (kelas, nama_normal)
    # Jika nama kosong, gunakan NISN sebagai fallback kunci.
    # ----------------------------------------------------------------
    groups: dict[tuple, list] = {}
    for row in rows:
        kelas = row[1].strip().upper() if len(row) > 1 else ""
        nama  = normalize_name(row[2]) if len(row) > 2 else ""
        nisn  = row[5].strip()          if len(row) > 5 else ""

        key = (kelas, nama if nama else nisn)
        groups.setdefault(key, []).append(row)

    # ----------------------------------------------------------------
    # Dari setiap grup, pilih baris paling lengkap
    # (jika seri, ambil yang timestamp terbaru)
    # ----------------------------------------------------------------
    deduped = []
    for key, group in groups.items():
        if len(group) == 1:
            deduped.append(group[0])
        else:
            # Urutkan: lebih banyak isi dulu, lalu timestamp terbaru
            def sort_key(r):
                filled = count_filled(r)
                ts = r[0].strip()
                return (filled, ts)
            best = sorted(group, key=sort_key, reverse=True)[0]
            deduped.append(best)
            if len(group) > 1:
                names = [r[2] for r in group]
                print(f"  Duplikat ditemukan ({len(group)}x): {key[0]} | {key[1]}")
                print(f"    Entri: {names}")

    # Urutkan kembali berdasarkan Kelas lalu Nama
    deduped.sort(key=lambda r: (r[1].strip().upper(), normalize_name(r[2])))

    with open(OUTPUT_FILE, "w", encoding="utf-8-sig", newline='') as f:
        writer = csv.writer(f, quoting=csv.QUOTE_MINIMAL)
        writer.writerow(header)
        writer.writerows(deduped)

    removed = len(rows) - len(deduped)
    print(f"\nSelesai!")
    print(f"  Baris sebelum : {len(rows)}")
    print(f"  Baris sesudah : {len(deduped)}")
    print(f"  Duplikat dihapus : {removed}")
    print(f"  Output : {OUTPUT_FILE}")

if __name__ == "__main__":
    main()
