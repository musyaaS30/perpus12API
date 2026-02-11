-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 11, 2026 at 07:24 AM
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
-- Database: `db_perpus_baru`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `id_role` int DEFAULT '1',
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_admin` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `id_role`, `username`, `password`, `nama_admin`, `email`, `created_at`) VALUES
(1, 1, 'admin', 'admin123', 'Admin Utama', 'admin@perpus.sch.id', '2026-01-18 15:09:03');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int NOT NULL,
  `id_kategori` int DEFAULT NULL,
  `judul` varchar(150) NOT NULL,
  `image_buku` text,
  `deskripsi` text,
  `penulis` varchar(100) DEFAULT NULL,
  `penerbit` varchar(100) DEFAULT NULL,
  `tahun_terbit` year DEFAULT NULL,
  `halaman` int DEFAULT NULL,
  `stok` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `id_kategori`, `judul`, `image_buku`, `deskripsi`, `penulis`, `penerbit`, `tahun_terbit`, `halaman`, `stok`) VALUES
(1, 2, 'Belajar PHP Native', 'https://cdn.gramedia.com/uploads/products/gamp4h-ol-.jpg', 'Panduan dasar pemrograman PHP tanpa framework', 'Andi Wijaya', 'Informatika', 2023, 250, 9),
(2, 2, 'Dasar JavaScript', 'https://www.penerbitlakeisha.com/admin/img/foto_buku/17-05-2023_Revisi_Cover_484_Web_Revisi.jpg', 'Mengenal JavaScript dari nol', 'Budi Santoso', 'Elex Media', 2022, 220, 7),
(3, 1, 'Negeri 5 Menara', 'https://imgv2-2-f.scribdassets.com/img/document/364192194/original/f8500770e5/1?v=1', 'Novel inspiratif tentang pendidikan', 'Ahmad Fuadi', 'Gramedia', 2009, 320, 6),
(4, 1, 'Bumi', 'https://www.gramedia.com/blog/content/images/2025/01/Bumi.png', 'Novel fantasi petualangan remaja', 'Tere Liye', 'Gramedia', 2014, 440, 8),
(5, 3, 'Kimia Dasar', 'https://mediabersaudara.com/wp-content/uploads/2023/08/Cover-buku-Kimia-Dasar-Vol-1_ISBN.png', 'Konsep dasar ilmu kimia', 'Sukardjo', 'Erlangga', 2020, 300, 5),
(6, 3, 'Biologi SMA Kelas XI', 'https://static.sc.cloudapp.web.id/content/image/coverteks/coverkurikulum21/Biologi-BS-KLS-XI-Cover.png', 'Materi biologi untuk SMA kelas XI', 'Campbell', 'Erlangga', 2019, 350, 4),
(7, 4, 'Biologi Sel', 'https://egcmedbooks.com/images/produk/313.jpg', 'Pembahasan lengkap struktur dan fungsi sel', 'Alberts', 'Pearson', 2018, 410, 3),
(8, 2, 'Pemrograman Web Lanjut', 'https://ebooks.gramedia.com/ebook-covers/45208/image_highres/ID_PWTL2018MTH11WTL.jpg', 'HTML, CSS, JavaScript tingkat lanjut', 'Dea Afrizal', 'Informatika', 2024, 280, 9),
(9, 9, 'Bahlil', 'https://example.com/cover.jpg', 'Buku pembelajaran ekonomi', 'Abdul Somat', 'Andi Kobra', 2023, 300, 10);

-- --------------------------------------------------------

--
-- Table structure for table `guru_anggota`
--

CREATE TABLE `guru_anggota` (
  `id_guru` int NOT NULL,
  `id_role` int DEFAULT '3',
  `nip` varchar(30) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama_guru` varchar(100) DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `guru_anggota`
--

INSERT INTO `guru_anggota` (`id_guru`, `id_role`, `nip`, `password`, `nama_guru`, `no_telp`, `alamat`, `created_at`) VALUES
(2, 3, '098787656543213243', 'guruir01', 'Bapak Irwan Saputra', '087654532178', 'jl kebon banwangyr3746736', '2026-02-02 03:48:30'),
(3, 3, '098787656543256534', 'yennyrpl12', 'Ibu yenny', '098798787681', 'jl jalanin aja 50', '2026-02-02 03:51:33'),
(4, 3, '198765432123456789', 'sari123', 'Ibu Sari Dewi', '081234567890', 'Bandung', '2026-02-09 06:54:51'),
(5, 3, '11234543453425643434', 'sinung123', 'Bapak Sinung', '081234567830', 'IKN', '2026-02-09 07:30:08');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_buku`
--

CREATE TABLE `kategori_buku` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori_buku`
--

INSERT INTO `kategori_buku` (`id_kategori`, `nama_kategori`) VALUES
(8, 'Agama'),
(4, 'biologi'),
(11, 'Ekonomi'),
(12, 'Ensiklopedia'),
(5, 'Fiksi'),
(6, 'Nonfiksi'),
(9, 'Pendidikan'),
(10, 'Psikologi'),
(3, 'sains'),
(1, 'sastra'),
(7, 'Sejarah'),
(2, 'teknologi');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_peminjaman` int NOT NULL,
  `id_siswa` int NOT NULL,
  `id_buku` int NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `peminjaman`
--

INSERT INTO `peminjaman` (`id_peminjaman`, `id_siswa`, `id_buku`, `tanggal_pinjam`, `tanggal_kembali`, `status`) VALUES
(1, 1, 1, '2026-02-09', NULL, 'dipinjam');

-- --------------------------------------------------------

--
-- Table structure for table `pustakawan`
--

CREATE TABLE `pustakawan` (
  `id_pustakawan` int NOT NULL,
  `id_role` int DEFAULT '2',
  `password` varchar(255) NOT NULL,
  `nama_pustakawan` varchar(100) NOT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pustakawan`
