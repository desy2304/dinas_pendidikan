<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// Hanya menerima POST -- mencegah penghapusan tidak sengaja lewat link GET/prefetch
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sakip.php");
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header("Location: sakip.php?notif=not_found");
    exit;
}

$q = mysqli_query($koneksi, "SELECT file FROM sakip WHERE id = $id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: sakip.php?notif=not_found");
    exit;
}
$row = mysqli_fetch_assoc($q);

if (!empty($row['file']) && file_exists(__DIR__ . '/../' . $row['file'])) {
    @unlink(__DIR__ . '/../' . $row['file']);
}

if (mysqli_query($koneksi, "DELETE FROM sakip WHERE id = $id")) {
    header("Location: sakip.php?notif=sukses_hapus");
} else {
    header("Location: sakip.php?notif=gagal_hapus");
}
exit;
