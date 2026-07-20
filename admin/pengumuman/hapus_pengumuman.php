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
    header("Location: pengumuman.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: pengumuman.php?notif=not_found");
    exit;
}

// Hapus file gambar fisik jika ada.
// Nama file di DB disimpan polos (tanpa folder), jadi path fisiknya harus
// digabung dengan folder img/pengumuman/ relatif ke __DIR__ (bukan cwd).
$folderUpload = __DIR__ . '/../img/pengumuman/';

$q = mysqli_query($koneksi, "SELECT gambar FROM pengumuman WHERE id = $id LIMIT 1");
if ($q && $row = mysqli_fetch_assoc($q)) {
    if (!empty($row['gambar']) && file_exists($folderUpload . $row['gambar'])) {
        @unlink($folderUpload . $row['gambar']);
    }
} else {
    header("Location: pengumuman.php?notif=not_found");
    exit;
}

if (mysqli_query($koneksi, "DELETE FROM pengumuman WHERE id = $id")) {
    header("Location: pengumuman.php?notif=sukses_hapus");
} else {
    header("Location: pengumuman.php?notif=gagal_hapus");
}
exit;
