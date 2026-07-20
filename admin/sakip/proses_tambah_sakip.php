<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sakip.php");
    exit;
}

$kategoriValid = ['renstra_pk', 'lkjip', 'iku'];

$kategori   = $_POST['kategori'] ?? '';
$judul      = trim($_POST['judul'] ?? '');
$tahun      = trim($_POST['tahun'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$adminId    = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

if (
    !in_array($kategori, $kategoriValid, true)
    || $judul === ''
    || !ctype_digit($tahun)
    || empty($_FILES['file']['name'])
) {
    header("Location: sakip.php?notif=gagal_kosong");
    exit;
}

// ==== Upload file PDF (wajib) ====
$folderUpload     = '../uploads/sakip';
$folderUploadPath = __DIR__ . '/../' . $folderUpload;
if (!is_dir($folderUploadPath)) {
    mkdir($folderUploadPath, 0755, true);
}

$ekstensi = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (
    $_FILES['file']['error'] !== UPLOAD_ERR_OK
    || $ekstensi !== 'pdf'
    || $_FILES['file']['size'] > 5 * 1024 * 1024
) {
    header("Location: sakip.php?notif=gagal_kosong");
    exit;
}

$namaFile = 'sakip_' . $kategori . '_' . time() . '_' . rand(100, 999) . '.pdf';
$namaSimpan = null;
if (move_uploaded_file($_FILES['file']['tmp_name'], $folderUploadPath . $namaFile)) {
    $namaSimpan = $folderUpload . $namaFile;
} else {
    header("Location: sakip.php?notif=gagal_simpan");
    exit;
}

// ==== Simpan ke database ====
$kategoriEsc   = mysqli_real_escape_string($koneksi, $kategori);
$judulEsc      = mysqli_real_escape_string($koneksi, $judul);
$tahunEsc      = (int)$tahun;
$keteranganEsc = mysqli_real_escape_string($koneksi, $keterangan);
$fileEsc       = mysqli_real_escape_string($koneksi, $namaSimpan);
$adminVal      = $adminId ? $adminId : "NULL";

$sql = "INSERT INTO sakip (admin_id, kategori, judul, tahun, file, keterangan)
        VALUES ($adminVal, '$kategoriEsc', '$judulEsc', $tahunEsc, '$fileEsc', '$keteranganEsc')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: sakip.php?notif=sukses_tambah");
} else {
    @unlink($folderUploadPath . $namaFile);
    header("Location: sakip.php?notif=gagal_simpan");
}
exit;
