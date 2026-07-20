<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pegawai.php");
    exit;
}

$id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$nama     = trim($_POST['nama'] ?? '');
$nip      = trim($_POST['nip'] ?? '');
$jabatan  = trim($_POST['jabatan'] ?? '');
$email    = trim($_POST['email'] ?? '');
$status   = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
$bidangId = (!empty($_POST['bidang_id']) && is_numeric($_POST['bidang_id'])) ? (int)$_POST['bidang_id'] : null;

if ($id <= 0) {
    header("Location: pegawai.php?notif=not_found");
    exit;
}

if ($nama === '' || $jabatan === '') {
    header("Location: pegawai.php?notif=gagal_kosong");
    exit;
}

// Ambil data pegawai saat ini (untuk foto lama)
$q = mysqli_query($koneksi, "SELECT foto FROM pegawai WHERE id = $id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: pegawai.php?notif=not_found");
    exit;
}
$dataLama = mysqli_fetch_assoc($q);

// ==== Cek NIP unik, kecuali milik pegawai ini sendiri ====
if ($nip !== '') {
    $nipEsc = mysqli_real_escape_string($koneksi, $nip);
    $cek = mysqli_query($koneksi, "SELECT id FROM pegawai WHERE nip = '$nipEsc' AND id != $id LIMIT 1");
    if ($cek && mysqli_num_rows($cek) > 0) {
        header("Location: pegawai.php?notif=gagal_nip");
        exit;
    }
}

// ==== Upload foto baru (opsional, replace foto lama jika ada) ====
$folderUpload = __DIR__ . '/../img/pegawai/';
$namaFoto     = $dataLama['foto']; // default: pakai foto lama

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($folderUpload)) {
        mkdir($folderUpload, 0755, true);
    }

    $ekstensi      = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ekstensi, $ekstensiValid) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'pegawai_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $folderUpload . $namaFile)) {
            // Hapus foto lama supaya tidak menumpuk file yatim
            if (!empty($dataLama['foto']) && file_exists($folderUpload . $dataLama['foto'])) {
                @unlink($folderUpload . $dataLama['foto']);
            }
            $namaFoto = $namaFile;
        }
    }
}

$namaEsc    = mysqli_real_escape_string($koneksi, $nama);
$nipEsc     = $nip !== '' ? "'" . mysqli_real_escape_string($koneksi, $nip) . "'" : "NULL";
$jabatanEsc = mysqli_real_escape_string($koneksi, $jabatan);
$emailEsc   = $email !== '' ? "'" . mysqli_real_escape_string($koneksi, $email) . "'" : "NULL";
$fotoVal    = $namaFoto ? "'" . mysqli_real_escape_string($koneksi, $namaFoto) . "'" : "NULL";
$bidangVal  = $bidangId ? $bidangId : "NULL";

$sql = "UPDATE pegawai SET
            bidang_id = $bidangVal,
            nama = '$namaEsc',
            nip = $nipEsc,
            jabatan = '$jabatanEsc',
            email = $emailEsc,
            status = '$status',
            foto = $fotoVal
        WHERE id = $id";

if (mysqli_query($koneksi, $sql)) {
    header("Location: pegawai.php?notif=sukses_edit");
} else {
    header("Location: pegawai.php?notif=gagal_simpan");
}
exit;
