<?php
session_start();
include __DIR__ . '/../koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

function buatSlugPengumuman($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    return trim($string, '-');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: pengumuman.php");
    exit;
}

$judul   = trim($_POST['judul'] ?? '');
$isi     = trim($_POST['isi'] ?? '');
$tanggal = trim($_POST['tanggal'] ?? '');
$status = ($_POST['status'] === 'terbit') ? 'terbit' : 'draf';
$adminId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

if ($judul === '' || $isi === '' || $tanggal === '') {
    header("Location: pengumuman.php?notif=gagal_kosong");
    exit;
}

// ==== Generate slug unik ====
$slugDasar = buatSlugPengumuman($judul);
if ($slugDasar === '') {
    $slugDasar = 'pengumuman';
}
$slug   = $slugDasar;
$urutan = 1;
while (true) {
    $slugEsc = mysqli_real_escape_string($koneksi, $slug);
    $cek = mysqli_query($koneksi, "SELECT id FROM pengumuman WHERE slug = '$slugEsc' LIMIT 1");
    if ($cek && mysqli_num_rows($cek) === 0) {
        break;
    }
    $urutan++;
    $slug = $slugDasar . '-' . $urutan;
}

// ==== Upload gambar (opsional) ====
// Folder fisik: <project_root>/img/pengumuman/  (script ini ada di subfolder /pengumuman/,
// jadi path fisik harus relatif ke __DIR__, bukan ke direktori kerja saat request)
$namaGambar   = null; // yang disimpan ke DB HANYA nama file polos, tanpa folder (samakan dengan cara pengumuman.php menampilkannya)
$folderUpload = __DIR__ . '/../img/pengumuman/';

if (!empty($_FILES['gambar']['name']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($folderUpload)) {
        mkdir($folderUpload, 0755, true);
    }

    $ekstensi      = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
    $ekstensiValid = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ekstensi, $ekstensiValid) && $_FILES['gambar']['size'] <= 2 * 1024 * 1024) {
        $namaFile = 'pengumuman_' . time() . '_' . rand(100, 999) . '.' . $ekstensi;
        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $folderUpload . $namaFile)) {
            $namaGambar = $namaFile;
        }
    }
}

// ==== Simpan ke database ====
$judulEsc   = mysqli_real_escape_string($koneksi, $judul);
$isiEsc     = mysqli_real_escape_string($koneksi, $isi);
$slugEsc    = mysqli_real_escape_string($koneksi, $slug);
$tanggalEsc = mysqli_real_escape_string($koneksi, $tanggal);

$gambarVal = $namaGambar ? "'" . mysqli_real_escape_string($koneksi, $namaGambar) . "'" : "NULL";
$adminVal  = $adminId ? $adminId : "NULL";

$sql = "INSERT INTO pengumuman (admin_id, judul, slug, isi, gambar, tanggal, status)
        VALUES ($adminVal, '$judulEsc', '$slugEsc', '$isiEsc', $gambarVal, '$tanggalEsc', '$status')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: pengumuman.php?notif=sukses_tambah");
} else {
    header("Location: pengumuman.php?notif=gagal_simpan");
}
exit;
