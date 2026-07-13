<?php
$no_tiket = isset($_GET['no_tiket']) ? trim($_GET['no_tiket']) : '';
$is_new   = isset($_GET['new']) && $_GET['new'] == '1';

$tiket = null;
$tanggapan_list = [];

if ($no_tiket !== '') {
    $stmt = $conn->prepare("SELECT * FROM pengaduan WHERE no_tiket = ?");
    $stmt->bind_param('s', $no_tiket);
    $stmt->execute();
    $tiket = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($tiket) {
        $stmt2 = $conn->prepare("SELECT * FROM tanggapan_pengaduan WHERE pengaduan_id = ? ORDER BY created_at ASC");
        $stmt2->bind_param('i', $tiket['id']);
        $stmt2->execute();
        $tanggapan_list = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();
    }
}

$kategori_label = [
    'sarana_prasarana' => 'Sarana & Prasarana',
    'kepegawaian'       => 'Kepegawaian',
    'pelayanan'         => 'Pelayanan',
    'lainnya'           => 'Lainnya',
];
$status_label = [
    'diajukan'   => 'Diajukan',
    'diproses'   => 'Diproses',
    'ditanggapi' => 'Ditanggapi',
    'ditutup'    => 'Ditutup',
];
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" style="display:inline-flex;align-items:center;gap:6px;color:var(--navy);font-weight:600;font-size:14px;text-decoration:none;margin-bottom:20px">&larr; Kembali ke Beranda</a>
    <div class="section-label">Layanan Aspirasi</div>
    <div class="section-title">Cek Status Pengaduan</div>
    <p class="section-sub">Masukkan nomor tiket yang Anda terima saat mengirim pengaduan untuk melihat status dan tanggapan terbaru.</p>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner" style="max-width:760px">

    <?php if ($is_new && $tiket): ?>
      <div class="tiket-alert-success reveal" style="margin-bottom:24px">
        <strong>Pengaduan berhasil dikirim!</strong> Simpan nomor tiket berikut untuk memantau status pengaduan Anda: <strong><?= htmlspecialchars($tiket['no_tiket']) ?></strong>
      </div>
    <?php endif; ?>

    <form action="index.php" method="GET" class="tiket-search reveal">
      <input type="hidden" name="page" value="pengaduan">
      <input type="text" name="no_tiket" placeholder="Masukkan nomor tiket (contoh: PGD-20260630-AB12)" value="<?= htmlspecialchars($no_tiket) ?>" required>
      <button type="submit">Cek →</button>
    </form>

    <?php if ($no_tiket !== '' && !$tiket): ?>
      <div class="tiket-alert-error reveal">
        Nomor tiket <strong><?= htmlspecialchars($no_tiket) ?></strong> tidak ditemukan. Periksa kembali penulisannya.
      </div>
    <?php endif; ?>

    <?php if ($tiket): ?>
      <div class="tiket-card reveal">
        <div class="tiket-card-head">
          <div>
            <div class="tiket-no">#<?= htmlspecialchars($tiket['no_tiket']) ?></div>
            <h3><?= htmlspecialchars($tiket['judul']) ?></h3>
          </div>
          <span class="status-badge status-<?= htmlspecialchars($tiket['status']) ?>">
            <?= $status_label[$tiket['status']] ?? htmlspecialchars($tiket['status']) ?>
          </span>
        </div>

        <div class="tiket-meta-row">
          <span><i class="bi bi-tag"></i> <?= $kategori_label[$tiket['kategori']] ?? htmlspecialchars($tiket['kategori']) ?></span>
          <span><i class="bi bi-calendar3"></i> Diajukan <?= date('d F Y', strtotime($tiket['created_at'])) ?></span>
        </div>

        <p class="tiket-isi"><?= nl2br(htmlspecialchars($tiket['isi'])) ?></p>

        <?php if (!empty($tiket['lampiran']) && file_exists('uploads/pengaduan/' . $tiket['lampiran'])): ?>
          <a href="uploads/pengaduan/<?= htmlspecialchars($tiket['lampiran']) ?>" target="_blank" class="dokumen-btn" style="display:inline-block;margin-bottom:24px">
            <i class="bi bi-paperclip"></i> Lihat Lampiran
          </a>
        <?php endif; ?>

        <div class="tanggapan-title">Tanggapan Petugas</div>
        <?php if (count($tanggapan_list) > 0): ?>
          <?php foreach ($tanggapan_list as $t): ?>
            <div class="tanggapan-item">
              <div class="tanggapan-date"><?= date('d F Y, H:i', strtotime($t['created_at'])) ?> WIB</div>
              <p><?= nl2br(htmlspecialchars($t['isi'])) ?></p>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="tanggapan-kosong">Belum ada tanggapan dari petugas. Mohon tunggu, pengaduan Anda sedang kami proses.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>
</section>
