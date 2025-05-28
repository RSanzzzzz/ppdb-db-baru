-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 28, 2025 at 06:20 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `newdb-ppdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nama_pengguna` varchar(50) NOT NULL COMMENT 'Username untuk login admin',
  `kata_sandi` varchar(255) NOT NULL COMMENT 'Password admin yang sudah di-hash',
  `email` varchar(100) NOT NULL COMMENT 'Email admin',
  `nama` varchar(100) DEFAULT NULL COMMENT 'Nama lengkap admin',
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Tanggal pembuatan akun admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `nama_pengguna`, `kata_sandi`, `email`, `nama`, `tanggal_dibuat`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@ppdb.sch.id', 'Administrator PPDB', '2025-05-28 03:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

CREATE TABLE `applicants` (
  `id` int(11) NOT NULL,
  `id_pengguna` int(11) NOT NULL COMMENT 'ID pengguna yang mendaftar',
  `nomor_pendaftaran` varchar(20) NOT NULL COMMENT 'Nomor pendaftaran unik',
  `nama_lengkap` varchar(100) NOT NULL COMMENT 'Nama lengkap siswa',
  `nisn` varchar(20) NOT NULL COMMENT 'NISN siswa',
  `tempat_lahir` varchar(100) NOT NULL COMMENT 'Tempat lahir siswa',
  `tanggal_lahir` date NOT NULL COMMENT 'Tanggal lahir siswa',
  `jenis_kelamin` enum('laki-laki','perempuan') NOT NULL COMMENT 'Jenis kelamin siswa',
  `agama` varchar(20) NOT NULL COMMENT 'Agama siswa',
  `alamat` text NOT NULL COMMENT 'Alamat lengkap siswa',
  `telepon` varchar(20) NOT NULL COMMENT 'Nomor telepon siswa',
  `email` varchar(100) NOT NULL COMMENT 'Email siswa',
  `nama_ayah` varchar(100) NOT NULL COMMENT 'Nama lengkap ayah',
  `pekerjaan_ayah` varchar(100) NOT NULL COMMENT 'Pekerjaan ayah',
  `nama_ibu` varchar(100) NOT NULL COMMENT 'Nama lengkap ibu',
  `pekerjaan_ibu` varchar(100) NOT NULL COMMENT 'Pekerjaan ibu',
  `telepon_orangtua` varchar(20) NOT NULL COMMENT 'Nomor telepon orang tua/wali',
  `nama_sekolah` varchar(100) NOT NULL COMMENT 'Nama sekolah asal',
  `alamat_sekolah` text NOT NULL COMMENT 'Alamat sekolah asal',
  `tahun_lulus` varchar(4) NOT NULL COMMENT 'Tahun lulus dari sekolah asal',
  `file_ijazah` varchar(255) NOT NULL COMMENT 'Path file ijazah/surat keterangan lulus',
  `file_akta_kelahiran` varchar(255) NOT NULL COMMENT 'Path file akta kelahiran',
  `file_kartu_keluarga` varchar(255) NOT NULL COMMENT 'Path file kartu keluarga',
  `file_foto` varchar(255) NOT NULL COMMENT 'Path file pas foto',
  `file_ijazah_sd` varchar(255) DEFAULT NULL COMMENT 'Path file ijazah SD/MI',
  `file_ijazah_mda` varchar(255) DEFAULT NULL COMMENT 'Path file ijazah MDA',
  `file_skhun` varchar(255) DEFAULT NULL COMMENT 'Path file SKHUN',
  `file_nisn` varchar(255) DEFAULT NULL COMMENT 'Path file NISN',
  `file_ktp_orangtua` varchar(255) DEFAULT NULL COMMENT 'Path file KTP orang tua/wali',
  `file_kartu_sosial` varchar(255) DEFAULT NULL COMMENT 'Path file kartu KIS/KIP/PKH',
  `file_surat_lulus` varchar(255) DEFAULT NULL COMMENT 'Path file surat keterangan lulus',
  `status` enum('menunggu','terverifikasi','diterima','ditolak') DEFAULT 'menunggu' COMMENT 'Status pendaftaran',
  `catatan_admin` text DEFAULT NULL COMMENT 'Catatan dari admin',
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Tanggal pendaftaran dibuat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama_pengguna` varchar(50) NOT NULL COMMENT 'Username untuk login',
  `kata_sandi` varchar(255) NOT NULL COMMENT 'Password yang sudah di-hash',
  `email` varchar(100) NOT NULL COMMENT 'Email pengguna',
  `nama` varchar(100) DEFAULT NULL COMMENT 'Nama lengkap pengguna',
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Tanggal pembuatan akun'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama_pengguna`, `kata_sandi`, `email`, `nama`, `tanggal_dibuat`) VALUES
(1, 'demo', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'demo@example.com', 'Demo User', '2025-05-28 04:08:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pengguna` (`nama_pengguna`);

--
-- Indexes for table `applicants`
--
ALTER TABLE `applicants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_pendaftaran` (`nomor_pendaftaran`),
  ADD KEY `id_pengguna` (`id_pengguna`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_pengguna` (`nama_pengguna`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `applicants`
--
ALTER TABLE `applicants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applicants`
--
ALTER TABLE `applicants`
  ADD CONSTRAINT `applicants_ibfk_1` FOREIGN KEY (`id_pengguna`) REFERENCES `pengguna` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
