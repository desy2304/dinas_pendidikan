<?php
$dokumen_sakip = [
    ['nama' => 'Rencana Strategis (Renstra) 2024–2029', 'jenis' => 'PDF', 'file' => 'renstra.pdf'],
    ['nama' => 'Perjanjian Kinerja Tahunan', 'jenis' => 'PDF', 'file' => 'perjanjian-kinerja.pdf'],
    ['nama' => 'Laporan Kinerja Instansi Pemerintah (LKjIP)', 'jenis' => 'PDF', 'file' => 'lkjip.pdf'],
    ['nama' => 'Indikator Kinerja Utama (IKU)', 'jenis' => 'PDF', 'file' => 'iku.pdf'],
];
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Akuntabilitas Kinerja</div>
    <div class="section-title">Sakip</div>
    <p class="section-sub">Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP) merupakan rangkaian sistematik dari perencanaan, pengukuran, pelaporan, dan evaluasi kinerja instansi pemerintah.</p>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="dokumen-list">
      <?php foreach ($dokumen_sakip as $d):
        $path = 'uploads/dokumen/' . $d['file'];
        $ada = file_exists($path);
      ?>
      <div class="dokumen-item reveal">
        <div class="dokumen-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
        <div class="dokumen-info">
          <h5><?= htmlspecialchars($d['nama']) ?></h5>
          <span><?= htmlspecialchars($d['jenis']) ?></span>
        </div>
        <?php if ($ada): ?>
          <a href="<?= htmlspecialchars($path) ?>" class="dokumen-btn" download>Unduh</a>
        <?php else: ?>
          <span class="dokumen-btn" style="background:var(--muted);cursor:not-allowed">Belum Tersedia</span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <p style="font-size:13px;color:var(--muted);margin-top:24px">
      *Konten dan dokumen pada halaman ini masih berupa draf. Unggah file PDF ke folder <code>uploads/dokumen/</code> sesuai nama file untuk mengaktifkan tombol unduh.
    </p>
  </div>
</section>
