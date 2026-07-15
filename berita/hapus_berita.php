<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// Sengaja hanya menerima POST -- request GET tidak boleh menghapus data,
// supaya link tidak bisa terhapus otomatis oleh browser preload/crawler.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: berita.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: berita.php?notif=not_found");
    exit;
}

// Hapus file gambar fisik jika ada
$q = mysqli_query($koneksi, "SELECT gambar FROM berita WHERE id = $id LIMIT 1");
if ($q && $row = mysqli_fetch_assoc($q)) {
    $pathGambar = __DIR__ . '/../img/berita/' . $row['gambar'];
    if (!empty($row['gambar']) && file_exists($pathGambar)) {
        unlink($pathGambar);
    }
} else {
    header("Location: berita.php?notif=not_found");
    exit;
}

if (mysqli_query($koneksi, "DELETE FROM berita WHERE id = $id")) {
    header("Location: berita.php?notif=sukses_hapus");
} else {
    header("Location: berita.php?notif=gagal_hapus");
}
exit;
