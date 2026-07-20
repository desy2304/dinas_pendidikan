<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// Hanya menerima POST -- request GET tidak boleh menghapus data.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pegawai.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: pegawai.php?notif=not_found");
    exit;
}

$folderUpload = __DIR__ . '/../img/pegawai/';

// Hapus file foto fisik jika ada
$q = mysqli_query($koneksi, "SELECT foto FROM pegawai WHERE id = $id LIMIT 1");
if ($q && $row = mysqli_fetch_assoc($q)) {
    if (!empty($row['foto']) && file_exists($folderUpload . $row['foto'])) {
        @unlink($folderUpload . $row['foto']);
    }
} else {
    header("Location: pegawai.php?notif=not_found");
    exit;
}

if (mysqli_query($koneksi, "DELETE FROM pegawai WHERE id = $id")) {
    header("Location: pegawai.php?notif=sukses_hapus");
} else {
    header("Location: pegawai.php?notif=gagal_hapus");
}
exit;
