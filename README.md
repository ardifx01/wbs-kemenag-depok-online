# WBS Kemenag Depok Online

Web-based **Whistleblowing System (WBS)** untuk Kantor Kementerian Agama Kota Depok.  
Menyediakan sistem pelaporan pelanggaran yang **aman**, **anonim**, dan mudah dilacak, dengan dashboard admin untuk memonitor dan menindaklanjuti laporan.

---

##  Fitur Utama
- Form pelaporan publik berbasis web dengan opsi **anonimitas**.
- Lacak status laporan dengan **kode unik**.
- Dashboard admin:
  - Ringkasan statistik laporan
  - Visualisasi data (contoh: Chart.js)
  - Filter tanggal, paginate, dan ekspor ke Excel.
  - Detil laporan lengkap dengan timeline tindak lanjut.

---

##  Teknologi (Asumsi Umum — sesuikan jika menggunakan PHP + MySQL)
- **Backend**: PHP  
- **Database**: MySQL (via `config.php`, `database.sql`)  
- **Template/UI**: PHP + CSS (folder `templates/`)  
- **Uploads**: disimpan di `uploads/`  
- **Logika pelaporan**: ada di `buat-laporan.php`, `proses_laporan.php`, `lacak.php`, `statistik.php`

---

##  Setup & Deploy (Local / Web Hosting Standar)

1. **Clone repo**
   ```bash
   git clone https://github.com/ardifx01/wbs-kemenag-depok-online.git
   cd wbs-kemenag-depok-online
   ```

2. **Import Database**
 Buka database.sql, import ke MySQL:
 ```
   CREATE DATABASE wbs;
USE wbs;
   SOURCE database.sql;
   ```

3. **Konfigurasi**
   Edit config.php sesuai server:
 ```
   $host = 'localhost';
$user = 'dbuser';
$pass = 'dbpass';
$db = 'wbs';
   ```
---
**Struktur Direktori**
```
wbs-kemenag-depok-online/
├── assets/css/ – stylesheet tampilan
├── templates/ – layout HTML umum
├── uploads/ – file yang di-upload pelapor
├── .htaccess – keamanan atau redirect
├── index.php – landing page / pengantar
├── buat-laporan.php – form laporan
├── proses_laporan.php – penyimpanan laporan
├── lacak.php – halaman tracking
├── statistik.php – dashboard statistik
├── config.php – pengaturan koneksi DB
├── database.sql – script tabel dan struktur DB
├── favicon.ico
└── random.txt (auto-commit/temp file; bisa dihapus)
```
