<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kegiatan.php");
    exit;
}

function buatSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    return trim($string, '-');
}

$bidangId       = !empty($_POST['bidang_id']) ? (int)$_POST['bidang_id'] : 0;
$judul          = trim($_POST['judul'] ?? '');
$deskripsi      = trim($_POST['deskripsi'] ?? '');
$tanggalMulai   = trim($_POST['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($_POST['tanggal_selesai'] ?? '');
$status         = ($_POST['status'] ?? 'draf') === 'terbit' ? 'terbit' : 'draf';
$adminId        = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

if ($bidangId <= 0 || $judul === '' || $tanggalMulai === '' || $tanggalSelesai === '') {
    header("Location: kegiatan.php?notif=gagal_kosong");
    exit;
}

if (strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
    header("Location: kegiatan.php?notif=gagal_tanggal");
    exit;
}

// ==== Slug otomatis & unik ====
$slugDasar = buatSlug($judul);
if ($slugDasar === '') $slugDasar = 'kegiatan';
$slug   = $slugDasar;
$urutan = 1;
while (true) {
    $slugEsc = mysqli_real_escape_string($koneksi, $slug);
    $cek = mysqli_query($koneksi, "SELECT id FROM kegiatan WHERE slug = '$slugEsc' LIMIT 1");
    if ($cek && mysqli_num_rows($cek) === 0) break;
    $urutan++;
    $slug = $slugDasar . '-' . $urutan;
}

// ==== Upload gambar (opsional) ====
$namaGambar = null;
if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $folderUpload     = 'img/kegiatan/';
    $folderUploadPath = __DIR__ . '/../' . $folderUpload;
    if (!is_dir($folderUploadPath)) mkdir($folderUploadPath, 0755, true);

    $ekstensi      = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ekstensi, $ekstensiValid) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'kegiatan_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUploadPath . $namaFile)) {
            $namaGambar = $folderUpload . $namaFile;
        }
    }
}

// ==== Simpan ke database ====
$judulEsc      = mysqli_real_escape_string($koneksi, $judul);
$slugEsc       = mysqli_real_escape_string($koneksi, $slug);
$deskripsiEsc  = mysqli_real_escape_string($koneksi, $deskripsi);
$mulaiEsc      = mysqli_real_escape_string($koneksi, $tanggalMulai);
$selesaiEsc    = mysqli_real_escape_string($koneksi, $tanggalSelesai);
$gambarVal     = $namaGambar ? "'" . mysqli_real_escape_string($koneksi, $namaGambar) . "'" : "NULL";
$adminVal      = $adminId ? $adminId : "NULL";

$sql = "INSERT INTO kegiatan (bidang_id, admin_id, judul, slug, deskripsi, gambar, tanggal_mulai, tanggal_selesai, status)
        VALUES ($bidangId, $adminVal, '$judulEsc', '$slugEsc', '$deskripsiEsc', $gambarVal, '$mulaiEsc', '$selesaiEsc', '$status')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kegiatan.php?notif=sukses_tambah");
} else {
    header("Location: kegiatan.php?notif=gagal_simpan");
}
exit;
