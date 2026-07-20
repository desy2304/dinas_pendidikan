<?php

include 'koneksi.php';

$q_profil = "SELECT * FROM profil LIMIT 1";
$r_profil = mysqli_query($conn, $q_profil);
$profil = $r_profil ? mysqli_fetch_assoc($r_profil) : null;
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Profil Instansi</div>
    <div class="section-title">Selayang Pandang</div>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="profile-summary">
      <div class="profile-summary-text reveal">
        <p>
          <?php if ($profil && !empty($profil['selayang_pandang'])): ?>
            <?= nl2br(htmlspecialchars($profil['selayang_pandang'])) ?>
          <?php else: ?>
            Informasi selayang pandang belum tersedia.
          <?php endif; ?>
        </p>
      </div>

      <div class="profile-card-block reveal">
        <h3>Kontak &amp; Informasi</h3>
        <p><i class="bi bi-geo-alt"></i>&nbsp; <?= $profil && !empty($profil['alamat']) ? htmlspecialchars($profil['alamat']) : '-' ?></p>
        <p><i class="bi bi-telephone"></i>&nbsp; <?= $profil && !empty($profil['telepon']) ? htmlspecialchars($profil['telepon']) : '-' ?></p>
        <p><i class="bi bi-envelope"></i>&nbsp; <?= $profil && !empty($profil['email']) ? htmlspecialchars($profil['email']) : '-' ?></p>
        <?php if ($profil && (!empty($profil['facebook']) || !empty($profil['instagram']) || !empty($profil['youtube']))): ?>
        <div style="display:flex;gap:12px;margin-top:14px">
          <?php if (!empty($profil['facebook'])): ?>
            <a href="<?= htmlspecialchars($profil['facebook']) ?>" target="_blank" style="color:var(--gold)"><i class="bi bi-facebook" style="font-size:20px"></i></a>
          <?php endif; ?>
          <?php if (!empty($profil['instagram'])): ?>
            <a href="<?= htmlspecialchars($profil['instagram']) ?>" target="_blank" style="color:var(--gold)"><i class="bi bi-instagram" style="font-size:20px"></i></a>
          <?php endif; ?>
          <?php if (!empty($profil['youtube'])): ?>
            <a href="<?= htmlspecialchars($profil['youtube']) ?>" target="_blank" style="color:var(--gold)"><i class="bi bi-youtube" style="font-size:20px"></i></a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
