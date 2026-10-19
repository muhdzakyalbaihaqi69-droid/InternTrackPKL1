# InternTrack — Sistem Informasi Monitoring PKL

Aplikasi web (PHP + MySQL/MariaDB + HTML/CSS/JavaScript) untuk memantau kegiatan Praktik Kerja Lapangan siswa.
SMK Hidayah Semarang — Skema Junior Coder, Rekayasa Perangkat Lunak.

## Cara menjalankan (XAMPP / Laragon)
1. Salin folder `InternTrack` ke `htdocs` (XAMPP) atau `www` (Laragon).
2. Jalankan **Apache** dan **MySQL**.
3. Buka phpMyAdmin → tab **Import** → pilih `database/interntrack.sql` → Go.
   (File ini membuat database `interntrack`, seluruh tabel, relasi, dan data dummy.)
4. Jika user/password MySQL Anda bukan `root` / kosong, ubah `config/database.php`.
5. Buka `http://localhost/InternTrack/`

## Akun demo
| Peran | Username | Password |
|---|---|---|
| Admin / Pembimbing | `admin` | `password` |
| Siswa (25 akun) | NIS `2526001` s.d. `2526025` | `password` |

Siswa baru yang ditambah admin: username = NIS, password awal = NIS (jika dikosongkan).
Catatan: siswa dengan NIS 2526001–2526010 sudah memiliki penempatan PKL; 2526011–2526025 belum.

## Struktur
```
InternTrack/
├── config/    database.php, app.php (koneksi, session, fungsi bantu, logika status PKL)
├── assets/    css/style.css, js/app.js
├── admin/     dashboard, siswa, perusahaan, pembimbing, penempatan, jurnal, absensi, monitoring
├── siswa/     dashboard, profil, tempat_pkl, jurnal, absensi, status, monitoring
├── auth/      login.php, logout.php
├── laporan/   siswa.php, kehadiran.php, monitoring.php
├── includes/  header.php, footer.php
├── database/  interntrack.sql
└── index.php
```
