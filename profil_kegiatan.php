<?php

include 'koneksi.php';

$per_page = 6;
$halaman = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$offset  = ($halaman - 1) * $per_page;

$q_kat = "SELECT id FROM kategori_berita WHERE slug = 'kegiatan' LIMIT 1";
$r_kat = mysqli_query($conn, $q_kat);
$kat_kegiatan = $r_kat ? mysqli_fetch_assoc($r_kat) : null;

$r_kegiatan = false;
$total_pages = 1;
if ($kat_kegiatan) {
    $kat_id = (int) $kat_kegiatan['id'];

    $total = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM berita WHERE status='terbit' AND kategori_id = $kat_id"));
    $total_pages = max(1, (int) ceil($total / $per_page));

    $q_kegiatan = "SELECT id, judul, isi, gambar, tanggal_publish
                   FROM berita
                   WHERE status = 'terbit' AND kategori_id = $kat_id
                   ORDER BY tanggal_publish DESC
                   LIMIT $per_page OFFSET $offset";
    $r_kegiatan = mysqli_query($conn, $q_kegiatan);
}
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Dokumentasi</div>
    <div class="section-title">Kegiatan</div>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="kegiatan-grid">
      <?php if ($r_kegiatan && mysqli_num_rows($r_kegiatan) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($r_kegiatan)): ?>
        <div class="kegiatan-card reveal">
          <div class="kegiatan-thumb">
            <?php if (!empty($row['gambar']) && file_exists('uploads/berita/' . $row['gambar'])): ?>
              <img src="uploads/berita/<?= htmlspecialchars($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>">
            <?php else: ?>
              <div class="kegiatan-thumb-fallback"><i class="bi bi-calendar-event"></i></div>
            <?php endif; ?>
          </div>
          <div class="kegiatan-body">
            <div class="kegiatan-date"><i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($row['tanggal_publish'])) ?></div>
            <h4><?= htmlspecialchars($row['judul']) ?></h4>
            <p><?= substr(strip_tags($row['isi']), 0, 100) ?>...</p>
            <a href="?page=detail_berita&id=<?= $row['id'] ?>" class="read-more">Baca selengkapnya</a>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="karyawan-empty">Belum ada kegiatan yang dipublikasikan.</div>
      <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <a class="page-btn <?= $halaman <= 1 ? 'disabled' : '' ?>" href="?page=profil_kegiatan&p=<?= $halaman - 1 ?>">‹ Prev</a>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="page-btn <?= $i == $halaman ? 'active' : '' ?>" href="?page=profil_kegiatan&p=<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a class="page-btn <?= $halaman >= $total_pages ? 'disabled' : '' ?>" href="?page=profil_kegiatan&p=<?= $halaman + 1 ?>">Next ›</a>
    </div>
    <?php endif; ?>
  </div>
</section>