<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// Hanya menerima POST -- mencegah penghapusan tidak sengaja lewat link GET/prefetch
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: galeri_prestasi.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: galeri_prestasi.php?notif=not_found");
    exit;
}

// Dibatasi hanya kategori prestasi, supaya form ini tidak bisa menghapus data foto/video
$q = mysqli_query($koneksi, "SELECT gambar FROM galeri WHERE id = $id AND kategori = 'prestasi' LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: galeri_prestasi.php?notif=not_found");
    exit;
}

$row = mysqli_fetch_assoc($q);
$pathGambar = __DIR__ . '/../img/galeri/' . $row['gambar'];
if (mysqli_query($koneksi, "DELETE FROM galeri WHERE id = $id AND kategori = 'prestasi'")) {
    if (is_file($pathGambar)) {
        unlink($pathGambar);
    }
    header("Location: galeri_prestasi.php?notif=sukses_hapus");
} else {
    header("Location: galeri_prestasi.php?notif=gagal_hapus");
}
exit;
