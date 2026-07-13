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

$id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$judul      = trim($_POST['judul'] ?? '');
$tanggal    = trim($_POST['tanggal'] ?? '');
$videoUrl   = trim($_POST['video'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

if ($id <= 0) {
    header("Location: galeri_video.php?notif=not_found");
    exit;
}

if ($judul === '' || $tanggal === '' || $videoUrl === '') {
    header("Location: galeri_video.php?notif=gagal_kosong");
    exit;
}

if (!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
    header("Location: galeri_video.php?notif=gagal_kosong");
    exit;
}

// Ambil data lama -- sekaligus pastikan baris ini memang kategori video
$q = mysqli_query($koneksi, "SELECT gambar FROM galeri WHERE id = $id AND kategori = 'video' LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: galeri_video.php?notif=not_found");
    exit;
}
$dataLama = mysqli_fetch_assoc($q);

// ==== Upload thumbnail baru (opsional, replace yang lama jika ada) ====
$namaGambar = $dataLama['gambar'];
if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $folderUpload     = 'img/galeri/';
    $folderUploadPath = __DIR__ . '/../' . $folderUpload;
    if (!is_dir($folderUploadPath)) {
        mkdir($folderUploadPath, 0755, true);
    }

    $ekstensi      = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ekstensi, $ekstensiValid) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'video_thumb_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUploadPath . $namaFile)) {
            if (!empty($dataLama['gambar']) && file_exists(__DIR__ . '/../' . $dataLama['gambar'])) {
                @unlink(__DIR__ . '/../' . $dataLama['gambar']);
            }
            $namaGambar = $folderUpload . $namaFile;
        }
    }
}

$judulEsc      = mysqli_real_escape_string($koneksi, $judul);
$tanggalEsc    = mysqli_real_escape_string($koneksi, $tanggal);
$videoEsc      = mysqli_real_escape_string($koneksi, $videoUrl);
$keteranganEsc = mysqli_real_escape_string($koneksi, $keterangan);
$gambarEsc     = mysqli_real_escape_string($koneksi, $namaGambar);

$sql = "UPDATE galeri SET
            judul = '$judulEsc',
            tanggal = '$tanggalEsc',
            video = '$videoEsc',
            keterangan = '$keteranganEsc',
            gambar = '$gambarEsc'
        WHERE id = $id AND kategori = 'video'";

if (mysqli_query($koneksi, $sql)) {
    header("Location: galeri_video.php?notif=sukses_edit");
} else {
    header("Location: galeri_video.php?notif=gagal_simpan");
}
exit;
