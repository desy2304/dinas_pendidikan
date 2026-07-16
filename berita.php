<?php

include 'koneksi.php';

function resolve_news_image($filename) {
  $candidates = [];

  if (!empty($filename)) {
    $candidates[] = __DIR__ . '/uploads/berita/' . $filename;
    $candidates[] = __DIR__ . '/img/berita/' . $filename;
    $candidates[] = __DIR__ . '/img/' . $filename;
    $candidates[] = __DIR__ . '/' . $filename;
  }

  foreach ($candidates as $path) {
    if (!empty($path) && file_exists($path)) {
      return $path;
    }
  }

  return null;
}

// ===== BERITA (paginasi) =====
$per_page = 9;
$halaman = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$offset  = ($halaman - 1) * $per_page;

$total       = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM berita WHERE status = 'terbit'"));
$total_pages = max(1, (int) ceil($total / $per_page));

$q_berita = "SELECT id, judul, isi, gambar, tanggal_publish
             FROM berita
             WHERE status = 'terbit'
             ORDER BY tanggal_publish DESC
             LIMIT $per_page OFFSET $offset";
$r_berita = mysqli_query($conn, $q_berita);

// ===== PENGUMUMAN (semua, terbaru dulu) =====
$q_pengumuman = "SELECT id, judul, tanggal FROM pengumuman WHERE status = 'terbit' ORDER BY tanggal DESC";
$r_pengumuman = mysqli_query($conn, $q_pengumuman);
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Informasi Terkini</div>
    <div class="section-title">Berita &amp; Pengumuman</div>
    <p class="section-sub">Kumpulan berita dan pengumuman resmi Dinas Pendidikan Kabupaten Sumenep.</p>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">

    <div class="section-head" style="margin-bottom:24px">
      <div>
        <div class="section-label">Berita</div>
      </div>
    </div>

    <div class="kegiatan-grid">
      <?php if ($r_berita && mysqli_num_rows($r_berita) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($r_berita)):
          $resolved_image_path = resolve_news_image($row['gambar']);
          $gambar_url = $resolved_image_path ? str_replace(__DIR__ . '/', '', $resolved_image_path) : null;
          $ada = !empty($resolved_image_path) && file_exists($resolved_image_path);
        ?>
        <div class="kegiatan-card reveal">
          <div class="kegiatan-thumb">
            <?php if ($ada): ?>
              <img src="<?= htmlspecialchars($gambar_url) ?>" alt="<?= htmlspecialchars($row['judul']) ?>">
            <?php else: ?>
              <div class="kegiatan-thumb-fallback"><i class="bi bi-newspaper"></i></div>
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
        <div class="karyawan-empty" style="grid-column:1/-1">Belum ada berita yang dipublikasikan.</div>
      <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <a class="page-btn <?= $halaman <= 1 ? 'disabled' : '' ?>" href="?page=berita&p=<?= $halaman - 1 ?>">‹ Prev</a>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="page-btn <?= $i == $halaman ? 'active' : '' ?>" href="?page=berita&p=<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a class="page-btn <?= $halaman >= $total_pages ? 'disabled' : '' ?>" href="?page=berita&p=<?= $halaman + 1 ?>">Next ›</a>
    </div>
    <?php endif; ?>

    <div class="section-head" style="margin:56px 0 24px">
      <div>
        <div class="section-label">Pengumuman</div>
      </div>
    </div>

    <div class="news-sidebar">
      <?php if ($r_pengumuman && mysqli_num_rows($r_pengumuman) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($r_pengumuman)): ?>
        <div class="news-list-card reveal">
          <div class="news-badge sidebar-badge" style="background:#166534;">Info</div>
          <div class="news-list-item">
            <h4><?= htmlspecialchars($row['judul']) ?></h4>
            <span><?= date('d F Y', strtotime($row['tanggal'])) ?></span>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="news-list-card reveal">
          <div class="news-list-item">
            <h4>Belum ada pengumuman.</h4>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</section>
