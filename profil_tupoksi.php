<?php

include 'koneksi.php';

$q_bidang = "SELECT * FROM bidang ORDER BY id ASC";
$r_bidang = mysqli_query($conn, $q_bidang);
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Landasan Kerja</div>
    <div class="section-title">Tugas Pokok dan Fungsi</div>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="tupoksi-grid">
      <?php if ($r_bidang && mysqli_num_rows($r_bidang) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($r_bidang)): ?>
        <div class="profile-card-block reveal">
          <h3><?= htmlspecialchars($row['nama']) ?></h3>
          <p><strong>Tugas:</strong> <?= !empty($row['tugas']) ? nl2br(htmlspecialchars($row['tugas'])) : '-' ?></p>
          <p><strong>Fungsi:</strong> <?= !empty($row['fungsi']) ? nl2br(htmlspecialchars($row['fungsi'])) : '-' ?></p>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="karyawan-empty">Belum ada data bidang.</div>
      <?php endif; ?>
    </div>
  </div>
</section>
