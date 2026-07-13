<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: galeri_video.php");
    exit;
}

$judul      = trim($_POST['judul'] ?? '');
$tanggal    = trim($_POST['tanggal'] ?? '');
$videoUrl   = trim($_POST['video'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');
$adminId    = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

// Dikunci hardcode di server -- halaman ini khusus kategori video
$kategori = 'video';

if ($judul === '' || $tanggal === '' || $videoUrl === '' || empty($_FILES['gambar']['name'])) {
    header("Location: galeri_video.php?notif=gagal_kosong");
    exit;
}

if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
    header("Location: galeri_video.php?notif=gagal_kosong");
    exit;
}

// ==== Upload thumbnail (wajib, kolom gambar NOT NULL di database) ====
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
    header("Location: galeri_video.php?notif=gagal_kosong");
    exit;
}

$namaFile   = 'video_thumb_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
$namaGambar = null;
if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUploadPath . $namaFile)) {
    $namaGambar = $folderUpload . $namaFile;
} else {
    header("Location: galeri_video.php?notif=gagal_simpan");
    exit;
}

// ==== Simpan ke database ====
$judulEsc      = mysqli_real_escape_string($koneksi, $judul);
$tanggalEsc    = mysqli_real_escape_string($koneksi, $tanggal);
$videoEsc      = mysqli_real_escape_string($koneksi, $videoUrl);
$keteranganEsc = mysqli_real_escape_string($koneksi, $keterangan);
$gambarEsc     = mysqli_real_escape_string($koneksi, $namaGambar);
$kategoriEsc   = mysqli_real_escape_string($koneksi, $kategori);
$adminVal      = $adminId ? $adminId : "NULL";

$sql = "INSERT INTO galeri (admin_id, judul, kategori, gambar, video, keterangan, tanggal)
        VALUES ($adminVal, '$judulEsc', '$kategoriEsc', '$gambarEsc', '$videoEsc', '$keteranganEsc', '$tanggalEsc')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: galeri_video.php?notif=sukses_tambah");
} else {
    @unlink($folderUploadPath . $namaFile);
    header("Location: galeri_video.php?notif=gagal_simpan");
}
exit;
