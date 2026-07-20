<?php
session_start();
include __DIR__ . '/koneksi.php';

if (!isset($_SESSION['user'])) {
    header("Location: login/login.php");
    exit();
}

// Library FPDF -- taruh folder vendor_fpdf/ (berisi fpdf.php + folder font/) di root project,
// sejajar dengan koneksi.php dan index.php.
require __DIR__ . '/vendor_fpdf/fpdf.php';

// ==== Label kategori & status (sama seperti di index.php) ====
$kategoriLabel = [
    'sarana_prasarana' => 'Sarana & Prasarana',
    'kepegawaian'      => 'Kepegawaian',
    'pelayanan'        => 'Pelayanan',
    'lainnya'          => 'Lainnya',
];
$statusLabel = [
    'diajukan'   => 'Diajukan',
    'diproses'   => 'Diproses',
    'ditanggapi' => 'Ditanggapi',
    'ditutup'    => 'Selesai',
];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Data pengaduan tidak ditemukan.');
}

// ==== Ambil data pengaduan ====
$q = mysqli_query($koneksi, "SELECT id, no_tiket, nama, email, telepon, kategori, judul, isi, lampiran, status, created_at
                              FROM pengaduan WHERE id = $id LIMIT 1");
if (!$q || mysqli_num_rows($q) === 0) {
    die('Data pengaduan tidak ditemukan.');
}
$p = mysqli_fetch_assoc($q);

// ==== Ambil riwayat tanggapan (join ke admin untuk nama yang membalas) ====
$daftarTanggapan = [];
$qt = mysqli_query($koneksi, "SELECT tanggapan_pengaduan.isi, tanggapan_pengaduan.created_at,
                                      admin.name AS nama_admin
                               FROM tanggapan_pengaduan
                               LEFT JOIN admin ON tanggapan_pengaduan.admin_id = admin.id
                               WHERE tanggapan_pengaduan.pengaduan_id = $id
                               ORDER BY tanggapan_pengaduan.created_at ASC");
if ($qt) {
    while ($row = mysqli_fetch_assoc($qt)) {
        $daftarTanggapan[] = $row;
    }
}

// ==== Helper bersihkan karakter yang tidak didukung font default FPDF (Latin-1) ====
function txt($str)
{
    $str = (string)$str;
    // FPDF core font pakai encoding Windows-1252/Latin-1, bukan UTF-8
    $converted = @iconv('UTF-8', 'CP1252//TRANSLIT//IGNORE', $str);
    return $converted !== false ? $converted : $str;
}

// ==== Bangun dokumen PDF ====
class LaporanPengaduanPDF extends FPDF
{
    public $judulLaporan = 'Laporan Detail Pengaduan';

    function Header()
    {
        $this->SetFont('Arial', 'B', 13);
        $this->Cell(0, 6, txt('DINAS PENDIDIKAN KABUPATEN SUMENEP'), 0, 1, 'C');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, txt($this->judulLaporan), 0, 1, 'C');
        $this->Ln(2);
        $this->SetDrawColor(22, 47, 85);
        $this->SetLineWidth(0.6);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 10, txt('Dicetak pada ' . date('d-m-Y H:i') . ' oleh sistem admin Disdik Sumenep - Halaman ' . $this->PageNo()), 0, 0, 'C');
    }

    function baris($label, $isi)
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(40, 7, txt($label), 0, 0);
        $this->Cell(4, 7, ':', 0, 0);
        $this->SetFont('Arial', '', 10);
        $this->MultiCell(0, 7, txt($isi));
    }

    function subjudul($teks)
    {
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(240, 244, 250);
        $this->Cell(0, 8, '  ' . txt($teks), 0, 1, 'L', true);
        $this->Ln(1);
    }
}

$pdf = new LaporanPengaduanPDF();
$pdf->AddPage();

// ==== Ringkasan pengaduan ====
$pdf->subjudul('Data Pengaduan');
$pdf->baris('No. Tiket', $p['no_tiket']);
$pdf->baris('Nama Pengadu', $p['nama']);
$pdf->baris('Email', $p['email'] ?: '-');
$pdf->baris('Telepon', $p['telepon'] ?: '-');
$pdf->baris('Kategori', $kategoriLabel[$p['kategori']] ?? $p['kategori']);
$pdf->baris('Status Saat Ini', $statusLabel[$p['status']] ?? $p['status']);
$pdf->baris('Tanggal Masuk', date('d-m-Y H:i', strtotime($p['created_at'])));

$pdf->subjudul('Isi Pengaduan');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, txt($p['judul']), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, txt($p['isi']));

if (!empty($p['lampiran'])) {
    $pdf->Ln(1);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 6, txt('Lampiran: ' . $p['lampiran']), 0, 1);
}

// ==== Riwayat tanggapan ====
$pdf->subjudul('Riwayat Tanggapan (' . count($daftarTanggapan) . ')');
if (empty($daftarTanggapan)) {
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 7, txt('Belum ada tanggapan untuk pengaduan ini.'), 0, 1);
} else {
    foreach ($daftarTanggapan as $i => $t) {
        $pdf->SetFont('Arial', 'B', 9.5);
        $namaAdmin = $t['nama_admin'] ?: 'Admin';
        $pdf->Cell(0, 6, txt(($i + 1) . '. ' . $namaAdmin . ' - ' . date('d-m-Y H:i', strtotime($t['created_at']))), 0, 1);
        $pdf->SetFont('Arial', '', 9.5);
        $pdf->SetX($pdf->GetX() + 4);
        $pdf->MultiCell(0, 5.5, txt($t['isi']));
        $pdf->Ln(1);
    }
}

// ==== Output: langsung diunduh sebagai file ====
$namaFile = 'laporan_pengaduan_' . preg_replace('/[^A-Za-z0-9_-]/', '', $p['no_tiket']) . '.pdf';
$pdf->Output('D', $namaFile); // 'D' = force download
exit;
