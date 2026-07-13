<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Profil</a>
    <div class="section-label">Bagan Kelembagaan</div>
    <div class="section-title">Struktur Organisasi</div>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="struktur-card reveal">
      <?php
        $struktur_path = 'image/struktur-organisasi.png';
        if (file_exists($struktur_path)):
      ?>
        <img src="<?= htmlspecialchars($struktur_path) ?>" alt="Struktur Organisasi Dinas Pendidikan" style="width:100%;height:auto;display:block;border-radius:18px;">
      <?php else: ?>
        <div class="struktur-empty">
          <i class="bi bi-diagram-3" style="font-size:40px;color:var(--gold);margin-bottom:14px;display:block"></i>
          Bagan struktur organisasi belum diunggah.
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
