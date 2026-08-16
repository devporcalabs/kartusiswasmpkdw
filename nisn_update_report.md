# Laporan Pembaruan Data & Koreksi NISN Siswa

Laporan ini merangkum seluruh pembaruan profil data pribadi siswa, penambahan/penghapusan baris ganda, serta koreksi NISN bertabrakan (*deadlock*) selama sesi pengerjaan ini.

## 📊 Ringkasan Statistik
| Kategori Aksi | Jumlah Siswa |
| --- | --- |
| **Total NISN Dikoreksi / Diperbarui** | **75** |
| **Total Data Pribadi Diperbarui (WA/Alamat/Kelas)** | **93** |
| **Siswa Baru Ditambahkan** | **0** |
| **Baris Ganda Dihapus (Deduplikasi)** | **2** |

## 🔄 1. Daftar Rinci Koreksi NISN Siswa
Berikut adalah daftar siswa yang NISN-nya disesuaikan untuk mencocokkan data resmi sekolah dan menyelesaikan konflik tabrakan database (*duplicate key constraints*):

| Nama Siswa | Kelas | NISN Lama | NISN Baru | Alasan / Keterangan |
| --- | --- | --- | --- | --- |
| ABDUL RISKY | KELAS 7.01 | `3143796759` | `3139300414` | Koreksi data resmi |
| DEN ILHAM NUR CAHAYA | KELAS 7.01 | `3149664123` | `3117693524` | Koreksi data resmi |
| HAURA NAZZIFA | KELAS 7.01 | `3134555934` | `3139560775` | Koreksi data resmi |
| RAISA MAULIDDA AZZAHRA | KELAS 7.01 | `0143728276` | `0143728176` | Koreksi data resmi |
| AHMAD MAULANA JINDANY | KELAS 7.02 | `3146773216` | `3139976996` | Koreksi data resmi |
| ASSIFA AFNI NURAENI | KELAS 7.02 | `31311890105` | `0131695307` | Koreksi data resmi |
| CINDY APRILIA | KELAS 7.02 | `3142516694` | `0144181498` | Koreksi data resmi |
| DENI MARTIN | KELAS 7.02 | `3137612488` | `3131602991` | Koreksi data resmi |
| M. RAYKA F. | KELAS 7.02 | `3138486423` | `3126851513` | Koreksi data resmi |
| RAISA INDAH SARI | KELAS 7.02 | `3147685200` | `3139370242` | Koreksi data resmi |
| SAFA NAURA PUSPITA | KELAS 7.02 | `149857200` | `0149957200` | Koreksi data resmi |
| AQILA PUTRI FAUJIA | KELAS 7.03 | `3134360295` | `3134360294` | Koreksi data resmi |
| MUHAMAD REZA | KELAS 7.03 | `3140112702` | `3132486607` | Koreksi data resmi |
| ALYA NUR INDAH | KELAS 7.04 | `3146341985` | `3132164070` | Koreksi data resmi |
| AMELIA IRAWATI | KELAS 7.04 | `3137628494` | `3142516694` | Koreksi data resmi |
| CHELSEA KIMBERLEY ALISYA GUNAWAN | KELAS 7.04 | `202101017` | `3131069381` | Koreksi data resmi |
| FAISHAL IMAMU HAKIM | KELAS 7.04 | `0076355419` | `0144793407` | Koreksi data resmi |
| INDRA NURJATI | KELAS 7.04 | `202101010` | `3127503177` | Koreksi data resmi |
| JAENAL PAHMI | KELAS 7.04 | `0139835614` | `3130971976` | Koreksi data resmi |
| MAHANDIKA NOUVAN PRADITYA | KELAS 7.04 | `1021206120` | `0133120039` | Koreksi data resmi |
| AMIRA RAFA LATIFAH | KELAS 7.05 | `3144848738` | `3135165497` | Koreksi data resmi |
| ANAS SUPARDI | KELAS 7.05 | `3129327435` | `3129327434` | Resolusi tabrakan (Rayyan vs Anas) |
| ASHA AZALEA AZZAHWA | KELAS 7.05 | `0144181498` | `3147965187` | Koreksi data resmi |
| AZZAHRA RAMADHANI | KELAS 7.05 | `3136898986` | `3136898996` | Koreksi data resmi |
| CAHYADI | KELAS 7.05 | `3130971976` | `3149565455` | Koreksi data resmi |
| INDAH NURMAIDA | KELAS 7.05 | `015133949` | `3131890105` | Koreksi data resmi |
| MUHAMAD AIDIL AKBAR | KELAS 7.05 | `-` | `3149664123` | Koreksi data resmi |
| NABILA NUR ALIPAH | KELAS 7.05 | `3149565455` | `0138963006` | Koreksi data resmi |
| NAUFAL BAIHHAQI | KELAS 7.05 | `3139976996` | `3137612488` | Koreksi data resmi |
| TIO HERMAWAN | KELAS 7.05 | `3139665904` | `3138540231` | Koreksi data resmi |
| TRI WULAN DARI | KELAS 7.05 | `3132297014` | `0139734142` | Koreksi data resmi |
| ZAHRA AULIA PUTRI | KELAS 7.05 | `202101043` | `0149619555` | Koreksi data resmi |
| AL FARIZI ZIKRI | KELAS 7.06 | `0131695307` | `0132965882` | Koreksi data resmi |
| ALISA MAULIDA | KELAS 7.06 | `3132033790` | `3132474257` | Koreksi data resmi |
| ELISA SALSA BILA | KELAS 7.06 | `3131399230` | `0139322660` | Koreksi data resmi |
| KEIKO ICHI YOSA | KELAS 7.06 | `314726136` | `3147262136` | Koreksi data resmi |
| KEYLA NURFADILLAH | KELAS 7.06 | `1046533673` | `0146533673` | Koreksi data resmi |
| MUHAMAD REYHAN | KELAS 7.06 | `3139370242` | `3138486423` | Koreksi data resmi |
| HADDYAR DUMADI | KELAS 7.07 | `3127503177` | `3132230029` | Koreksi data resmi |
| MUHAMAD AL FARIZI | KELAS 7.07 | `3131027135` | `0145011754` | Koreksi data resmi |
| MUHAMMAD RAFA RIZKY | KELAS 7.07 | `1039734142` | `3144848738` | Koreksi data resmi |
| RAFI YANWAR | KELAS 7.07 | `3139486079` | `3136720585` | Koreksi data resmi |
| AINA NAZWA | KELAS 7.08 | `0143380307` | `3138813152` | Koreksi data resmi |
| ANJENG AGUSTINA | KELAS 7.08 | `0131268909` | `0148336984` | Koreksi data resmi |
| CANDRA MAULANA | KELAS 7.08 | `3146082892202101005` | `3146082892` | Koreksi data resmi |
| NUR ROHMAN | KELAS 7.08 | `3149448095` | `3140106821` | Koreksi data resmi |
| RIZKI RAFAEL | KELAS 7.08 | `153804015` | `0153804015` | Koreksi data resmi |
| SAHRUL RAMADHAN | KELAS 7.08 | `3138514828` | `0132084301` | Koreksi data resmi |
| WINDA CALISTA | KELAS 7.08 | `3144855291` | `3140112701` | Koreksi data resmi |
| DARNAWI | KELAS 7.09 | `3145268368` | `3146773216` | Koreksi data resmi |
| EIREN LARISA PUTRI | KELAS 7.09 | `0132705827` | `0132705828` | Koreksi data resmi |
| MUHAMAD DWI RAFA | KELAS 7.09 | `3133280489` | `3133280496` | Koreksi data resmi |
| MUHAMAD RAIHAN | KELAS 7.09 | `3143346218` | `3143346213` | Resolusi tabrakan (Raihan vs Riski) |
| SURYA PERMANA | KELAS 7.09 | `3141818082` | `0139853729` | Koreksi data resmi |
| ISNAENI NUR HIKMAH | KELAS 7.10 | `0131854395` | `0143380307` | Koreksi data resmi |
| KIANDRA REISYA PUTRA | KELAS 7.10 | `3143409671` | `3133612485` | Koreksi data resmi |
| NABILA SEPTIANI PUTRI | KELAS 7.10 | `202101023` | `3139972818` | Koreksi data resmi |
| RIAN | KELAS 7.10 | `3138540231` | `3143796759` | Koreksi data resmi |
| SATRIA SANJAYA | KELAS 7.10 | `3133612485` | `3131045996` | Koreksi data resmi |
| SITI NURAENI | KELAS 7.10 | `3144900855` | `3145268368` | Koreksi data resmi |
| SYAHRIATUN NABAWIYAH | KELAS 7.10 | `3138860010` | `3137556520` | Koreksi data resmi |
| TINI LESTARI | KELAS 7.10 | `3139300414` | `3142849277` | Koreksi data resmi |
| ALVIANSYAH | KELAS 7.11 | `3147965187` | `0139835614` | Koreksi data resmi |
| AUFA RIJAL RAIZ | KELAS 7.11 | `0145011754` | `3139553909` | Koreksi data resmi |
| IRMA OKTAVIANI | KELAS 7.11 | `0155701501` | `3138167504` | Koreksi data resmi |
| RAKA PRATAMA | KELAS 7.11 | `3138813152` | `3135960225` | Koreksi data resmi |
| RISMA YUSRIYAH | KELAS 7.11 | `3145314542` | `3146341985` | Koreksi data resmi |
| YUKI YANUAR | KELAS 7.11 | `3138017899` | `3147685200` | Koreksi data resmi |
| AMAL SALEH | KELAS 7.01 | `134628997` | `0134628997` | Koreksi data resmi |
| MUHAMAD RISKI | KELAS 7.04 | `3143346213` | `3143346212` | Resolusi tabrakan (Raihan vs Riski) |
| RAYYAN LAIL ASAWAL | KELAS 7.09 | `3139306044` | `3129327435` | Resolusi tabrakan (Rayyan vs Anas) |
| ARGA RAMHAZ RUKMANA | KELAS 7.01 | `0139170214` | `0139170213` | Resolusi tabrakan (Cintani vs Arga) |
| JAESEN ELIANIUS LIMBONG | KELAS 7.02 | `139974576` | `0139974576` | Koreksi data resmi |
| RIZAL HAQ SYAHRONI | KELAS 7.03 | `135871165` | `0135871165` | Koreksi data resmi |
| AHMAD AZAM FATHUR RAHMAN | KELAS 7.04 | `0135792089` | `0135792088` | Koreksi data resmi |

## 📝 2. Daftar Siswa Baru / Baris Ditambahkan
| Nama Siswa | Kelas | NIS | NISN |
| --- | --- | --- | --- |

## 🗑️ 3. Daftar Baris Duplikat Dihapus (Deduplikasi)
Menghilangkan baris identik ganda yang tersimpan secara keliru di CSV asal:

| Nama Siswa | Kelas | NIS | NISN |
| --- | --- | --- | --- |
| FIRMAN ELNATHAN SIANIPAR | KELAS 7.10 | `262707465` | `3133215265` |
| DARA AUXYLIA PRADITHA | KELAS 7.11 | `262707462` | `3139445425` |
