<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$berita = null;
if ($id > 0) {
    $q = "SELECT * FROM berita WHERE id = $id AND status = 'terbit' LIMIT 1";
    $r = mysqli_query($conn, $q);
    $berita = $r ? mysqli_fetch_assoc($r) : null;
}
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner" style="max-width:820px">
    <a href="?page=berita" style="display:inline-flex;align-items:center;gap:6px;color:var(--navy);font-weight:600;font-size:14px;text-decoration:none;margin-bottom:20px">&larr; Kembali ke Berita</a>

    <?php if (!$berita): ?>
      <div class="section-label">Berita</div>
      <div class="section-title">Berita Tidak Ditemukan</div>
      <p class="section-sub">Berita yang kamu cari tidak tersedia atau belum dipublikasikan.</p>
    <?php else: ?>
      <div class="section-label">Berita · <?= date('d F Y', strtotime($berita['tanggal_publish'])) ?></div>
      <div class="section-title" style="font-size:32px"><?= htmlspecialchars($berita['judul']) ?></div>
    <?php endif; ?>
  </div>
</section>

<?php if ($berita): ?>
<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner" style="max-width:820px">

    <?php
      $gambar_path = 'uploads/berita/' . $berita['gambar'];
      $ada = !empty($berita['gambar']) && file_exists($gambar_path);
    ?>
    <?php if ($ada): ?>
      <div class="reveal" style="border-radius:18px;overflow:hidden;margin-bottom:32px;box-shadow:0 10px 30px rgba(11,31,58,.08)">
        <img src="<?= htmlspecialchars($gambar_path) ?>" alt="<?= htmlspecialchars($berita['judul']) ?>" style="width:100%;height:auto;display:block">
      </div>
    <?php endif; ?>

    <div class="tiket-card reveal article-body">
      <?= $berita['isi'] ?>
    </div>

  </div>
</section>
<?php endif; ?>
