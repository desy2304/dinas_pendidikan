<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pengaduan.php");
    exit;
}

$statusValid = ['diajukan', 'diproses', 'ditanggapi', 'ditutup'];

$id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$isi     = trim($_POST['isi_tanggapan'] ?? '');
$status  = $_POST['status'] ?? '';
$adminId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

if ($id <= 0) {
    header("Location: pengaduan.php?notif=not_found");
    exit;
}

if (!in_array($status, $statusValid, true)) {
    $status = 'diproses';
}

// Pastikan pengaduan memang ada
$cek = mysqli_query($koneksi, "SELECT id FROM pengaduan WHERE id = $id LIMIT 1");
if (!$cek || mysqli_num_rows($cek) === 0) {
    header("Location: pengaduan.php?notif=not_found");
    exit;
}

// Kalau ada isi tanggapan, simpan sebagai balasan baru
if ($isi !== '') {
    $isiEsc   = mysqli_real_escape_string($koneksi, $isi);
    $adminVal = $adminId ? $adminId : "NULL";

    $sqlInsert = "INSERT INTO tanggapan_pengaduan (pengaduan_id, admin_id, isi)
                  VALUES ($id, $adminVal, '$isiEsc')";

    if (!mysqli_query($koneksi, $sqlInsert)) {
        header("Location: pengaduan.php?notif=gagal_simpan");
        exit;
    }
}

// Perbarui status (menimpa status otomatis dari trigger, sesuai pilihan admin)
$statusEsc = mysqli_real_escape_string($koneksi, $status);
mysqli_query($koneksi, "UPDATE pengaduan SET status = '$statusEsc' WHERE id = $id");

header("Location: pengaduan.php?notif=sukses_balas");
exit;
