<?php

include 'koneksi.php';

$q_profil = "SELECT * FROM profil LIMIT 1";
$r_profil = mysqli_query($conn, $q_profil);
$profil = $r_profil ? mysqli_fetch_assoc($r_profil) : null;
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Profil</a>
    <div class="section-label">Lokasi</div>
    <div class="section-title">Peta Sekolah</div>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="peta-frame reveal">
      <?php if ($profil && !empty($profil['alamat'])): ?>
        <iframe
          src="https://maps.google.com/maps?q=<?= urlencode($profil['alamat']) ?>&output=embed"
          width="100%" height="420" style="border:0;border-radius:18px" loading="lazy"></iframe>
      <?php else: ?>
        <div class="struktur-empty">
          <i class="bi bi-geo-alt" style="font-size:40px;color:var(--gold);margin-bottom:14px;display:block"></i>
          Alamat belum tersedia untuk ditampilkan di peta.
        </div>
      <?php endif; ?>
    </div>
    <p style="font-size:13px;color:var(--muted);margin-top:14px">
      *Menampilkan lokasi Kantor Dinas Pendidikan Kabupaten Sumenep. Direktori peta satuan pendidikan menyusul.
    </p>
  </div>
</section>
