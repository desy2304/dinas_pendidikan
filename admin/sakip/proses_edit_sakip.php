<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: sakip.php");
    exit;
}

$kategoriValid = ['renstra_pk', 'lkjip', 'iku'];

$id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$kategori   = $_POST['kategori'] ?? '';
$judul      = trim($_POST['judul'] ?? '');
$tahun      = trim($_POST['tahun'] ?? '');
$keterangan = trim($_POST['keterangan'] ?? '');

if ($id <= 0) {
    header("Location: sakip.php?notif=not_found");
    exit;
}

if (!in_array($kategori, $kategoriValid, true) || $judul === '' || !ctype_digit($tahun)) {
    header("Location: sakip.php?notif=gagal_kosong");
    exit;
}

// Ambil data lama
$q = mysqli_query($koneksi, "SELECT file FROM sakip WHERE id = $id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    header("Location: sakip.php?notif=not_found");
    exit;
}
$dataLama = mysqli_fetch_assoc($q);

// ==== Upload file baru (opsional, replace yang lama jika ada) ====
$namaSimpan = $dataLama['file'];
if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $folderUpload     = 'files/sakip/';
    $folderUploadPath = __DIR__ . '/../' . $folderUpload;
    if (!is_dir($folderUploadPath)) {
        mkdir($folderUploadPath, 0755, true);
    }

    $ekstensi = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    if ($ekstensi === 'pdf' && $_FILES['file']['size'] <= 5 * 1024 * 1024) {
        $namaFile = 'sakip_' . $kategori . '_' . time() . '_' . rand(100, 999) . '.pdf';
        if (move_uploaded_file($_FILES['file']['tmp_name'], $folderUploadPath . $namaFile)) {
            if (!empty($dataLama['file']) && file_exists(__DIR__ . '/../' . $dataLama['file'])) {
                @unlink(__DIR__ . '/../' . $dataLama['file']);
            }
            $namaSimpan = $folderUpload . $namaFile;
        }
    }
}

$kategoriEsc   = mysqli_real_escape_string($koneksi, $kategori);
$judulEsc      = mysqli_real_escape_string($koneksi, $judul);
$tahunEsc      = (int)$tahun;
$keteranganEsc = mysqli_real_escape_string($koneksi, $keterangan);
$fileEsc       = mysqli_real_escape_string($koneksi, $namaSimpan);

$sql = "UPDATE sakip SET
            kategori = '$kategoriEsc',
            judul = '$judulEsc',
            tahun = $tahunEsc,
            keterangan = '$keteranganEsc',
            file = '$fileEsc'
        WHERE id = $id";

if (mysqli_query($koneksi, $sql)) {
    header("Location: sakip.php?notif=sukses_edit");
} else {
    header("Location: sakip.php?notif=gagal_simpan");
}
exit;
