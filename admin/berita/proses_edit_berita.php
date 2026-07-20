<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: berita.php");
    exit;
}

$id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$judul      = trim($_POST['judul'] ?? '');
$isi        = trim($_POST['isi'] ?? '');
$kategoriId = !empty($_POST['kategori_id']) ? (int)$_POST['kategori_id'] : null;
$status     = ($_POST['status'] ?? 'draf') === 'terbit' ? 'terbit' : 'draf';

$tanggalInput = trim($_POST['tanggal_publish'] ?? '');
$tanggalValid = (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalInput);

if ($id <= 0) {
    header("Location: berita.php?notif=not_found");
    exit;
}

if ($judul === '' || $isi === '') {
    header("Location: berita.php?notif=gagal_kosong");
    exit;
}

// Ambil data berita saat ini (untuk gambar lama & status tanggal_publish)
$q = mysqli_query($koneksi, "SELECT gambar, tanggal_publish, status AS status_lama FROM berita WHERE id = $id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: berita.php?notif=not_found");
    exit;
}
$dataLama = mysqli_fetch_assoc($q);

// ==== Upload gambar baru (opsional) ====
$namaGambar = $dataLama['gambar'];
if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    $folderUpload = 'img/berita/';
    $folderUploadPath = __DIR__ . '/../' . $folderUpload;
    if (!is_dir($folderUploadPath)) {
        mkdir($folderUploadPath, 0755, true);
    }
    $ekstensi = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];
    if (in_array($ekstensi, $ekstensiValid) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'berita_' . time() . '_' . rand(100,999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUploadPath . $namaFile)) {
            // hapus gambar lama
            if (!empty($dataLama['gambar'])) {
                $gambarLama = __DIR__ . '/../img/berita/' . $dataLama['gambar'];
                if (file_exists($gambarLama)) {
                    unlink($gambarLama);
                }
            }
            $namaGambar = $namaFile;
        }
    }
}

// Tentukan tanggal_publish:
// 1) Pakai yang diisi manual di form kalau valid.
// 2) Kalau tidak diisi/tidak valid: pertahankan tanggal lama, atau isi otomatis
//    hari ini kalau baru pertama kali berstatus "terbit" dan belum pernah ada tanggalnya.
if ($tanggalValid) {
    $tanggalPublish = $tanggalInput;
} else {
    $tanggalPublish = $dataLama['tanggal_publish'];
    if ($status === 'terbit' && empty($tanggalPublish)) {
        $tanggalPublish = date('Y-m-d');
    }
}

$judulEsc  = mysqli_real_escape_string($koneksi, $judul);
$isiEsc    = mysqli_real_escape_string($koneksi, $isi);
$gambarVal = $namaGambar ? "'" . mysqli_real_escape_string($koneksi, $namaGambar) . "'" : "NULL";
$kategoriVal = $kategoriId ? $kategoriId : "NULL";
$tanggalVal  = $tanggalPublish ? "'" . $tanggalPublish . "'" : "NULL";

$sql = "UPDATE berita SET
            judul = '$judulEsc',
            isi = '$isiEsc',
            kategori_id = $kategoriVal,
            status = '$status',
            gambar = $gambarVal,
            tanggal_publish = $tanggalVal
        WHERE id = $id";

if (mysqli_query($koneksi, $sql)) {
    header("Location: berita.php?notif=sukses_edit");
} else {
    header("Location: berita.php?notif=gagal_simpan");
}
exit;
