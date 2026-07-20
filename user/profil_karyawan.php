<?php

include 'koneksi.php';

$per_page = 9;
$halaman = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
$offset  = ($halaman - 1) * $per_page;

// Filter bidang (opsional, dari tombol filter)
$bidang_id = isset($_GET['bidang']) ? (int) $_GET['bidang'] : 0;
$where_bidang = $bidang_id > 0 ? "AND p.bidang_id = $bidang_id" : "";

// Daftar bidang untuk tombol filter
$r_bidang_filter = mysqli_query($conn, "SELECT id, nama FROM bidang ORDER BY nama ASC");

$total       = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM pegawai p WHERE p.status = 'aktif' $where_bidang"));
$total_pages = max(1, (int) ceil($total / $per_page));

$q_pegawai = "SELECT p.*, b.nama AS bidang_nama
              FROM pegawai p
              LEFT JOIN bidang b ON p.bidang_id = b.id
              WHERE p.status = 'aktif' $where_bidang
              ORDER BY p.id ASC
              LIMIT $per_page OFFSET $offset";
$r_pegawai = mysqli_query($conn, $q_pegawai);

function inisial_nama(string $nama) {
    $kata = preg_split('/\s+/', trim($nama));
    $inisial = strtoupper(substr($kata[0], 0, 1));
    if (count($kata) > 1) {
        $inisial .= strtoupper(substr(end($kata), 0, 1));
    }
    return $inisial;
}
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Sumber Daya Manusia</div>
    <div class="section-title">Data Karyawan</div>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="bidang-filter">
      <a href="?page=profil_karyawan" class="bidang-filter-btn <?= $bidang_id === 0 ? 'active' : '' ?>">Semua Bidang</a>
      <?php while ($b = mysqli_fetch_assoc($r_bidang_filter)): ?>
        <a href="?page=profil_karyawan&bidang=<?= $b['id'] ?>" class="bidang-filter-btn <?= $bidang_id === (int)$b['id'] ? 'active' : '' ?>">
          <?= htmlspecialchars($b['nama']) ?>
        </a>
      <?php endwhile; ?>
    </div>

    <div class="karyawan-grid">
      <?php if ($r_pegawai && mysqli_num_rows($r_pegawai) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($r_pegawai)): ?>
        <div class="karyawan-card reveal">
          <div class="karyawan-avatar">
            <?php if (!empty($row['foto']) && file_exists('img/pegawai/' . $row['foto'])): ?>
              <img src="img/pegawai/<?= htmlspecialchars($row['foto']) ?>" alt="<?= htmlspecialchars($row['nama']) ?>">
            <?php else: ?>
              <div class="karyawan-avatar-fallback"><?= inisial_nama($row['nama']) ?></div>
            <?php endif; ?>
          </div>
          <div class="karyawan-body">
            <div class="karyawan-name"><?= htmlspecialchars($row['nama']) ?></div>
            <div class="karyawan-role"><?= htmlspecialchars($row['jabatan']) ?></div>
            <?php if (!empty($row['bidang_nama'])): ?>
              <span class="karyawan-badge"><?= htmlspecialchars($row['bidang_nama']) ?></span>
            <?php endif; ?>
            <div class="karyawan-meta">
              <?php if (!empty($row['nip'])): ?><span><strong>NIP</strong> <?= htmlspecialchars($row['nip']) ?></span><?php endif; ?>
              <?php if (!empty($row['email'])): ?><span><strong>Email</strong> <?= htmlspecialchars($row['email']) ?></span><?php endif; ?>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="karyawan-empty">Belum ada data karyawan.</div>
      <?php endif; ?>
    </div>

    <?php if ($total_pages > 1): ?>
    <div class="pagination">
      <a class="page-btn <?= $halaman <= 1 ? 'disabled' : '' ?>" href="?page=profil_karyawan&bidang=<?= $bidang_id ?>&p=<?= $halaman - 1 ?>">‹ Prev</a>
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a class="page-btn <?= $i == $halaman ? 'active' : '' ?>" href="?page=profil_karyawan&bidang=<?= $bidang_id ?>&p=<?= $i ?>"><?= $i ?></a>
      <?php endfor; ?>
      <a class="page-btn <?= $halaman >= $total_pages ? 'disabled' : '' ?>" href="?page=profil_karyawan&bidang=<?= $bidang_id ?>&p=<?= $halaman + 1 ?>">Next ›</a>
    </div>
    <?php endif; ?>
  </div>
</section>