--

INSERT INTO `pustakawan` (`id_pustakawan`, `id_role`, `password`, `nama_pustakawan`, `no_telp`, `alamat`, `created_at`) VALUES
(1, 2, 'pustaka123', 'Ijul Ardiansyah', '087654532178', 'jl. swasembada 43', '2026-01-18 15:09:03'),
(2, 2, 'pustaka456', 'Ryan Armando', '081234567890', 'jl. P lembang no. 10', '2026-01-18 15:09:03');

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id_role` int NOT NULL,
  `nama_role` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id_role`, `nama_role`) VALUES
(1, 'admin'),
(2, 'pustakawan'),
(3, 'member');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id_siswa` int NOT NULL,
  `id_role` int DEFAULT '3',
  `nis` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `kelas` varchar(20) DEFAULT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `id_role`, `nis`, `password`, `nama_siswa`, `kelas`, `alamat`, `created_at`) VALUES
(1, 3, '2024001', 'rakha123', 'Rakha Naufal', 'XI RPL 1', 'Jakarta', '2026-01-18 15:09:03'),
(2, 3, '2024002', 'Lisa123', 'Paramesti', 'XI RPL 1', 'jakarta', '2026-01-18 15:09:03'),
(3, 3, '2024003', 'zeril123', 'M.Nazriel', 'XI RPL 1', 'Jakarta utara', '2026-01-18 15:09:03'),
(4, 3, '2024030', 'musya123', 'Musyahadat', 'XI RPL 1', 'Palembang', '2026-01-18 15:09:03'),
(5, 3, '2024002232', 'nabilah33', 'Nabilah Assegaf', 'XI RPL 1', 'jln kedondong 45', '2026-02-02 03:56:19'),
(6, 3, '2024010', 'Hazzel123', 'Hazzel pratama', 'XI RPL 1', 'Jakarta Timur', '2026-02-09 06:54:25'),
(7, 3, '1123454', 'roji123', 'Roji pratama', '', 'Jakarta Utara', '2026-02-09 07:42:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `id_role` int NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `id_role`, `password`, `nama`, `created_at`) VALUES
(1, 1, 'admin123', 'Admin Utama', '2026-02-02 03:33:26'),
(4, 2, 'pustaka123', 'Ijul Ardiansyah', '2026-02-02 03:35:25'),
(5, 2, 'pustaka456', 'Ryan Armando', '2026-02-02 03:35:25'),
(7, 3, 'rakha123', 'Rakha Naufal', '2026-02-02 03:35:25'),
(8, 3, 'Lisa123', 'Paramesti', '2026-02-02 03:35:25'),
(9, 3, 'zeril123', 'M.Nazriel', '2026-02-02 03:35:25'),
(10, 3, 'musya123', 'Musyahadat', '2026-02-02 03:35:25'),
(14, 3, 'yennyrpl12', 'Ibu yenny', '2026-02-02 03:51:33'),
(15, 3, 'nabilah33', 'Nabilah Assegaf', '2026-02-02 03:56:19'),
(16, 3, 'Hazzel123', 'Hazzel pratama', '2026-02-09 06:54:25'),
(17, 3, 'sari123', 'Ibu Sari Dewi', '2026-02-09 06:54:51'),
(18, 3, 'sinung123', 'Bapak Sinung', '2026-02-09 07:30:08'),
(19, 3, 'roji123', 'Roji pratama', '2026-02-09 07:50:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_admin_role` (`id_role`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`),
  ADD KEY `fk_buku_kategori` (`id_kategori`);

--
-- Indexes for table `guru_anggota`
--
ALTER TABLE `guru_anggota`
  ADD PRIMARY KEY (`id_guru`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `id_role` (`id_role`);

--
-- Indexes for table `kategori_buku`
--
ALTER TABLE `kategori_buku`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `nama_kategori` (`nama_kategori`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_peminjaman`),
  ADD KEY `id_siswa` (`id_siswa`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `pustakawan`
--
ALTER TABLE `pustakawan`
  ADD PRIMARY KEY (`id_pustakawan`),
  ADD KEY `fk_pustakawan_role` (`id_role`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id_role`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id_siswa`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `fk_siswa_role` (`id_role`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `nama` (`nama`),
  ADD KEY `id_role` (`id_role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `guru_anggota`
--
ALTER TABLE `guru_anggota`
  MODIFY `id_guru` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kategori_buku`
--
ALTER TABLE `kategori_buku`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_peminjaman` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pustakawan`
--
ALTER TABLE `pustakawan`
  MODIFY `id_pustakawan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id_role` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `fk_admin_role` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`);

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_buku` (`id_kategori`);

--
-- Constraints for table `guru_anggota`
--
ALTER TABLE `guru_anggota`
  ADD CONSTRAINT `guru_anggota_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`);

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`),
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`);

--
-- Constraints for table `pustakawan`
--
ALTER TABLE `pustakawan`
  ADD CONSTRAINT `fk_pustakawan_role` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`);

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_role` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`id_role`) REFERENCES `role` (`id_role`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
