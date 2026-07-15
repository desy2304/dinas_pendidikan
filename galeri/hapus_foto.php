<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// Hanya menerima POST -- mencegah penghapusan tidak sengaja lewat link GET/prefetch
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: galeri_foto.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: galeri_foto.php?notif=not_found");
    exit;
}

// Dibatasi hanya kategori foto, supaya form Foto tidak bisa menghapus data video/prestasi
$q = mysqli_query($koneksi, "SELECT gambar FROM galeri WHERE id = $id AND kategori = 'foto' LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: galeri_foto.php?notif=not_found");
    exit;
}

$row = mysqli_fetch_assoc($q);
$pathGambar = __DIR__ . '/../img/galeri/' . $row['gambar'];
if (mysqli_query($koneksi, "DELETE FROM galeri WHERE id = $id AND kategori = 'foto'")) {
    if (!empty($row['gambar']) && file_exists($pathGambar)) {
        unlink($pathGambar);
    }
    header("Location: galeri_foto.php?notif=sukses_hapus");
} else {
    header("Location: galeri_foto.php?notif=gagal_hapus");
}
exit;
