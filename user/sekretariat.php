<?php

include 'koneksi.php';
$nama_bidang = 'Sekretariat';
$q_kegiatan_bidang = "SELECT k.* FROM kegiatan k
                       INNER JOIN bidang b ON k.bidang_id = b.id
                       WHERE b.nama = '$nama_bidang' AND k.status = 'terbit'
                       ORDER BY k.tanggal_mulai DESC";
$r_kegiatan_bidang = mysqli_query($conn, $q_kegiatan_bidang);
?>
<section class="section">
  <div class="section-inner">
    <div class="section-head">
      <div>
        <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
        <div class="section-label">Bidang</div>
        <div class="section-title">Sekretariat</div>
      </div>
    </div>
    <div class="profile-summary reveal">
      <div class="profile-summary-text">
        <p>Sekretariat bertugas menyelenggarakan koordinasi pelaksanaan tugas, pembinaan, dan pemberian dukungan administrasi kepada seluruh bidang di lingkungan Dinas Pendidikan Kabupaten Sumenep, meliputi urusan umum, kepegawaian, keuangan, serta perencanaan program dan pelaporan.</p>
      </div>
      <div class="profile-card-block">
        <h3>Program Utama</h3>
        <ul>
          <li>Perencanaan program dan anggaran dinas</li>
          <li>Administrasi umum dan kepegawaian</li>
          <li>Pengelolaan keuangan dan aset</li>
          <li>Evaluasi dan pelaporan kinerja</li>
        </ul>
      </div>
    </div>

    <div class="reveal" style="margin-top:40px">
      <h3 class="kegiatan-bidang-title">Kegiatan Pada Bidang <?= htmlspecialchars($nama_bidang) ?></h3>
      <div class="kegiatan-bidang-table-wrap">
        <table class="kegiatan-bidang-table">
          <thead>
            <tr>
              <th>Nama Kegiatan</th>
              <th>Uraian</th>
              <th>Tgl Pelaksanaan</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($r_kegiatan_bidang && mysqli_num_rows($r_kegiatan_bidang) > 0): ?>
              <?php while ($row = mysqli_fetch_assoc($r_kegiatan_bidang)): ?>
              <tr>
                <td><?= htmlspecialchars($row['judul']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['deskripsi'])) ?></td>
                <td>
                  <?= date('d-m-Y', strtotime($row['tanggal_mulai'])) ?>
                  <?php if (!empty($row['tanggal_selesai']) && $row['tanggal_selesai'] !== $row['tanggal_mulai']): ?>
                    s/d <?= date('d-m-Y', strtotime($row['tanggal_selesai'])) ?>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="3" style="text-align:center;color:var(--muted)">Belum ada kegiatan yang dipublikasikan.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>