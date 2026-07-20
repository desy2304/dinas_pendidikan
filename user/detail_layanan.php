<?php
include 'data_layanan.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$layanan = $daftar_layanan[$slug] ?? null;
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=layanan_publik" class="btn-back">&larr; Kembali ke Layanan Publik</a>

    <?php if (!$layanan): ?>
      <div class="section-label">Layanan Publik</div>
      <div class="section-title">Layanan Tidak Ditemukan</div>
      <p class="section-sub">Jenis layanan yang kamu cari tidak tersedia. Silakan kembali ke daftar layanan.</p>
    <?php else: ?>
      <div class="section-label">Standar Pelayanan</div>
      <div class="section-title"><?= htmlspecialchars($layanan['judul']) ?></div>
      <p class="section-sub"><?= htmlspecialchars($layanan['ringkasan']) ?></p>
    <?php endif; ?>
  </div>
</section>

<?php if ($layanan): ?>
<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner" style="max-width:900px">

    <!-- Info ringkas: jangka waktu & biaya -->
    <div class="layanan-info-row reveal">
      <div class="layanan-info-box">
        <i class="bi bi-clock-history"></i>
        <div>
          <span>Jangka Waktu</span>
          <strong><?= htmlspecialchars($layanan['jangka_waktu']) ?></strong>
        </div>
      </div>
      <div class="layanan-info-box">
        <i class="bi bi-cash"></i>
        <div>
          <span>Biaya / Tarif</span>
          <strong><?= htmlspecialchars($layanan['biaya']) ?></strong>
        </div>
      </div>
      <div class="layanan-info-box">
        <i class="bi bi-file-earmark-check"></i>
        <div>
          <span>Produk Layanan</span>
          <strong><?= htmlspecialchars($layanan['produk_layanan']) ?></strong>
        </div>
      </div>
    </div>

    <!-- Persyaratan -->
    <div class="tiket-card reveal" style="margin-top:24px">
      <h3 style="font-size:18px;color:var(--navy);margin-bottom:16px">Persyaratan</h3>

      <?php if (($layanan['format'] ?? '') === 'grouped'): ?>
        <?php foreach ($layanan['grup_persyaratan'] as $nama_grup => $items): ?>
          <div style="margin-bottom:20px">
            <div style="font-size:14px;font-weight:700;color:var(--gold);margin-bottom:10px"><?= htmlspecialchars($nama_grup) ?></div>
            <ul style="padding-left:20px;margin:0">
              <?php foreach ($items as $item): ?>
                <li style="font-size:14px;color:var(--text);line-height:1.9"><?= htmlspecialchars($item) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <ul style="padding-left:20px;margin:0">
          <?php foreach ($layanan['persyaratan'] as $item): ?>
            <li style="font-size:14px;color:var(--text);line-height:1.9"><?= htmlspecialchars($item) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>

    <!-- Prosedur & Mekanisme -->
    <div class="tiket-card reveal" style="margin-top:24px">
      <h3 style="font-size:18px;color:var(--navy);margin-bottom:20px">Prosedur dan Mekanisme</h3>
      <div class="prosedur-flow">
        <?php foreach ($layanan['prosedur'] as $i => $langkah): ?>
          <div class="prosedur-step">
            <div class="prosedur-num"><?= $i + 1 ?></div>
            <div class="prosedur-label"><?= htmlspecialchars($langkah) ?></div>
          </div>
          <?php if ($i < count($layanan['prosedur']) - 1): ?>
            <div class="prosedur-arrow"><i class="bi bi-arrow-right"></i></div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Narahubung -->
    <div class="tiket-card reveal" style="margin-top:24px">
      <h3 style="font-size:18px;color:var(--navy);margin-bottom:16px">Narahubung</h3>
      <ul style="padding-left:20px;margin:0">
        <?php foreach ($layanan['narahubung'] as $orang): ?>
          <li style="font-size:14px;color:var(--text);line-height:1.9"><?= htmlspecialchars($orang) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

  </div>
</section>
<?php endif; ?>