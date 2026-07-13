<?php
include 'data_layanan.php';
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" style="display:inline-flex;align-items:center;gap:6px;color:var(--navy);font-weight:600;font-size:14px;text-decoration:none;margin-bottom:20px">&larr; Kembali ke Beranda</a>
    <div class="section-label">Untuk Masyarakat</div>
    <div class="section-title">Layanan Publik</div>
    <p class="section-sub">Standar pelayanan Dinas Pendidikan Kabupaten Sumenep. Klik salah satu layanan untuk melihat persyaratan, jangka waktu, biaya, dan prosedur lengkapnya.</p>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="info-grid">
      <?php foreach ($daftar_layanan as $slug => $layanan): ?>
      <a href="?page=detail_layanan&slug=<?= urlencode($slug) ?>" class="info-card reveal" style="text-decoration:none;color:inherit;display:block;cursor:pointer">
        <div class="info-card-icon"><i class="bi <?= htmlspecialchars($layanan['icon']) ?>"></i></div>
        <h4><?= htmlspecialchars($layanan['judul']) ?></h4>
        <p><?= htmlspecialchars($layanan['ringkasan']) ?></p>
        <span style="font-size:13px;font-weight:700;color:var(--gold);display:inline-flex;align-items:center;gap:6px">Lihat detail <i class="bi bi-arrow-right"></i></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>