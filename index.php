<?php
// Koneksi database
require_once __DIR__ . '/koneksi.php';

// Tentukan halaman yang diminta (default: home)
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Daftar halaman yang diizinkan
$allowed_pages = ['home', 'berita', 'detail_berita', 'profil_selayang', 'profil_struktur', 'profil_karyawan', 'profil_tupoksi', 'profil_peta', 'profil_kegiatan', 'galeri_foto', 'galeri_video', 'galeri_kegiatan', 'galeri_prestasi', 'layanan_info', 'layanan_publik', 'layanan_panduan', 'layanan_sakip', 'layanan_faq', 'detail_layanan', 'pegawai', 'pengaduan', 'sekretariat', 'paud', 'sd', 'smp', 'ketenagaan', 'eboss', 'epegawai', 'ebudaya', 'eskm', 'simantap'];
if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}
$file = $page . '.php';

// Jika file tidak ada, fallback ke home
if (!file_exists($file)) {
    $file = 'home.php';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dinas Pendidikan Kabupaten Sumenep</title>
    <link rel="stylesheet" href="css/base.css?v=<?= filemtime('css/base.css') ?>" />
    <link rel="stylesheet" href="css/header.css?v=<?= filemtime('css/header.css') ?>" />
    <link rel="stylesheet" href="css/hero.css?v=<?= filemtime('css/hero.css') ?>" />
    <link rel="stylesheet" href="css/home.css?v=<?= filemtime('css/home.css') ?>" />
    <link rel="stylesheet" href="css/profil.css?v=<?= filemtime('css/profil.css') ?>" />
    <link rel="stylesheet" href="css/layanan.css?v=<?= filemtime('css/layanan.css') ?>" />
    <link rel="stylesheet" href="css/galeri.css?v=<?= filemtime('css/galeri.css') ?>" />
    <link rel="stylesheet" href="css/pengaduan.css?v=<?= filemtime('css/pengaduan.css') ?>" />
    <link rel="stylesheet" href="css/berita.css?v=<?= filemtime('css/berita.css') ?>" />
    <link rel="stylesheet" href="css/footer.css?v=<?= filemtime('css/footer.css') ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body id="top">

<!-- ========== HEADER ========== -->
<header class="header">
  <div class="header-inner">
    <a href="?page=home" class="brand">
      <img src="image/Logo1.png" alt="Logo Dinas Pendidikan Sumenep" class="logo-img"/>
      <div class="brand-text">
        <h1>DINAS PENDIDIKAN</h1>
        <p>Kabupaten Sumenep</p>
      </div>
    </a>
    <nav>
      <a href="?page=home" <?= ($page == 'home') ? 'class="active"' : '' ?>>Beranda</a>
      <div class="nav-item dropdown">
        <a href="#" class="drop-toggle">Profil</a>
        <div class="dropdown-menu">
          <a href="?page=profil_selayang">Selayang Pandang</a>
          <a href="?page=profil_struktur">Struktur Organisasi</a>
          <a href="?page=profil_karyawan">Data Karyawan</a>
          <a href="?page=profil_tupoksi">Tugas Pokok Dan Fungsi</a>
          <a href="?page=profil_peta">Peta</a>
          <a href="?page=profil_kegiatan">Kegiatan</a>
        </div>
      </div>
      <div class="nav-item dropdown">
        <a href="#" class="drop-toggle">Bidang - Bidang</a>
        <div class="dropdown-menu">
          <a href="?page=sekretariat">Sekretariat</a>
          <a href="?page=paud">PAUD dan PNF</a>
          <a href="?page=sd">SD (Sekolah Dasar)</a>
          <a href="?page=smp">SMP (Sekolah Menengah Pertama)</a>
          <a href="?page=ketenagaan">Ketenagaan</a>
        </div>
      </div>
      <div class="nav-item dropdown">
        <a href="#" class="drop-toggle">Galeri</a>
        <div class="dropdown-menu">
          <a href="?page=galeri_foto">Foto</a>
          <a href="?page=galeri_prestasi">Prestasi</a>
        </div>
      </div>
      <div class="nav-item dropdown">
        <a href="#" class="drop-toggle">Informasi &amp; Layanan</a>
        <div class="dropdown-menu">
          <a href="?page=layanan_publik">Layanan Publik</a>
          <a href="?page=layanan_sakip">Sakip</a>
          <a href="?page=layanan_faq">FAQ</a>
        </div>
      </div>
    </nav>
  </div>
</header>

<!-- ========== KONTEN DINAMIS ========== -->
<?php include $file; ?>

<!-- ========== FOOTER ========== -->
<footer id="footer" class="footer dark-background">
  <div class="container">
    <h3>Dinas Pendidikan Kabupaten Sumenep</h3>
    <p>Jl. DR. Cipto No.35, Desa Kolor, Kecamatan Kota Sumenep, Jawa Timur 69417, Indonesia.</p>
    <div class="social-link">
      <a href="#" target="_blank" class="social-icon"><i class="bi bi-tiktok"></i></a>
      <a href="#" target="_blank" class="social-icon"><i class="bi bi-facebook"></i></a>
      <a href="#" target="_blank" class="social-icon"><i class="bi bi-instagram"></i></a>
      <a href="#" target="_blank" class="social-icon"><i class="bi bi-whatsapp"></i></a>
    </div>
    <div class="footer-divider"></div>
    <div class="copyright">
      <span>Copyright</span>
      <strong class="sitename">Disdik Kabupaten Sumenep</strong>
    </div>
  </div>
</footer>

<a href="#" class="scroll-top" aria-label="Kembali ke atas">↑</a>

<!-- ===== LIGHTBOX GALERI (global, dipakai halaman galeri foto/kegiatan/prestasi) ===== -->
<div id="lightbox" class="lightbox-overlay" onclick="closeLightbox()">
  <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
  <img id="lightbox-img" src="" alt="">
</div>
<script>
function openLightbox(src){
  document.getElementById('lightbox-img').src = src;
  document.getElementById('lightbox').classList.add('active');
}
function closeLightbox(){
  document.getElementById('lightbox').classList.remove('active');
}
document.addEventListener('keydown', function(e){
  if (e.key === 'Escape') closeLightbox();
});
</script>

<!-- Script untuk efek reveal -->
<script>
document.addEventListener('DOMContentLoaded', function(){
  if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('.reveal').forEach(function(el){ el.classList.add('in-view'); });
    return;
  }

  var reveals = Array.from(document.querySelectorAll('.reveal'));
  var io = new IntersectionObserver(function(entries, observer){
    entries.forEach(function(entry){
      if (entry.isIntersecting) {
        var target = entry.target;
        var startIndex = reveals.indexOf(target);
        var maxAhead = 4;
        var delayStep = 80;
        for (var k = 0; k < maxAhead; k++){
          var el = reveals[startIndex + k];
          if (!el) break;
          var rect = el.getBoundingClientRect();
          if (rect.top <= window.innerHeight * 1.5){
            (function(e, d){
              setTimeout(function(){
                e.classList.add('in-view');
                observer.unobserve(e);
              }, d);
            })(el, k * delayStep);
          }
        }
      }
    });
  }, { threshold: 0.12 });

  reveals.forEach(function(el){ io.observe(el); });
});
</script>

</body>
</html>