<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pengumuman.php");
    exit;
}

$id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$judul   = trim($_POST['judul'] ?? '');
$isi     = trim($_POST['isi'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');
$status  = ($_POST['status'] ?? 'draf') === 'terbit' ? 'terbit' : 'draf';

if ($id <= 0) {
    header("Location: pengumuman.php?notif=not_found");
    exit;
}

if ($judul === '' || $isi === '' || $tanggal === '') {
    header("Location: pengumuman.php?notif=gagal_kosong");
    exit;
}

// Ambil data pengumuman saat ini (untuk gambar lama)
$q = mysqli_query($koneksi, "SELECT gambar FROM pengumuman WHERE id = $id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: pengumuman.php?notif=not_found");
    exit;
}
$dataLama = mysqli_fetch_assoc($q);

// ==== Upload gambar baru (opsional, replace gambar lama jika ada) ====
// Folder fisik & nama file yang disimpan ke DB disamakan dengan proses_tambah_pengumuman.php:
// hanya nama file polos (tanpa folder), karena pengumuman.php menampilkannya sebagai
// '../img/pengumuman/' . $row['gambar']
$folderUpload = __DIR__ . '/../img/pengumuman/';
$namaGambar   = $dataLama['gambar']; // default: pakai gambar lama

if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($folderUpload)) {
        mkdir($folderUpload, 0755, true);
    }

    $ekstensi      = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ekstensi, $ekstensiValid) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'pengumuman_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUpload . $namaFile)) {
            // Hapus gambar lama supaya tidak menumpuk file yatim
            if (!empty($dataLama['gambar']) && file_exists($folderUpload . $dataLama['gambar'])) {
                @unlink($folderUpload . $dataLama['gambar']);
            }
            $namaGambar = $namaFile;
        }
    }
}

$judulEsc   = mysqli_real_escape_string($koneksi, $judul);
$isiEsc     = mysqli_real_escape_string($koneksi, $isi);
$tanggalEsc = mysqli_real_escape_string($koneksi, $tanggal);
$gambarVal  = $namaGambar ? "'" . mysqli_real_escape_string($koneksi, $namaGambar) . "'" : "NULL";

$sql = "UPDATE pengumuman SET
            judul = '$judulEsc',
            isi = '$isiEsc',
            tanggal = '$tanggalEsc',
            status = '$status',
            gambar = $gambarVal
        WHERE id = $id";

if (mysqli_query($koneksi, $sql)) {
    header("Location: pengumuman.php?notif=sukses_edit");
} else {
    header("Location: pengumuman.php?notif=gagal_simpan");
}
exit;
