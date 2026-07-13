<?php
session_start();
include_once __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$aksi = $_POST['aksi'] ?? '';

// ========================
// TAMBAH
// ========================
if ($aksi === 'tambah') {
    $nama  = trim($_POST['nama']  ?? '');
    $tugas = trim($_POST['tugas'] ?? '');
    $fungsi = trim($_POST['fungsi'] ?? '');

    if ($nama === '') {
        header("Location: bidang.php?notif=gagal_kosong");
        exit;
    }

    $n = mysqli_real_escape_string($koneksi, $nama);
    $t = mysqli_real_escape_string($koneksi, $tugas);
    $f = mysqli_real_escape_string($koneksi, $fungsi);

    $sql = "INSERT INTO bidang (nama, tugas, fungsi, created_at, updated_at)
            VALUES ('$n', '$t', '$f', NOW(), NOW())";

    if (mysqli_query($koneksi, $sql)) {
        header("Location: bidang.php?notif=sukses_tambah");
    } else {
        header("Location: bidang.php?notif=gagal_simpan");
    }
    exit;
}

// ========================
// EDIT
// ========================
if ($aksi === 'edit') {
    $id    = (int)($_POST['id'] ?? 0);
    $nama  = trim($_POST['nama']  ?? '');
    $tugas = trim($_POST['tugas'] ?? '');
    $fungsi = trim($_POST['fungsi'] ?? '');

    if ($id === 0 || $nama === '') {
        header("Location: bidang.php?notif=gagal_kosong");
        exit;
    }

    $n = mysqli_real_escape_string($koneksi, $nama);
    $t = mysqli_real_escape_string($koneksi, $tugas);
    $f = mysqli_real_escape_string($koneksi, $fungsi);

    $sql = "UPDATE bidang SET nama='$n', tugas='$t', fungsi='$f', updated_at=NOW() WHERE id=$id";

    if (mysqli_query($koneksi, $sql)) {
        header("Location: bidang.php?notif=sukses_edit");
    } else {
        header("Location: bidang.php?notif=gagal_simpan");
    }
    exit;
}

// ========================
// HAPUS
// ========================
if ($aksi === 'hapus') {
    $id = (int)($_POST['id'] ?? 0);

    if ($id === 0) {
        header("Location: bidang.php?notif=gagal_hapus");
        exit;
    }

    // Cek apakah masih ada pegawai aktif di bidang ini
    $cek = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM pegawai WHERE bidang_id = $id AND status = 'aktif'");
    $jumlah = mysqli_fetch_assoc($cek)['jml'] ?? 0;

    if ($jumlah > 0) {
        header("Location: bidang.php?notif=gagal_pegawai");
        exit;
    }

    if (mysqli_query($koneksi, "DELETE FROM bidang WHERE id = $id")) {
        header("Location: bidang.php?notif=sukses_hapus");
    } else {
        header("Location: bidang.php?notif=gagal_hapus");
    }
    exit;
}

header("Location: bidang.php");
exit;
