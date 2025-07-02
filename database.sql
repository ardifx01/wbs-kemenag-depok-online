CREATE DATABASE IF NOT EXISTS `wbs_kemenag`;
USE `wbs_kemenag`;

-- Tabel untuk menyimpan laporan
CREATE TABLE `laporan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_code` varchar(20) NOT NULL,
  `kategori` varchar(100) NOT NULL,
  `judul_laporan` varchar(255) NOT NULL,
  `isi_laporan` text NOT NULL,
  `bukti_pendukung` varchar(255) DEFAULT NULL,
  `nama_pelapor` varchar(255) DEFAULT NULL,
  `email_pelapor` varchar(255) DEFAULT NULL,
  `nomor_hp` varchar(20) DEFAULT NULL,
  `is_anonim` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'Baru',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_code` (`tracking_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel untuk admin
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel untuk log aktivitas admin (opsional tapi sangat disarankan)
CREATE TABLE `log_aktivitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `aktivitas` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabel untuk tindak lanjut atau komentar admin
CREATE TABLE `tindak_lanjut` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `laporan_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `komentar` text NOT NULL,
  `file_tambahan` varchar(255) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `laporan_id` (`laporan_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Tambahkan admin default (password: 'admin123')
-- PENTING: Ganti password ini setelah login pertama kali!
INSERT INTO `admin` (`nama`, `username`, `password`) VALUES
('Admin Utama', 'admin', '$2y$10$vslsb4/iC8qC3C1e.9VnVeEKB.YyA3P8YJt8Y7iZ5E9e8.j8h.s1S');