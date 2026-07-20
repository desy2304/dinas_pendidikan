<?php

include 'koneksi.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$pengumuman = null;
if ($id > 0) {
    $q = "SELECT * FROM pengumuman WHERE id = $id AND status = 'terbit' LIMIT 1";
    $r = mysqli_query($conn, $q);
    $pengumuman = $r ? mysqli_fetch_assoc($r) : null;
}
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner" style="max-width:820px">
    <a href="?page=berita" class="btn-back">&larr; Kembali ke Berita &amp; Pengumuman</a>

    <?php if (!$pengumuman): ?>
      <div class="section-label">Pengumuman</div>
      <div class="section-title">Pengumuman Tidak Ditemukan</div>
      <p class="section-sub">Pengumuman yang kamu cari tidak tersedia atau belum dipublikasikan.</p>
    <?php else: ?>
      <div class="section-label">Pengumuman · <?= date('d F Y', strtotime($pengumuman['tanggal'])) ?></div>
      <div class="section-title" style="font-size:32px"><?= htmlspecialchars($pengumuman['judul']) ?></div>
    <?php endif; ?>
  </div>
</section>

<?php if ($pengumuman): ?>
<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner" style="max-width:820px">

    <?php
      $gambar_path = 'uploads/pengumuman/' . $pengumuman['gambar'];
      $ada = !empty($pengumuman['gambar']) && file_exists($gambar_path);
    ?>
    <?php if ($ada): ?>
      <div class="reveal" style="border-radius:18px;overflow:hidden;margin-bottom:32px;box-shadow:0 10px 30px rgba(11,31,58,.08)">
        <img src="<?= htmlspecialchars($gambar_path) ?>" alt="<?= htmlspecialchars($pengumuman['judul']) ?>" style="width:100%;height:auto;display:block">
      </div>
    <?php endif; ?>

    <div class="tiket-card reveal article-body">
      <?= $pengumuman['isi'] ?>
    </div>

  </div>
</section>
<?php endif; ?>
