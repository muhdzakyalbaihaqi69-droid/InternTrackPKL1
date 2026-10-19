-- ============================================================
-- InternTrack - Sistem Informasi Monitoring PKL
-- Database : MySQL / MariaDB
-- Akun demo: admin / password  |  siswa (NIS 2526001 dst.) / password
-- ============================================================
CREATE DATABASE IF NOT EXISTS interntrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE interntrack;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS monitoring, absensi, jurnal, penempatan, pembimbing, perusahaan, siswa, users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id_user    INT AUTO_INCREMENT PRIMARY KEY,
  username   VARCHAR(50)  NOT NULL UNIQUE,
  password   VARCHAR(255) NOT NULL,
  nama       VARCHAR(100) NOT NULL,
  role       ENUM('admin','siswa') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE siswa (
  id_siswa INT AUTO_INCREMENT PRIMARY KEY,
  id_user  INT NOT NULL UNIQUE,
  nis      VARCHAR(20)  NOT NULL UNIQUE,
  nama     VARCHAR(100) NOT NULL,
  kelas    VARCHAR(20),
  jurusan  VARCHAR(60),
  no_hp    VARCHAR(20),
  alamat   VARCHAR(255),
  CONSTRAINT fk_siswa_user FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE perusahaan (
  id_perusahaan   INT AUTO_INCREMENT PRIMARY KEY,
  nama_perusahaan VARCHAR(120) NOT NULL,
  alamat          VARCHAR(255),
  bidang          VARCHAR(100),
  kontak          VARCHAR(30)
) ENGINE=InnoDB;

CREATE TABLE pembimbing (
  id_pembimbing INT AUTO_INCREMENT PRIMARY KEY,
  nip           VARCHAR(25),
  nama          VARCHAR(100) NOT NULL,
  no_hp         VARCHAR(20),
  bidang        VARCHAR(80)
) ENGINE=InnoDB;

CREATE TABLE penempatan (
  id_penempatan INT AUTO_INCREMENT PRIMARY KEY,
  id_siswa      INT NOT NULL UNIQUE,
  id_perusahaan INT NOT NULL,
  id_pembimbing INT NOT NULL,
  tgl_mulai     DATE NOT NULL,
  tgl_selesai   DATE NOT NULL,
  status        ENUM('Belum Mulai','Aktif','Selesai') NOT NULL DEFAULT 'Belum Mulai',
  CONSTRAINT fk_pen_siswa      FOREIGN KEY (id_siswa)      REFERENCES siswa(id_siswa)           ON DELETE CASCADE,
  CONSTRAINT fk_pen_perusahaan FOREIGN KEY (id_perusahaan) REFERENCES perusahaan(id_perusahaan) ON DELETE RESTRICT,
  CONSTRAINT fk_pen_pembimbing FOREIGN KEY (id_pembimbing) REFERENCES pembimbing(id_pembimbing) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE jurnal (
  id_jurnal     INT AUTO_INCREMENT PRIMARY KEY,
  id_penempatan INT NOT NULL,
  tanggal       DATE NOT NULL,
  kegiatan      TEXT NOT NULL,
  hasil         TEXT,
  kendala       TEXT,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_jurnal_pen FOREIGN KEY (id_penempatan) REFERENCES penempatan(id_penempatan) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE absensi (
  id_absensi    INT AUTO_INCREMENT PRIMARY KEY,
  id_penempatan INT NOT NULL,
  tanggal       DATE NOT NULL,
  status        ENUM('Hadir','Izin','Sakit','Alpa') NOT NULL,
  keterangan    VARCHAR(255),
  UNIQUE KEY uq_absen_hari (id_penempatan, tanggal),
  CONSTRAINT fk_absen_pen FOREIGN KEY (id_penempatan) REFERENCES penempatan(id_penempatan) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE monitoring (
  id_monitoring INT AUTO_INCREMENT PRIMARY KEY,
  id_penempatan INT NOT NULL,
  id_pembimbing INT NULL,
  tanggal       DATE NOT NULL,
  catatan       TEXT NOT NULL,
  nilai         TINYINT UNSIGNED NOT NULL,
  rekomendasi   TEXT,
  CONSTRAINT chk_nilai CHECK (nilai BETWEEN 0 AND 100),
  CONSTRAINT fk_mon_pen FOREIGN KEY (id_penempatan) REFERENCES penempatan(id_penempatan) ON DELETE CASCADE,
  CONSTRAINT fk_mon_pbb FOREIGN KEY (id_pembimbing) REFERENCES pembimbing(id_pembimbing) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ===================== DATA DUMMY =====================

INSERT INTO users (id_user, username, password, nama, role) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator PKL', 'admin'),
(2, '2526001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Fauzi', 'siswa'),
(3, '2526002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Aulia Rahma', 'siswa'),
(4, '2526003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bagas Prasetyo', 'siswa'),
(5, '2526004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Citra Lestari', 'siswa'),
(6, '2526005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dimas Saputra', 'siswa'),
(7, '2526006', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eka Putri Wulandari', 'siswa'),
(8, '2526007', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Farhan Maulana', 'siswa'),
(9, '2526008', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gita Permata', 'siswa'),
(10, '2526009', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hendra Kurniawan', 'siswa'),
(11, '2526010', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Intan Maharani', 'siswa'),
(12, '2526011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Joko Santoso', 'siswa'),
(13, '2526012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kartika Sari', 'siswa'),
(14, '2526013', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Luthfi Hakim', 'siswa'),
(15, '2526014', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maya Anggraini', 'siswa'),
(16, '2526015', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Naufal Ramadhan', 'siswa'),
(17, '2526016', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Olivia Putri', 'siswa'),
(18, '2526017', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Putra Wijaya', 'siswa'),
(19, '2526018', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Qonita Zahra', 'siswa'),
(20, '2526019', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rizky Firmansyah', 'siswa'),
(21, '2526020', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Nurhaliza', 'siswa'),
(22, '2526021', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tegar Pratama', 'siswa'),
(23, '2526022', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Umi Kulsum', 'siswa'),
(24, '2526023', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vina Oktaviani', 'siswa'),
(25, '2526024', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Wahyu Nugroho', 'siswa'),
(26, '2526025', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Yoga Aditya', 'siswa');

INSERT INTO siswa (id_siswa, id_user, nis, nama, kelas, jurusan, no_hp, alamat) VALUES
(1, 2, '2526001', 'Ahmad Fauzi', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '085700000001', 'Semarang'),
(2, 3, '2526002', 'Aulia Rahma', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '085700000002', 'Semarang'),
(3, 4, '2526003', 'Bagas Prasetyo', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '085700000003', 'Semarang'),
(4, 5, '2526004', 'Citra Lestari', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '085700000004', 'Semarang'),
(5, 6, '2526005', 'Dimas Saputra', 'XII RPL 1', 'Rekayasa Perangkat Lunak', '085700000005', 'Semarang'),
(6, 7, '2526006', 'Eka Putri Wulandari', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '085700000006', 'Semarang'),
(7, 8, '2526007', 'Farhan Maulana', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '085700000007', 'Semarang'),
(8, 9, '2526008', 'Gita Permata', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '085700000008', 'Semarang'),
(9, 10, '2526009', 'Hendra Kurniawan', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '085700000009', 'Semarang'),
(10, 11, '2526010', 'Intan Maharani', 'XII RPL 2', 'Rekayasa Perangkat Lunak', '085700000010', 'Semarang'),
(11, 12, '2526011', 'Joko Santoso', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '085700000011', 'Semarang'),
(12, 13, '2526012', 'Kartika Sari', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '085700000012', 'Semarang'),
(13, 14, '2526013', 'Luthfi Hakim', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '085700000013', 'Semarang'),
(14, 15, '2526014', 'Maya Anggraini', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '085700000014', 'Semarang'),
(15, 16, '2526015', 'Naufal Ramadhan', 'XII TKJ 1', 'Teknik Komputer dan Jaringan', '085700000015', 'Semarang'),
(16, 17, '2526016', 'Olivia Putri', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '085700000016', 'Semarang'),
(17, 18, '2526017', 'Putra Wijaya', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '085700000017', 'Semarang'),
(18, 19, '2526018', 'Qonita Zahra', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '085700000018', 'Semarang'),
(19, 20, '2526019', 'Rizky Firmansyah', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '085700000019', 'Semarang'),
(20, 21, '2526020', 'Siti Nurhaliza', 'XII TKJ 2', 'Teknik Komputer dan Jaringan', '085700000020', 'Semarang'),
(21, 22, '2526021', 'Tegar Pratama', 'XII MM 1', 'Multimedia', '085700000021', 'Semarang'),
(22, 23, '2526022', 'Umi Kulsum', 'XII MM 1', 'Multimedia', '085700000022', 'Semarang'),
(23, 24, '2526023', 'Vina Oktaviani', 'XII MM 1', 'Multimedia', '085700000023', 'Semarang'),
(24, 25, '2526024', 'Wahyu Nugroho', 'XII MM 1', 'Multimedia', '085700000024', 'Semarang'),
(25, 26, '2526025', 'Yoga Aditya', 'XII MM 1', 'Multimedia', '085700000025', 'Semarang');

INSERT INTO perusahaan (id_perusahaan, nama_perusahaan, alamat, bidang, kontak) VALUES
(1, 'PT Cipta Karya Teknologi', 'Jl. Pandanaran No. 21, Semarang', 'Pengembangan Perangkat Lunak', '024-8311001'),
(2, 'CV Mitra Jaringan Utama', 'Jl. Pemuda No. 110, Semarang', 'Jaringan dan Infrastruktur IT', '024-8311002'),
(3, 'PT Nusantara Digital Solusi', 'Jl. Gajahmada No. 45, Semarang', 'Konsultan Teknologi Informasi', '024-8311003'),
(4, 'Kreasi Media Semarang', 'Jl. Veteran No. 8, Semarang', 'Multimedia dan Desain Grafis', '024-8311004'),
(5, 'PT Data Prima Mandiri', 'Jl. MT Haryono No. 77, Semarang', 'Pengolahan Data', '024-8311005'),
(6, 'Diskominfo Kota Semarang', 'Jl. Pemuda No. 148, Semarang', 'Pemerintahan - Teknologi Informasi', '024-8311006'),
(7, 'PT Kodeku Inovasi', 'Jl. Setiabudi No. 33, Semarang', 'Startup Aplikasi Mobile', '024-8311007'),
(8, 'CV Bintang Net', 'Jl. Ki Mangunsarkoro No. 12, Semarang', 'Penyedia Layanan Internet', '024-8311008'),
(9, 'Toko Elektro Jaya Abadi', 'Jl. Karangrejo Raya No. 5, Semarang', 'Perangkat Keras dan Servis Komputer', '024-8311009'),
(10, 'PT Sinar Visual Indonesia', 'Jl. Sultan Agung No. 90, Semarang', 'Produksi Video dan Animasi', '024-8311010');

INSERT INTO pembimbing (id_pembimbing, nip, nama, no_hp, bidang) VALUES
(1, '197805122005011001', 'Drs. Sugeng Riyadi', '081200000001', 'Produktif RPL'),
(2, '198203172008012002', 'Sri Wahyuni, S.Kom.', '081200000002', 'Produktif RPL'),
(3, '198507212010011003', 'Budi Hartono, S.T.', '081200000003', 'Produktif TKJ'),
(4, '198811042011012004', 'Rina Marlina, S.Pd.', '081200000004', 'Produktif Multimedia'),
(5, '197912302006041005', 'Agus Setiawan, M.Kom.', '081200000005', 'Produktif RPL'),
(6, '199001152014022006', 'Dewi Kusumawati, S.Kom.', '081200000006', 'Basis Data'),
(7, '198406092009031007', 'Teguh Wibowo, S.T.', '081200000007', 'Produktif TKJ'),
(8, '199103252015012008', 'Lilis Handayani, S.Pd.', '081200000008', 'Produktif Multimedia'),
(9, '198009182007011009', 'Eko Prasetyo, M.T.', '081200000009', 'Produktif TKJ'),
(10, '199205082017022010', 'Nanda Permatasari, S.Kom.', '081200000010', 'Pemrograman Web');

INSERT INTO penempatan (id_penempatan, id_siswa, id_perusahaan, id_pembimbing, tgl_mulai, tgl_selesai, status) VALUES
(1, 1, 1, 1, '2026-09-01', '2026-12-19', 'Aktif'),
(2, 2, 2, 2, '2026-09-01', '2026-12-19', 'Aktif'),
(3, 3, 3, 3, '2026-09-01', '2026-12-19', 'Aktif'),
(4, 4, 4, 4, '2026-09-01', '2026-12-19', 'Aktif'),
(5, 5, 5, 5, '2026-09-01', '2026-12-19', 'Aktif'),
(6, 6, 6, 6, '2026-09-01', '2026-10-17', 'Selesai'),
(7, 7, 7, 7, '2026-09-01', '2026-12-19', 'Aktif'),
(8, 8, 8, 8, '2026-09-01', '2026-12-19', 'Aktif'),
(9, 9, 9, 9, '2026-11-02', '2027-01-30', 'Belum Mulai'),
(10, 10, 10, 10, '2026-11-02', '2027-01-30', 'Belum Mulai');

INSERT INTO jurnal (id_penempatan, tanggal, kegiatan, hasil, kendala) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Mengenal lingkungan kerja', 'Memahami alur kerja dan aturan perusahaan', 'Belum hafal struktur tim'),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Instalasi dan konfigurasi tools kerja', 'Tools kerja siap digunakan', 'Koneksi internet sempat terputus'),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Membuat desain database', 'ERD selesai dibuat', 'Masih kesulitan menentukan relasi tabel'),
(1, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 'Membuat halaman login dan dashboard', 'Halaman berfungsi dengan baik', 'Validasi form masih perlu diperbaiki'),
(2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Mengenal lingkungan kerja', 'Memahami alur kerja dan aturan perusahaan', 'Belum hafal struktur tim'),
(2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Instalasi dan konfigurasi tools kerja', 'Tools kerja siap digunakan', 'Koneksi internet sempat terputus'),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Membuat desain database', 'ERD selesai dibuat', 'Masih kesulitan menentukan relasi tabel'),
(2, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 'Membuat halaman login dan dashboard', 'Halaman berfungsi dengan baik', 'Validasi form masih perlu diperbaiki'),
(3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Mengenal lingkungan kerja', 'Memahami alur kerja dan aturan perusahaan', 'Belum hafal struktur tim'),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Instalasi dan konfigurasi tools kerja', 'Tools kerja siap digunakan', 'Koneksi internet sempat terputus'),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Membuat desain database', 'ERD selesai dibuat', 'Masih kesulitan menentukan relasi tabel'),
(3, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 'Membuat halaman login dan dashboard', 'Halaman berfungsi dengan baik', 'Validasi form masih perlu diperbaiki'),
(4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Mengenal lingkungan kerja', 'Memahami alur kerja dan aturan perusahaan', 'Belum hafal struktur tim'),
(4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Instalasi dan konfigurasi tools kerja', 'Tools kerja siap digunakan', 'Koneksi internet sempat terputus'),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Membuat desain database', 'ERD selesai dibuat', 'Masih kesulitan menentukan relasi tabel'),
(4, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 'Membuat halaman login dan dashboard', 'Halaman berfungsi dengan baik', 'Validasi form masih perlu diperbaiki'),
(5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Mengenal lingkungan kerja', 'Memahami alur kerja dan aturan perusahaan', 'Belum hafal struktur tim'),
(5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Instalasi dan konfigurasi tools kerja', 'Tools kerja siap digunakan', 'Koneksi internet sempat terputus'),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Membuat desain database', 'ERD selesai dibuat', 'Masih kesulitan menentukan relasi tabel'),
(5, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 'Membuat halaman login dan dashboard', 'Halaman berfungsi dengan baik', 'Validasi form masih perlu diperbaiki'),
(6, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Mengenal lingkungan kerja', 'Memahami alur kerja dan aturan perusahaan', 'Belum hafal struktur tim'),
(6, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Instalasi dan konfigurasi tools kerja', 'Tools kerja siap digunakan', 'Koneksi internet sempat terputus'),
(6, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Membuat desain database', 'ERD selesai dibuat', 'Masih kesulitan menentukan relasi tabel'),
(6, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 'Membuat halaman login dan dashboard', 'Halaman berfungsi dengan baik', 'Validasi form masih perlu diperbaiki'),
(7, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Mengenal lingkungan kerja', 'Memahami alur kerja dan aturan perusahaan', 'Belum hafal struktur tim'),
(7, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Instalasi dan konfigurasi tools kerja', 'Tools kerja siap digunakan', 'Koneksi internet sempat terputus'),
(7, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 'Membuat desain database', 'ERD selesai dibuat', 'Masih kesulitan menentukan relasi tabel'),
(8, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Mengenal lingkungan kerja', 'Memahami alur kerja dan aturan perusahaan', 'Belum hafal struktur tim'),
(8, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Instalasi dan konfigurasi tools kerja', 'Tools kerja siap digunakan', 'Koneksi internet sempat terputus'),
(8, DATE_SUB(CURDATE(), INTERVAL 0 DAY), 'Membuat desain database', 'ERD selesai dibuat', 'Masih kesulitan menentukan relasi tabel');

INSERT INTO absensi (id_penempatan, tanggal, status, keterangan) VALUES
(1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Hadir', ''),
(1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Hadir', ''),
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Hadir', ''),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Hadir', ''),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Hadir', ''),
(2, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Hadir', ''),
(2, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Hadir', ''),
(2, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Hadir', ''),
(2, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Hadir', ''),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Izin', 'Keperluan keluarga'),
(3, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Hadir', ''),
(3, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Hadir', ''),
(3, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Hadir', ''),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Hadir', ''),
(3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Sakit', 'Surat dokter'),
(4, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Hadir', ''),
(4, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Hadir', ''),
(4, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Hadir', ''),
(4, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Sakit', 'Surat dokter'),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Alpa', 'Tanpa keterangan'),
(5, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Hadir', ''),
(5, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Hadir', ''),
(5, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Sakit', 'Surat dokter'),
(5, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Alpa', 'Tanpa keterangan'),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Izin', 'Keperluan keluarga'),
(6, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Hadir', ''),
(6, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Hadir', ''),
(6, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Hadir', ''),
(6, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Hadir', ''),
(6, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Hadir', '');

INSERT INTO monitoring (id_penempatan, id_pembimbing, tanggal, catatan, nilai, rekomendasi) VALUES
(1, 1, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Siswa mampu berkomunikasi dengan baik dan dapat menyelesaikan tugas yang diberikan industri.', 85, 'Pertahankan kedisiplinan dan tingkatkan inisiatif.'),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 9 DAY), 'Siswa aktif bertanya dan cepat beradaptasi dengan tim.', 82, 'Perdalam pemahaman basis data.'),
(3, 3, DATE_SUB(CURDATE(), INTERVAL 8 DAY), 'Pekerjaan teknis baik, namun dokumentasi kegiatan perlu dirapikan.', 78, 'Biasakan menulis laporan harian secara rutin.'),
(4, 4, DATE_SUB(CURDATE(), INTERVAL 7 DAY), 'Kehadiran kurang stabil, hasil kerja cukup baik.', 70, 'Perlu pendampingan terkait kedisiplinan hadir.'),
(5, 5, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 'Sering tidak hadir sehingga progres pekerjaan tertinggal.', 55, 'Dilakukan pembinaan dan komunikasi dengan orang tua.'),
(6, 6, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Siswa menyelesaikan seluruh tugas dengan sangat baik.', 92, 'Direkomendasikan sebagai teladan bagi siswa lain.'),
(7, 7, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 'Mampu bekerja mandiri pada tugas dasar.', 80, 'Tingkatkan kecepatan dan ketelitian.'),
(8, 8, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'Komunikasi dengan rekan kerja baik.', 83, 'Mulai dilatih mengerjakan tugas yang lebih kompleks.'),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Monitoring lanjutan: kualitas kerja meningkat dan siswa mulai dipercaya memegang modul sendiri.', 88, 'Siap diberi tanggung jawab lebih besar.'),
(2, 2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'Monitoring lanjutan: kemampuan teknis meningkat pesat.', 86, 'Lanjutkan, fokus pada pengujian aplikasi.');
