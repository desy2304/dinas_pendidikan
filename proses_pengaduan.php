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

// Generate nomor tiket unik: PGD-YYYYMMDD-XXXX
function generate_no_tiket($conn) {
    $karakter = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // hindari karakter ambigu (0/O, 1/I)
    do {
        $acak = '';
        for ($i = 0; $i < 4; $i++) {
            $acak .= $karakter[random_int(0, strlen($karakter) - 1)];
        }
        $no_tiket = 'PGD-' . date('Ymd') . '-' . $acak;

        $stmt = $conn->prepare("SELECT id FROM pengaduan WHERE no_tiket = ?");
        $stmt->bind_param('s', $no_tiket);
        $stmt->execute();
        $ada = $stmt->get_result()->num_rows > 0;
        $stmt->close();
    } while ($ada);

    return $no_tiket;
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
