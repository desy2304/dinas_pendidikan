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

$nama     = trim($_POST['nama'] ?? '');
$nip      = trim($_POST['nip'] ?? '');
$jabatan  = trim($_POST['jabatan'] ?? '');
$email    = trim($_POST['email'] ?? '');
$status   = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';
$bidangId = (!empty($_POST['bidang_id']) && is_numeric($_POST['bidang_id'])) ? (int)$_POST['bidang_id'] : null;

if ($nama === '' || $jabatan === '') {
    header("Location: pegawai.php?notif=gagal_kosong");
    exit;
}

// ==== Cek NIP unik (kolom nip UNIQUE di database) ====
if ($nip !== '') {
    $nipEsc = mysqli_real_escape_string($koneksi, $nip);
    $cek = mysqli_query($koneksi, "SELECT id FROM pegawai WHERE nip = '$nipEsc' LIMIT 1");
    if ($cek && mysqli_num_rows($cek) > 0) {
        header("Location: pegawai.php?notif=gagal_nip");
        exit;
    }
}

// ==== Upload foto (opsional) ====
$namaFoto     = null; // disimpan ke DB hanya nama file polos, folder fisik terpisah
$folderUpload = __DIR__ . '/../img/pegawai/';

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($folderUpload)) {
        mkdir($folderUpload, 0755, true);
    }

    $ekstensi      = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ekstensi, $ekstensiValid) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'pegawai_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $folderUpload . $namaFile)) {
            $namaFoto = $namaFile;
        }
    }
}

// ==== Simpan ke database ====
$namaEsc    = mysqli_real_escape_string($koneksi, $nama);
$nipEsc     = $nip !== '' ? "'" . mysqli_real_escape_string($koneksi, $nip) . "'" : "NULL";
$jabatanEsc = mysqli_real_escape_string($koneksi, $jabatan);
$emailEsc   = $email !== '' ? "'" . mysqli_real_escape_string($koneksi, $email) . "'" : "NULL";
$fotoVal    = $namaFoto ? "'" . mysqli_real_escape_string($koneksi, $namaFoto) . "'" : "NULL";
$bidangVal  = $bidangId ? $bidangId : "NULL";

$sql = "INSERT INTO pegawai (bidang_id, nama, nip, jabatan, foto, email, status)
        VALUES ($bidangVal, '$namaEsc', $nipEsc, '$jabatanEsc', $fotoVal, $emailEsc, '$status')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: pegawai.php?notif=sukses_tambah");
} else {
    header("Location: pegawai.php?notif=gagal_simpan");
}
exit;
