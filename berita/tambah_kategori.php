<?php
// Tampung semua output (termasuk warning/error PHP) supaya tidak merusak format JSON
ob_start();
session_start();

$GLOBALS['__jsonSent'] = false;

function kirimJson($data)
{
    if (ob_get_length() !== false) {
        ob_end_clean();
    }
    header('Content-Type: application/json');
    echo json_encode($data);
    $GLOBALS['__jsonSent'] = true;
    exit;
}

// Jaring pengaman terakhir: kalau ada error fatal / die() yang tidak lewat kirimJson(),
// tetap kirim JSON yang valid (bukan halaman HTML/putih) supaya JS bisa membaca pesannya.
register_shutdown_function(function () {
    if (!$GLOBALS['__jsonSent']) {
        $leftover = '';
        if (ob_get_length() !== false) {
            $leftover = ob_get_clean();
        }
        $pesan = trim(strip_tags($leftover));
        if ($pesan === '') {
            $pesan = 'Terjadi kesalahan tak terduga di server.';
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode(['success' => false, 'message' => $pesan]);
    }
});

if (!isset($_SESSION['user'])) {
    kirimJson(['success' => false, 'message' => 'Sesi login berakhir, silakan login ulang lalu coba lagi.']);
}

include __DIR__ . '/../koneksi.php';

if (empty($koneksi)) {
    kirimJson(['success' => false, 'message' => 'Koneksi database gagal. Cek pengaturan di koneksi.php.']);
}

function buatSlugKategori($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9]+/', '-', $string);
    return trim($string, '-');
}

$nama = trim($_POST['nama'] ?? '');

if ($nama === '') {
    kirimJson(['success' => false, 'message' => 'Nama kategori wajib diisi.']);
}

if (mb_strlen($nama) > 100) {
    kirimJson(['success' => false, 'message' => 'Nama kategori maksimal 100 karakter.']);
}

// Cek duplikasi nama (tidak peka huruf besar/kecil)
$namaEsc = mysqli_real_escape_string($koneksi, $nama);
$cekNama = mysqli_query($koneksi, "SELECT id FROM kategori_berita WHERE LOWER(nama) = LOWER('$namaEsc') LIMIT 1");

if ($cekNama === false) {
    kirimJson(['success' => false, 'message' => 'Query gagal (cek nama): ' . mysqli_error($koneksi)]);
}

if (mysqli_num_rows($cekNama) > 0) {
    kirimJson(['success' => false, 'message' => 'Kategori dengan nama tersebut sudah ada.']);
}

// Generate slug unik
$slugDasar = buatSlugKategori($nama);
if ($slugDasar === '') {
    $slugDasar = 'kategori';
}
$slug   = $slugDasar;
$urutan = 1;
while (true) {
    $slugEsc = mysqli_real_escape_string($koneksi, $slug);
    $cek = mysqli_query($koneksi, "SELECT id FROM kategori_berita WHERE slug = '$slugEsc' LIMIT 1");
    if ($cek === false) {
        kirimJson(['success' => false, 'message' => 'Query gagal (cek slug): ' . mysqli_error($koneksi)]);
    }
    if (mysqli_num_rows($cek) === 0) {
        break;
    }
    $urutan++;
    $slug = $slugDasar . '-' . $urutan;
}

$sql = "INSERT INTO kategori_berita (nama, slug) VALUES ('$namaEsc', '$slugEsc')";

if (mysqli_query($koneksi, $sql)) {
    kirimJson([
        'success' => true,
        'id'      => mysqli_insert_id($koneksi),
        'nama'    => $nama,
    ]);
} else {
    kirimJson(['success' => false, 'message' => 'Gagal menyimpan kategori: ' . mysqli_error($koneksi)]);
}
