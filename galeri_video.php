<?php
$per_page = 8;
$halaman = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$offset  = ($halaman - 1) * $per_page;

$total       = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM galeri WHERE kategori = 'video'"));
$total_pages = max(1, (int) ceil($total / $per_page));

$q = "SELECT * FROM galeri WHERE kategori = 'video' ORDER BY tanggal DESC LIMIT $per_page OFFSET $offset";
$r = mysqli_query($conn, $q);

// Ubah link YouTube biasa (watch?v= / youtu.be/) menjadi link embed
function youtube_embed_url(string $url) {
    if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]{11})/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return $url; // fallback: anggap sudah berupa link embed
}
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" style="display:inline-flex;align-items:center;gap:6px;color:var(--navy);font-weight:600;font-size:14px;text-decoration:none;margin-bottom:20px">&larr; Kembali ke Beranda</a>
    <div class="section-label">Dokumentasi</div>
    <div class="section-title">Galeri Video</div>
    <p class="section-sub">Kumpulan video dokumentasi dan profil Dinas Pendidikan Kabupaten Sumenep.</p>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="video-grid">
      <?php if ($r && mysqli_num_rows($r) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($r)): ?>
        <div class="video-card reveal">
          <div class="video-frame">
            <?php if (!empty($row['video'])): ?>
              <iframe src="<?= htmlspecialchars(youtube_embed_url($row['video'])) ?>" title="<?= htmlspecialchars($row['judul']) ?>" allowfullscreen loading="lazy"></iframe>
            <?php else: ?>
              <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:rgba(255,255,255,.4);font-size:32px"><i class="bi bi-camera-video"></i></div>
            <?php endif; ?>
          </div>
          <div class="video-body">
            <div class="galeri-full-date"><i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($row['tanggal'])) ?></div>
            <h4><?= htmlspecialchars($row['judul']) ?></h4>
            <?php if (!empty($row['keterangan'])): ?>
              <p><?= htmlspecialchars($row['keterangan']) ?></p>
            <?php endif; ?>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="karyawan-empty" style="grid-column:1/-1">Belum ada video pada galeri ini.</div>
      <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <a class="page-btn <?= $halaman <= 1 ? 'disabled' : '' ?>" href="?page=galeri_video&p=<?= $halaman - 1 ?>">‹ Prev</a>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="page-btn <?= $i == $halaman ? 'active' : '' ?>" href="?page=galeri_video&p=<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a class="page-btn <?= $halaman >= $total_pages ? 'disabled' : '' ?>" href="?page=galeri_video&p=<?= $halaman + 1 ?>">Next ›</a>
    </div>
    <?php endif; ?>
  </div>
</section>