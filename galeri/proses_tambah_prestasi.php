<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: galeri_prestasi.php");
    exit;
}

$judul      = trim($_POST['judul'] ?? '');
$tanggal    = trim($_POST['tanggal'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$adminId    = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

// Dikunci hardcode di server -- halaman ini khusus kategori prestasi
$kategori = 'prestasi';

if ($judul === '' || $tanggal === '' || empty($_FILES['gambar']['name'])) {
    header("Location: galeri_prestasi.php?notif=gagal_kosong");
    exit;
}

// ==== Upload foto (wajib) ====
$folderUpload     = 'img/galeri/';
$folderUploadPath = __DIR__ . '/../' . $folderUpload;
if (!is_dir($folderUploadPath)) {
    mkdir($folderUploadPath, 0755, true);
}

$ekstensi      = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
$ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

if (
    $_FILES['gambar']['error'] !== UPLOAD_ERR_OK
    || !in_array($ekstensi, $ekstensiValid)
    || $_FILES['gambar']['size'] > 2 * 1024 * 1024
) {
    header("Location: galeri_prestasi.php?notif=gagal_kosong");
    exit;
}

$namaFile   = 'prestasi_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
$namaGambar = null;
if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUploadPath . $namaFile)) {
    $namaGambar = $folderUpload . $namaFile;
} else {
    header("Location: galeri_prestasi.php?notif=gagal_simpan");
    exit;
}

// ==== Simpan ke database ====
$judulEsc      = mysqli_real_escape_string($koneksi, $judul);
$tanggalEsc    = mysqli_real_escape_string($koneksi, $tanggal);
$keteranganEsc = mysqli_real_escape_string($koneksi, $keterangan);
$gambarEsc     = mysqli_real_escape_string($koneksi, $namaGambar);
$kategoriEsc   = mysqli_real_escape_string($koneksi, $kategori);
$adminVal      = $adminId ? $adminId : "NULL";

$sql = "INSERT INTO galeri (admin_id, judul, kategori, gambar, keterangan, tanggal)
        VALUES ($adminVal, '$judulEsc', '$kategoriEsc', '$gambarEsc', '$keteranganEsc', '$tanggalEsc')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: galeri_prestasi.php?notif=sukses_tambah");
} else {
    @unlink($folderUploadPath . $namaFile);
    header("Location: galeri_prestasi.php?notif=gagal_simpan");
}
exit;
