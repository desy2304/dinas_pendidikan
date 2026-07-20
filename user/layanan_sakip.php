<?php

include 'koneksi.php';

// Label tampilan untuk tiap kategori (sesuai enum di tabel sakip)
$kategori_label = [
    'renstra_pk' => 'Renstra & Perjanjian Kinerja',
    'lkjip'      => 'LKjIP',
    'iku'        => 'IKU',
];

// Filter kategori (opsional, dari tombol filter)
$filter = isset($_GET['kategori']) && array_key_exists($_GET['kategori'], $kategori_label) ? $_GET['kategori'] : '';

$q = "SELECT * FROM sakip ORDER BY tahun DESC, judul ASC";
$r = mysqli_query($conn, $q);

// Kelompokkan hasil query per kategori
$dokumen_per_kategori = [
    'renstra_pk' => [],
    'lkjip'      => [],
    'iku'        => [],
];
if ($r) {
    while ($row = mysqli_fetch_assoc($r)) {
        $dokumen_per_kategori[$row['kategori']][] = $row;
    }
}

// Kategori mana saja yang ditampilkan (semua, atau cuma yang difilter)
$kategori_tampil = $filter !== '' ? [$filter => $kategori_label[$filter]] : $kategori_label;
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

    <div class="bidang-filter">
      <a href="?page=layanan_sakip" class="bidang-filter-btn <?= $filter === '' ? 'active' : '' ?>">Semua Kategori</a>
      <?php foreach ($kategori_label as $kode => $label): ?>
        <a href="?page=layanan_sakip&kategori=<?= urlencode($kode) ?>" class="bidang-filter-btn <?= $filter === $kode ? 'active' : '' ?>">
          <?= htmlspecialchars($label) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php foreach ($kategori_tampil as $kode => $label): ?>
      <div style="margin-bottom:40px">
        <div class="section-label" style="margin-bottom:16px"><?= htmlspecialchars($label) ?></div>

        <div class="dokumen-list">
          <?php if (count($dokumen_per_kategori[$kode]) > 0): ?>
            <?php foreach ($dokumen_per_kategori[$kode] as $d):
              $path = 'uploads/sakip/' . $d['file'];
              $ada = !empty($d['file']) && file_exists($path);
            ?>
            <div class="dokumen-item reveal">
              <div class="dokumen-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
              <div class="dokumen-info">
                <h5><?= htmlspecialchars($d['judul']) ?></h5>
                <span>
                  Tahun <?= htmlspecialchars($d['tahun']) ?>
                  <?php if (!empty($d['keterangan'])): ?> · <?= htmlspecialchars($d['keterangan']) ?><?php endif; ?>
                </span>
              </div>
              <?php if ($ada): ?>
                <div class="dokumen-actions">
                  <a href="<?= htmlspecialchars($path) ?>" target="_blank" class="dokumen-btn dokumen-btn-outline">Lihat</a>
                  <a href="<?= htmlspecialchars($path) ?>" class="dokumen-btn" download>Unduh</a>
                </div>
              <?php else: ?>
                <span class="dokumen-btn" style="background:var(--muted);cursor:not-allowed">Belum Tersedia</span>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="karyawan-empty">Belum ada dokumen pada kategori ini.</div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>

    <p style="font-size:13px;color:var(--muted)">
      *Dokumen PDF diunggah ke folder <code>uploads/dokumen/</code> dengan nama file yang sesuai kolom <code>file</code> di tabel <code>sakip</code>, tombol unduh otomatis aktif setelah filenya tersedia.
    </p>
  </div>
</section>