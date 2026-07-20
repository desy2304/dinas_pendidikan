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

$id             = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$bidangId       = !empty($_POST['bidang_id']) ? (int)$_POST['bidang_id'] : 0;
$judul          = trim($_POST['judul'] ?? '');
$deskripsi      = trim($_POST['deskripsi'] ?? '');
$tanggalMulai   = trim($_POST['tanggal_mulai'] ?? '');
$tanggalSelesai = trim($_POST['tanggal_selesai'] ?? '');
$status         = ($_POST['status'] ?? 'draf') === 'terbit' ? 'terbit' : 'draf';

if ($id <= 0) {
    header("Location: kegiatan.php?notif=not_found");
    exit;
}

if ($bidangId <= 0 || $judul === '' || $tanggalMulai === '' || $tanggalSelesai === '') {
    header("Location: kegiatan.php?notif=gagal_kosong");
    exit;
}

if (strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
    header("Location: kegiatan.php?notif=gagal_tanggal");
    exit;
}

// Ambil data lama (untuk gambar)
$q = mysqli_query($koneksi, "SELECT gambar FROM kegiatan WHERE id = $id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: kegiatan.php?notif=not_found");
    exit;
}
$dataLama = mysqli_fetch_assoc($q);

// ==== Upload gambar baru (opsional, replace gambar lama jika ada) ====
$namaGambar = $dataLama['gambar'];
if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $folderUpload     = 'img/kegiatan/';
    $folderUploadPath = __DIR__ . '/../' . $folderUpload;
    if (!is_dir($folderUploadPath)) mkdir($folderUploadPath, 0755, true);

    $ekstensi      = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ekstensi, $ekstensiValid) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'kegiatan_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUploadPath . $namaFile)) {
            if (!empty($dataLama['gambar']) && file_exists(__DIR__ . '/../' . $dataLama['gambar'])) {
                @unlink(__DIR__ . '/../' . $dataLama['gambar']);
            }
            $namaGambar = $folderUpload . $namaFile;
        }
    }
}

$judulEsc     = mysqli_real_escape_string($koneksi, $judul);
$deskripsiEsc = mysqli_real_escape_string($koneksi, $deskripsi);
$mulaiEsc     = mysqli_real_escape_string($koneksi, $tanggalMulai);
$selesaiEsc   = mysqli_real_escape_string($koneksi, $tanggalSelesai);
$gambarVal    = $namaGambar ? "'" . mysqli_real_escape_string($koneksi, $namaGambar) . "'" : "NULL";

// Slug SENGAJA tidak diubah saat edit, supaya link publik yang sudah dibagikan tidak rusak
$sql = "UPDATE kegiatan SET
            bidang_id = $bidangId,
            judul = '$judulEsc',
            deskripsi = '$deskripsiEsc',
            tanggal_mulai = '$mulaiEsc',
            tanggal_selesai = '$selesaiEsc',
            status = '$status',
            gambar = $gambarVal
        WHERE id = $id";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kegiatan.php?notif=sukses_edit");
} else {
    header("Location: kegiatan.php?notif=gagal_simpan");
}
exit;
