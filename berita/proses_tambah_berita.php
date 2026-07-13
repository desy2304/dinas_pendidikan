<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

function buatSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    return trim($string, '-');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: berita.php");
    exit;
}

$judul      = trim($_POST['judul'] ?? '');
$isi        = trim($_POST['isi'] ?? '');
$kategoriId = !empty($_POST['kategori_id']) ? (int)$_POST['kategori_id'] : null;
$status     = ($_POST['status'] ?? 'draf') === 'terbit' ? 'terbit' : 'draf';
$adminId    = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

if ($judul === '' || $isi === '') {
    header("Location: berita.php?notif=gagal_kosong");
    exit;
}

// ==== Generate slug unik ====
$slugDasar = buatSlug($judul);
if ($slugDasar === '') {
    $slugDasar = 'berita';
}
$slug   = $slugDasar;
$urutan = 1;
while (true) {
    $slugEsc = mysqli_real_escape_string($koneksi, $slug);
    $cek = mysqli_query($koneksi, "SELECT id FROM berita WHERE slug = '$slugEsc' LIMIT 1");
    if ($cek && mysqli_num_rows($cek) === 0) {
        break;
    }
    $urutan++;
    $slug = $slugDasar . '-' . $urutan;
}

// ==== Upload gambar (opsional) ====
$namaGambar = null;
if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $folderUpload = 'img/berita/';
    $folderUploadPath = __DIR__ . '/../' . $folderUpload;
    if (!is_dir($folderUploadPath)) {
        mkdir($folderUploadPath, 0755, true);
    }

    $ekstensi      = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ekstensi, $ekstensiValid) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'berita_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUploadPath . $namaFile)) {
            $namaGambar = $folderUpload . $namaFile;
        }
    }
}

// ==== Simpan ke database ====
$judulEsc = mysqli_real_escape_string($koneksi, $judul);
$isiEsc   = mysqli_real_escape_string($koneksi, $isi);
$slugEsc  = mysqli_real_escape_string($koneksi, $slug);

$gambarVal    = $namaGambar ? "'" . mysqli_real_escape_string($koneksi, $namaGambar) . "'" : "NULL";
$kategoriVal  = $kategoriId ? $kategoriId : "NULL";
$adminVal     = $adminId ? $adminId : "NULL";
$tanggalPublish = ($status === 'terbit') ? "'" . date('Y-m-d') . "'" : "NULL";

$sql = "INSERT INTO berita (admin_id, kategori_id, judul, slug, isi, gambar, status, tanggal_publish)
        VALUES ($adminVal, $kategoriVal, '$judulEsc', '$slugEsc', '$isiEsc', $gambarVal, '$status', $tanggalPublish)";

if (mysqli_query($koneksi, $sql)) {
    header("Location: berita.php?notif=sukses_tambah");
} else {
    header("Location: berita.php?notif=gagal_simpan");
}
exit;
