-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 16, 2026 at 01:26 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `disdik_sumenep`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `name`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@disdiksumenep.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-06-30 07:27:55', '2026-06-30 07:27:55'),
(2, 'desy', 'desy@gmail.com', '$2y$10$ZBkzM/Or4JWs7vfTEAh3Ge3Nlxnf8C9vk3s1BtJdgjLNG2mlqAjY6', '2026-07-06 02:37:26', '2026-07-06 02:37:26'),
(3, 'admin', 'admin@gmail.com', '$2y$10$T6rXpAwvY83JaIGl25B1de9w7eDGwybYMAcAcbouqZnCAnDco4VM2', '2026-07-14 01:39:14', '2026-07-14 01:39:14');

-- --------------------------------------------------------

--
-- Table structure for table `berita`
--

CREATE TABLE `berita` (
  `id` int UNSIGNED NOT NULL,
  `admin_id` int UNSIGNED DEFAULT NULL,
  `kategori_id` int UNSIGNED DEFAULT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draf','terbit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draf',
  `tanggal_publish` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `berita`
--

INSERT INTO `berita` (`id`, `admin_id`, `kategori_id`, `judul`, `slug`, `isi`, `gambar`, `status`, `tanggal_publish`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'Siswa SMPN 1 Sumenep Raih Juara Tahfidz se-Jawa Timur', 'siswa-smpn-1-sumenep-raih-juara-osn-matematika', 'Media Center, Senin ( 23/10 ) Siswi SMPN 1 Sumenep, Neyla Hilyatal Fauziyyah kelas (8-8), berhasil meraih Juara 1 Lomba Tahfidz Tingkat SMP/MTs Kompetisi Seni Islami 2023 se-Jawa Timur yang diselenggarakan oleh Himpunan Mahasiswa IPA Universitas Negeri Surabaya (Unesa).\r\n\r\nKepala SMPN 1 Sumenep Syaiful Rahman Dasuki, mengungkapkan, sangat bersyukur dan menyampaikan apresiasi atas prestasi yang diraih peserta didiknya tersebut. Pihaknya mengaku akan terus mendukung dan mensupport siswa-siswinya yang memiliki prestasi baik akademik maupun non akademik.Panduan Kota & Daerah\r\n\r\n\"Syukurlah salah satu siswi terbaik kami, Neyla Hilyatal Fauziyyah bisa meriah juara 1 Tahfidz se-Jawa Timur, syukur nantinya berlanjut ke tingkat nasional,\" ujar Syaiful Rahman, Senin (23/10/2023).\r\n\r\nDikatakan, sebagai Kepala sekolah di SMP Negeri 1 Sumenep, yang merupakan sekolah penggerak tentunya memang layak memiliki banyak prestasi, bahkan dalam setahun ini sudah memiliki seratus lebih prestasi mulai tingkat kabupaten, provinsi hingga nasional. Hal tersebut diakui Syaiful berkat bimbingan guru-guru pembina dan pelatih berbagai bidang akademik maupun non akademik.\r\n\r\nSetidaknya, beberapa guru penggerak yang ada di sekolahnya juga memiliki andil dalam memacu guru lainnya dan siswa yang ada di SMPN 1 Sumenep ini, untuk terus meningkatkan kemampuan yang dimiliki. Sehingga, berkat kekompakan dan kebersamaan, termasuk para orang tua siswa  yang saling mendukung menjadikan sekolah terfavorit di kabupaten Sumenep ini sukses meraih banyak prestasi.\r\n\r\n\"Tentunya dukungan dari semuanya sangat diharapkan untuk memberikan semangat bagi sekolah dalam meningkatkan prestasi sekolah dan siswa,\" tambahnya. ( Ren, Fer )', 'img/berita/berita_1783385851_378.jpeg', 'terbit', '2026-07-07', '2026-07-07 00:57:31', '2026-07-07 01:08:30');

-- --------------------------------------------------------

--
-- Table structure for table `bidang`
--

CREATE TABLE `bidang` (
  `id` int UNSIGNED NOT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tugas` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fungsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bidang`
--

INSERT INTO `bidang` (`id`, `nama`, `tugas`, `fungsi`, `created_at`, `updated_at`) VALUES
(1, 'Sekretariat', 'Mengoordinasikan administrasi umum dinas', 'Perencanaan, keuangan, dan umum', '2026-06-30 07:27:55', '2026-06-30 07:27:55'),
(2, 'Bidang Pembinaan SD', 'Membina satuan pendidikan jenjang SD', 'Kurikulum dan kesiswaan jenjang SD', '2026-06-30 07:27:55', '2026-06-30 07:27:55'),
(3, 'Bidang Pembinaan SMP', 'Membina satuan pendidikan jenjang SMP', 'Kurikulum dan kesiswaan jenjang SMP', '2026-06-30 07:27:55', '2026-06-30 07:27:55'),
(4, 'PAUD dan PNF', 'Membina satuan pendidikan anak usia dini dan pendidikan non formal', 'Kurikulum dan kesiswaan jenjang PAUD/PNF', '2026-07-15 02:10:02', '2026-07-15 02:10:02'),
(5, 'Ketenagaan', 'Mengelola data dan pengembangan tenaga pendidik dan kependidikan', 'Perencanaan dan pembinaan tenaga kependidikan', '2026-07-15 02:10:02', '2026-07-15 02:10:02');

-- --------------------------------------------------------

--
-- Table structure for table `galeri`
--

CREATE TABLE `galeri` (
  `id` int UNSIGNED NOT NULL,
  `admin_id` int UNSIGNED DEFAULT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('foto','prestasi') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'foto',
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `tanggal` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `galeri`
--

INSERT INTO `galeri` (`id`, `admin_id`, `judul`, `kategori`, `gambar`, `keterangan`, `tanggal`, `created_at`, `updated_at`) VALUES
(2, 2, 'Siswa SMPN 1 Sumenep Raih Juara OSN Matematika', 'foto', 'foto_1784087260_644.jpg', 'blablablabla', '2026-07-10', '2026-07-09 17:29:52', '2026-07-15 04:31:20'),
(3, 3, 'Olimpiade OSN Nasional 2026', 'foto', 'foto_1784087249_178.jpg', '', '2026-07-15', '2026-07-15 02:31:26', '2026-07-15 04:31:57'),
(4, 3, 'Beasiswa Luar negeri', 'foto', 'foto_1784087240_565.jpg', '', '2026-07-15', '2026-07-15 02:32:00', '2026-07-15 04:31:57'),
(5, 3, 'Sejarah Indonesia', 'foto', 'foto_1784085821_825.jpg', '', '2026-07-15', '2026-07-15 03:23:41', '2026-07-15 04:31:57'),
(6, 3, 'Sejarah Indonesia', 'foto', 'foto_1784087230_186.jpg', '', '2026-07-15', '2026-07-15 03:29:34', '2026-07-15 04:31:57'),
(7, 3, 'Anak anak', 'foto', 'foto_1784087277_238.webp', '', '2026-07-15', '2026-07-15 03:47:57', '2026-07-15 04:31:57'),
(8, 3, 'Sejarah Indonesia', 'foto', 'foto_1784089413_114.jpeg', '', '2026-07-15', '2026-07-15 04:23:33', '2026-07-15 04:23:33');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_berita`
--

CREATE TABLE `kategori_berita` (
  `id` int UNSIGNED NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_berita`
--

INSERT INTO `kategori_berita` (`id`, `nama`, `slug`) VALUES
(1, 'Kegiatan', 'kegiatan'),
(2, 'Kebijakan', 'kebijakan'),
(3, 'Prestasi', 'prestasi'),
(4, 'Prestasi Siswa', 'prestasi-siswa');

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int UNSIGNED NOT NULL,
  `bidang_id` int UNSIGNED NOT NULL,
  `admin_id` int UNSIGNED NOT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `status` enum('draf','terbit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draf',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `bidang_id`, `admin_id`, `judul`, `slug`, `deskripsi`, `gambar`, `tanggal_mulai`, `tanggal_selesai`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'Monitoring MPLS Tahun Ajaran Baru 2026/2027', 'monitoring-mpls-tahun-ajaran-baru-2026-2027', 'Melakukan Pengawasan pada pelaksanaan MPLS di hari pertama masuk sekolah tahun ajaran baru 2026/2027 di SDN Bluto 1', 'img/kegiatan/kegiatan_1784081798_470.jpg', '2026-07-15', '2026-07-15', 'terbit', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pegawai`
--

CREATE TABLE `pegawai` (
  `id` int UNSIGNED NOT NULL,
  `bidang_id` int UNSIGNED DEFAULT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('aktif','nonaktif') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pegawai`
--

INSERT INTO `pegawai` (`id`, `bidang_id`, `nama`, `nip`, `jabatan`, `foto`, `email`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nurul Hidayati S.Pd.', '19880902789827731', 'Kepala Bidang Pembinaan SD', 'pegawai_1784082544_823.webp', 'nurul.h@gmail.com', 'aktif', '2026-07-08 01:57:26', '2026-07-15 02:29:04'),
(2, 1, 'Tria Desy Nurhaliza, S.kom', '19880902789822003', 'Kepala Bidang Pembinaan SMP', 'pegawai_1784082834_689.jpg', 'desyyyy@gmail.com', 'aktif', '2026-07-15 02:33:54', '2026-07-15 02:33:54'),
(3, 2, 'kamila Mulya Fadila, S.Kom', '19880902789822004', 'Kepala Bidang Pembinaan SD', 'pegawai_1784082878_959.jpg', 'Kamilaaaaa@gmail.com', 'aktif', '2026-07-15 02:34:38', '2026-07-15 02:34:38'),
(4, NULL, 'Nadiatul Khoir, S.Kom', '19880902789822006', 'Kepala Dinas Pendidikan', 'pegawai_1784082940_771.jpg', 'Nadiaaaa@gmail.com', 'aktif', '2026-07-15 02:35:40', '2026-07-15 02:35:40');

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id` int UNSIGNED NOT NULL,
  `nama` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_tiket` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('sarana_prasarana','kepegawaian','pelayanan','lainnya') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lainnya',
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lampiran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('diajukan','diproses','ditanggapi','ditutup') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'diajukan',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengaduan`
--

INSERT INTO `pengaduan` (`id`, `nama`, `email`, `telepon`, `no_tiket`, `kategori`, `judul`, `isi`, `lampiran`, `status`, `created_at`, `updated_at`) VALUES
(1, 'faiza', 'faiza@gmail.com', '082345678191', 'TK-001', 'kepegawaian', 'Koneksi Internet Sekolah Sering Terputus', 'Internet di ruang guru dan lab komputer SDN 2 Bluto sering terputus sejak dua minggu terakhir, terutama pada siang hari. Hal ini mengganggu proses input data Dapodik dan pembelajaran daring. Mohon dilakukan pengecekan ke pihak penyedia layanan.', 'lampiran.jpeg', 'ditutup', '2026-07-06 04:21:38', '2026-07-09 16:58:59');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int UNSIGNED NOT NULL,
  `admin_id` int UNSIGNED DEFAULT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `isi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal` date NOT NULL,
  `status` enum('draf','terbit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draf',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `admin_id`, `judul`, `slug`, `isi`, `gambar`, `tanggal`, `status`, `created_at`, `updated_at`) VALUES
(2, 2, 'Siswa SMPN 1 Sumenep Raih Juara OSN Matematika', 'siswa-smpn-1-sumenep-raih-juara-osn-matematika', 'blablabla', NULL, '2026-07-07', 'terbit', '2026-07-07 17:16:36', '2026-07-13 17:03:15');

-- --------------------------------------------------------

--
-- Table structure for table `profil`
--

CREATE TABLE `profil` (
  `id` int UNSIGNED NOT NULL,
  `selayang_pandang` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visi` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `misi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `alamat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profil`
--

INSERT INTO `profil` (`id`, `selayang_pandang`, `visi`, `misi`, `alamat`, `telepon`, `email`, `facebook`, `youtube`, `instagram`, `updated_at`) VALUES
(1, 'Dinas Pendidikan Kabupaten Sumenep merupakan unsur pelaksana urusan pemerintahan daerah di bidang pendidikan.', 'Terwujudnya layanan pendidikan yang merata, bermutu, dan berkarakter.', 'Meningkatkan akses dan mutu pendidikan serta tata kelola yang transparan.', 'Jl. DR. Cipto No.35, Desa Kolor, Sumenep', '0328 662322', 'disdik@sumenepkab.go.id', '', '', '', '2026-07-13 01:58:42');

-- --------------------------------------------------------

--
-- Table structure for table `sakip`
--

CREATE TABLE `sakip` (
  `id` int UNSIGNED NOT NULL,
  `admin_id` int UNSIGNED DEFAULT NULL,
  `kategori` enum('renstra_pk','lkjip','iku') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun` year NOT NULL,
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tanggapan_pengaduan`
--

CREATE TABLE `tanggapan_pengaduan` (
  `id` int UNSIGNED NOT NULL,
  `pengaduan_id` int UNSIGNED NOT NULL,
  `admin_id` int UNSIGNED DEFAULT NULL,
  `isi` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tanggapan_pengaduan`
--

INSERT INTO `tanggapan_pengaduan` (`id`, `pengaduan_id`, `admin_id`, `isi`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 'blanlanla', '2026-07-09 16:58:14', '2026-07-09 16:58:14'),
(2, 1, 2, 'blablablablabla', '2026-07-09 16:58:45', '2026-07-09 16:58:45');

--
-- Triggers `tanggapan_pengaduan`
--
DELIMITER $$
CREATE TRIGGER `trg_tanggapan_update_status` AFTER INSERT ON `tanggapan_pengaduan` FOR EACH ROW BEGIN
  UPDATE pengaduan
  SET status = 'ditanggapi'
  WHERE id = NEW.pengaduan_id
    AND status IN ('diajukan','diproses');
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `berita`
--
ALTER TABLE `berita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_berita_admin` (`admin_id`),
  ADD KEY `fk_berita_kategori` (`kategori_id`),
  ADD KEY `idx_berita_status` (`status`);

--
-- Indexes for table `bidang`
--
ALTER TABLE `bidang`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `galeri`
--
ALTER TABLE `galeri`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_galeri_admin` (`admin_id`);

--
-- Indexes for table `kategori_berita`
--
ALTER TABLE `kategori_berita`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kegiatan_bidang` (`bidang_id`),
  ADD KEY `fk_kegiatan_admin` (`admin_id`);

--
-- Indexes for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `fk_pegawai_bidang` (`bidang_id`),
  ADD KEY `idx_pegawai_status` (`status`);

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_tiket` (`no_tiket`),
  ADD KEY `idx_pengaduan_status` (`status`),
  ADD KEY `idx_pengaduan_notiket` (`no_tiket`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_pengumuman_admin` (`admin_id`),
  ADD KEY `idx_pengumuman_status` (`status`);

--
-- Indexes for table `profil`
--
ALTER TABLE `profil`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sakip`
--
ALTER TABLE `sakip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sakip_admin` (`admin_id`),
  ADD KEY `idx_sakip_kategori` (`kategori`),
  ADD KEY `idx_sakip_tahun` (`tahun`);

--
-- Indexes for table `tanggapan_pengaduan`
--
ALTER TABLE `tanggapan_pengaduan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tanggapan_pengaduan` (`pengaduan_id`),
  ADD KEY `fk_tanggapan_admin` (`admin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `berita`
--
ALTER TABLE `berita`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bidang`
--
ALTER TABLE `bidang`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `galeri`
--
ALTER TABLE `galeri`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `kategori_berita`
--
ALTER TABLE `kategori_berita`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pegawai`
--
ALTER TABLE `pegawai`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `profil`
--
ALTER TABLE `profil`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sakip`
--
ALTER TABLE `sakip`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tanggapan_pengaduan`
--
ALTER TABLE `tanggapan_pengaduan`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `berita`
--
ALTER TABLE `berita`
  ADD CONSTRAINT `fk_berita_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_berita_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_berita` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `galeri`
--
ALTER TABLE `galeri`
  ADD CONSTRAINT `fk_galeri_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD CONSTRAINT `fk_kegiatan_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`),
  ADD CONSTRAINT `fk_kegiatan_bidang` FOREIGN KEY (`bidang_id`) REFERENCES `bidang` (`id`);

--
-- Constraints for table `pegawai`
--
ALTER TABLE `pegawai`
  ADD CONSTRAINT `fk_pegawai_bidang` FOREIGN KEY (`bidang_id`) REFERENCES `bidang` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `fk_pengumuman_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sakip`
--
ALTER TABLE `sakip`
  ADD CONSTRAINT `fk_sakip_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tanggapan_pengaduan`
--
ALTER TABLE `tanggapan_pengaduan`
  ADD CONSTRAINT `fk_tanggapan_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tanggapan_pengaduan` FOREIGN KEY (`pengaduan_id`) REFERENCES `pengaduan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
