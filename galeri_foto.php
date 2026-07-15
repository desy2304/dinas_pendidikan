<?php

include 'koneksi.php';

$per_page = 8;
$halaman = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$offset  = ($halaman - 1) * $per_page;

$total       = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM galeri WHERE kategori = 'foto'"));
$total_pages = max(1, (int) ceil($total / $per_page));

$q = "SELECT * FROM galeri WHERE kategori = 'foto' ORDER BY tanggal DESC LIMIT $per_page OFFSET $offset";
$r = mysqli_query($conn, $q);
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Dokumentasi</div>
    <div class="section-title">Galeri Foto</div>
    <p class="section-sub">Kumpulan dokumentasi foto kegiatan Dinas Pendidikan Kabupaten Sumenep.</p>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="galeri-full-grid">
      <?php if ($r && mysqli_num_rows($r) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($r)):
          $path = 'uploads/galeri/' . $row['gambar'];
          $ada = !empty($row['gambar']) && file_exists($path);
        ?>
        <div class="galeri-full-item reveal" <?php if ($ada): ?>onclick="openLightbox('<?= htmlspecialchars($path) ?>')"<?php endif; ?>>
          <div class="galeri-full-thumb">
            <?php if ($ada): ?>
              <img src="uploads/galeri/<?= htmlspecialchars($path) ?>" alt="<?= htmlspecialchars($row['judul']) ?>">
            <?php else: ?>
              <div class="galeri-full-fallback"><i class="bi bi-image"></i></div>
            <?php endif; ?>
          </div>
          <div class="galeri-full-body">
            <div class="galeri-full-date"><i class="bi bi-calendar3"></i> <?= date('d F Y', strtotime($row['tanggal'])) ?></div>
            <h5><?= htmlspecialchars($row['judul']) ?></h5>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="karyawan-empty" style="grid-column:1/-1">Belum ada foto pada galeri ini.</div>
      <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <a class="page-btn <?= $halaman <= 1 ? 'disabled' : '' ?>" href="?page=galeri_foto&p=<?= $halaman - 1 ?>">‹ Prev</a>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="page-btn <?= $i == $halaman ? 'active' : '' ?>" href="?page=galeri_foto&p=<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a class="page-btn <?= $halaman >= $total_pages ? 'disabled' : '' ?>" href="?page=galeri_foto&p=<?= $halaman + 1 ?>">Next ›</a>
    </div>
    <?php endif; ?>
  </div>
</section>