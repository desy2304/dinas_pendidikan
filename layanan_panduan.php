<?php
// Daftar dokumen panduan & formulir (statis untuk sekarang)
// Nanti bisa diganti jadi dinamis dari database kalau sudah ada tabelnya
$dokumen = [
    ['nama' => 'Formulir Pendaftaran PPDB', 'jenis' => 'PDF', 'file' => 'formulir-ppdb.pdf'],
    ['nama' => 'Panduan Pengajuan Izin Operasional Sekolah', 'jenis' => 'PDF', 'file' => 'panduan-izin-operasional.pdf'],
    ['nama' => 'Formulir Pengaduan Masyarakat', 'jenis' => 'PDF', 'file' => 'formulir-pengaduan.pdf'],
    ['nama' => 'Panduan Verifikasi NUPTK', 'jenis' => 'PDF', 'file' => 'panduan-nuptk.pdf'],
    ['nama' => 'Formulir Legalisir Ijazah', 'jenis' => 'PDF', 'file' => 'formulir-legalisir.pdf'],
];
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Unduhan</div>
    <div class="section-title">Panduan &amp; Formulir</div>
    <p class="section-sub">Unduh panduan dan formulir resmi yang dibutuhkan untuk mengakses layanan Dinas Pendidikan Kabupaten Sumenep.</p>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="dokumen-list">
      <?php foreach ($dokumen as $d):
        $path = 'uploads/dokumen/' . $d['file'];
        $ada = file_exists($path);
      ?>
      <div class="dokumen-item reveal">
        <div class="dokumen-icon"><i class="bi bi-file-earmark-pdf"></i></div>
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
      *Daftar dokumen masih berupa draf. Unggah file PDF-nya ke folder <code>uploads/dokumen/</code> dengan nama file yang sesuai supaya tombol "Unduh" aktif otomatis.
    </p>
  </div>
</section>
