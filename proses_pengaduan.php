<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?page=home');
    exit;
}

$nama     = trim($_POST['nama'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telepon  = trim($_POST['telepon'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$judul    = trim($_POST['judul'] ?? '');
$isi      = trim($_POST['isi'] ?? '');

$kategori_valid = ['sarana_prasarana', 'kepegawaian', 'pelayanan', 'lainnya'];

// Validasi dasar — kalau tidak lengkap, kembali ke form dengan pesan error
if ($nama === '' || $judul === '' || $isi === '' || !in_array($kategori, $kategori_valid, true)) {
    header('Location: index.php?page=home&pengaduan_error=1#pengaduan');
    exit;
}

// Upload lampiran (opsional) — maksimal 2MB, hanya jpg/jpeg/png/pdf
$lampiran = null;
if (!empty($_FILES['lampiran']['name']) && $_FILES['lampiran']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf'];

    if (in_array($ext, $allowed_ext, true) && $_FILES['lampiran']['size'] <= 2 * 1024 * 1024) {
        $target_dir = 'uploads/pengaduan/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        $lampiran = 'lampiran_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        move_uploaded_file($_FILES['lampiran']['tmp_name'], $target_dir . $lampiran);
    }
}

// Generate nomor tiket berurutan: TK-001, TK-002, dst
function generate_no_tiket(mysqli $conn) {
    $result = $conn->query("SELECT no_tiket FROM pengaduan WHERE no_tiket LIKE 'TK-%' ORDER BY id DESC LIMIT 1");
    $last = $result ? $result->fetch_assoc() : null;

    $next_number = 1;
    if ($last && preg_match('/^TK-(\d+)$/', $last['no_tiket'], $m)) {
        $next_number = (int) $m[1] + 1;
    }

    return 'TK-' . str_pad($next_number, 3, '0', STR_PAD_LEFT);
}

$no_tiket = generate_no_tiket($conn);

$stmt = $conn->prepare(
    "INSERT INTO pengaduan (nama, email, telepon, no_tiket, kategori, judul, isi, lampiran, status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'diajukan')"
);
$stmt->bind_param('ssssssss', $nama, $email, $telepon, $no_tiket, $kategori, $judul, $isi, $lampiran);
$stmt->execute();
$stmt->close();

header('Location: index.php?page=pengaduan&no_tiket=' . urlencode($no_tiket) . '&new=1');
exit;