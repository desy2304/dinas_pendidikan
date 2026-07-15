<?php

include 'koneksi.php';

// Ambil data profil (visi, misi)
$query_profil = "SELECT visi, misi FROM profil LIMIT 1";
$result_profil = mysqli_query($conn, $query_profil);
$profil = mysqli_fetch_assoc($result_profil);

// Ambil 1 berita terbaru untuk featured
$query_berita = "SELECT id, judul, isi, tanggal_publish, gambar FROM berita WHERE status='terbit' ORDER BY tanggal_publish DESC LIMIT 1";
$result_berita = mysqli_query($conn, $query_berita);
$berita_utama = mysqli_fetch_assoc($result_berita);

// Ambil 3 pengumuman terbaru
$query_pengumuman = "SELECT judul, tanggal FROM pengumuman WHERE status='terbit' ORDER BY tanggal DESC LIMIT 3";
$result_pengumuman = mysqli_query($conn, $query_pengumuman);

// Ambil 5 galeri terbaru
$query_galeri = "SELECT judul, gambar FROM galeri ORDER BY tanggal DESC LIMIT 5";
$result_galeri = mysqli_query($conn, $query_galeri);

// Ambil 4 pegawai aktif
$query_pegawai = "SELECT nama, jabatan, foto FROM pegawai WHERE status='aktif' ORDER BY id LIMIT 4";
$result_pegawai = mysqli_query($conn, $query_pegawai);
?>

<!-- ===== HERO ===== -->
<section class="hero">
  <div class="hero-bg-slider">
    <div class="hero-bg-slide" style="background-image:url('image/bg1.jpg')"></div>
    <div class="hero-bg-slide" style="background-image:url('image/bg2.png')"></div>
    <div class="hero-bg-slide" style="background-image:url('image/bg3.jpg')"></div>
  </div>
  <div class="hero-pattern"></div>
  <div class="hero-grid"></div>
  <div class="hero-inner">
    <div>
      <div class="hero-badge">Portal Resmi Dinas Pendidikan</div>
      <h2>Selamat Datang Di Dinas Pendidikan Kabupaten Sumenep</h2>
      <p>Mewujudkan layanan pendidikan yang merata, bermutu, dan berkarakter demi generasi Sumenep yang unggul dan berdaya saing.</p>
    </div>
  </div>
</section>

<!-- ===== TICKER ===== -->
<div class="ticker">
  <div class="ticker-inner">
    <span>Sampaikan Pengaduan dan Aspirasi Anda Melalui Layanan Kami</span>
    <span>Penyaluran BOS Triwulan III Selesai</span>
    <span>Pelatihan Guru Penggerak Angkatan XI</span>
    <span>Festival Seni Pelajar Sumenep 2026</span>
    <span>PPDB 2026/2027 Dibuka 1 Juli 2026</span>
    <span>Penyaluran BOS Triwulan III Selesai</span>
    <span>Pelatihan Guru Penggerak Angkatan XI</span>
    <span>Festival Seni Pelajar Sumenep 2026</span>
  </div>
</div>
 

<!-- ===== VISI MISI ===== -->
<section id="visi-misi" class="visi-misi">
    <div class="section-inner">

        <div class="section-head">
            <div>
                <div class="section-label">Profil Instansi</div>
                <div class="section-title">Visi & Misi</div>
            </div>
        </div>

        <div class="visi-grid">

            <!-- VISI -->
            <div class="profile-summary-card reveal">
                <div class="icon">
                    <i class="bi bi-eye"></i>
                </div>

                <h3>Visi Utama</h3>

                <p>
                    <?= nl2br(htmlspecialchars($profil['visi'])) ?>
                </p>
            </div>

            <!-- MISI -->
            <div class="profile-summary-card reveal">
                <div class="icon">
                    <i class="bi bi-bullseye"></i>
                </div>

                <h3>Misi Utama</h3>

                <?php
                    // Pecah teks misi per baris & buang nomor manual (1. / 1) ) jika ada,
                    // lalu render ulang sebagai <ol> supaya penomoran otomatis & bisa diberi gaya.
                    $misi_lines = preg_split('/\r\n|\r|\n/', trim($profil['misi']));
                    $misi_lines = array_filter($misi_lines, fn($l) => trim($l) !== '');
                ?>
                <ol>
                    <?php foreach ($misi_lines as $line): ?>
                        <?php $clean = preg_replace('/^\s*\d+[\.\)]\s*/', '', trim($line)); ?>
                        <li><?= htmlspecialchars($clean) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>

        </div>

    </div>
</section>

<!-- ===== BERITA & PENGUMUMAN ===== -->
<section class="section section-alt">
  <div class="section-inner">
    <div class="section-head">
      <div>
        <div class="section-label">Informasi Terkini</div>
        <div class="section-title">Berita &amp; Pengumuman</div>
      </div>
      <a href="?page=berita" class="link-all">Lihat semua</a>
    </div>
    <div class="news-layout">
      <?php if ($berita_utama): ?>
      <div class="news-featured-card">
        <div class="featured-img reveal">
          <div class="news-badge">Terbaru</div>
          <?php 
            $gambar_path = 'img/berita/' . $berita_utama['gambar'];
            // Cek apakah ada gambar dan file benar-benar ada
            if (!empty($berita_utama['gambar']) && file_exists($gambar_path)): 
          ?>
            <img src="<?= htmlspecialchars($gambar_path) ?>" alt="<?= htmlspecialchars($berita_utama['judul']) ?>" style="position:absolute; inset:0; width:100%; height:100%; object-fit:cover;">
          <?php else: ?>
            <div class="news-img-icon">
              <svg viewBox="0 0 24 24" width="80" height="80"><path d="M19 20H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h10l6 6v8a2 2 0 0 1-2 2z"/></svg>
            </div>
          <?php endif; ?>
          <div class="news-img-overlay"></div>
        </div>
        <div class="featured-body reveal">
          <div class="news-meta">Berita · <?= date('d F Y', strtotime($berita_utama['tanggal_publish'])) ?></div>
          <h3><?= htmlspecialchars($berita_utama['judul']) ?></h3>
          <p><?= substr(strip_tags($berita_utama['isi']), 0, 150) ?>...</p>
          <a href="?page=detail_berita&id=<?= $berita_utama['id'] ?>" class="read-more">Baca selengkapnya</a>
        </div>
      </div>
      <?php endif; ?>

      <div class="news-sidebar">
        <?php if (mysqli_num_rows($result_pengumuman) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result_pengumuman)): ?>
          <div class="news-list-card reveal">
            <div class="news-badge sidebar-badge" style="background:#166534;">Info</div>
            <div class="news-list-item">
              <h4><?= htmlspecialchars($row['judul']) ?></h4>
              <span><?= date('d F Y', strtotime($row['tanggal'])) ?></span>
            </div>
          </div>
          <?php endwhile; ?>
        <?php else: ?>
          <div class="news-list-card reveal">
            <div class="news-list-item">
              <h4>Belum ada pengumuman.</h4>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ===== LAYANAN (STATIS) ===== -->
<section class="section">
  <div class="section-inner">
    <div class="section-head">
      <div>
        <div class="section-label">Pelayanan Publik</div>
        <div class="section-title">Layanan Kami</div>
      </div>
      <a href="?page=layanan_publik" class="link-all">Lihat semua</a>
    </div>
    <div class="layanan-grid">
      <a href="?page=detail_layanan&slug=legalisir-ijazah" class="layanan-card reveal" style="text-decoration:none;color:inherit;display:block">
        <div class="layanan-icon">
          <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m22 21-3-3m0 0a5 5 0 1 0-7-7 5 5 0 0 0 7 7z"/></svg>
        </div>
        <h4>Legalisir Ijazah</h4>
        <p>Pelayanan legalisir ijazah SD, SMP, dan Paket A/B/C bagi alumni sesuai persyaratan yang berlaku.</p>
      </a>
      <a href="?page=layanan_publik" class="layanan-card reveal" style="text-decoration:none;color:inherit;display:block">
        <div class="layanan-icon">
          <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>
        </div>
        <h4>Perbaikan Data Ijazah</h4>
        <p>Pelayanan surat keterangan untuk kesalahan penulisan, kehilangan, atau kerusakan ijazah.</p>
      </a>
      <a href="?page=detail_layanan&slug=ijin-operasional" class="layanan-card reveal" style="text-decoration:none;color:inherit;display:block">
        <div class="layanan-icon">
          <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <h4>Izin Operasional</h4>
        <p>Pengajuan rekomendasi izin operasional pendirian sekolah swasta, PAUD, dan lembaga pendidikan baru.</p>
      </a>
      <a href="?page=detail_layanan&slug=mutasi-siswa" class="layanan-card reveal" style="text-decoration:none;color:inherit;display:block">
        <div class="layanan-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
        </div>
        <h4>Mutasi Siswa</h4>
        <p>Pelayanan rekomendasi mutasi siswa antar kabupaten maupun antar provinsi.</p>
      </a>
      <a href="?page=detail_layanan&slug=pencairan-bos" class="layanan-card reveal" style="text-decoration:none;color:inherit;display:block">
        <div class="layanan-icon">
          <svg viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <h4>Dana BOS</h4>
        <p>Pelayanan rekomendasi pencairan Dana BOS setelah proses verifikasi persyaratan.</p>
      </a>
      <a href="?page=detail_layanan&slug=rekomendasi-pip" class="layanan-card reveal" style="text-decoration:none;color:inherit;display:block">
        <div class="layanan-icon">
          <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h4>Program Indonesia Pintar (PIP)</h4>
        <p>Pelayanan rekomendasi Program Indonesia Pintar bagi peserta didik yang memenuhi syarat.</p>
      </a>
      <a href="?page=detail_layanan&slug=pelayanan-dapodik" class="layanan-card reveal" style="text-decoration:none;color:inherit;display:block">
        <div class="layanan-icon">
          <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </div>
        <h4>Layanan Dapodik</h4>
        <p>Pelayanan akun Dapodik, NPSN, NPYP, serta approval data peserta didik dan mutasi.</p>
      </a>
      <a href="?page=layanan_publik" class="layanan-card" style="text-decoration:none;color:inherit;display:block">
        <div class="layanan-icon">
          <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <h4>Layanan Kepegawaian</h4>
        <p>Melayani pengusulan pensiun, cuti, kenaikan gaji berkala, serta izin atau tugas belajar bagi ASN.</p>
      </a>
    </div>
  </div>
</section>

<!-- ===== GALERI ===== -->
<section class="section section-alt">
  <div class="section-inner">
    <div class="section-head">
      <div>
        <div class="section-label">Dokumentasi Kegiatan</div>
        <div class="section-title">Galeri Foto</div>
      </div>
      <a href="?page=galeri_foto" class="link-all">Lihat semua foto</a>
    </div>
    <div class="galeri-grid">
      <?php if (mysqli_num_rows($result_galeri) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result_galeri)): ?>
        <div class="galeri-item reveal">
          <?php if ($row['gambar'] && file_exists('img/galeri/' . $row['gambar'])): ?>
            <div class="galeri-bg" style="background-image: url('img/galeri/<?= $row['gambar'] ?>'); background-size:cover; background-position:center;"></div>
          <?php else: ?>
            <div class="galeri-bg" style="background: linear-gradient(135deg, #1a3a5c, #2a5f7a);"></div>
          <?php endif; ?>
          <div class="galeri-overlay"><span><?= htmlspecialchars($row['judul']) ?></span></div>
          <div class="galeri-icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align:center; padding:40px; color:#666;">Belum ada foto galeri.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===== PEGAWAI ===== -->
<section class="section">
  <div class="section-inner">
    <div class="section-head">
      <div>
        <div class="section-label">Sumber Daya Manusia</div>
        <div class="section-title">Pimpinan &amp; Staff</div>
      </div>
      <a href="?page=profil_karyawan" class="link-all">Lihat semua pegawai</a>
    </div>
    <div class="pegawai-grid">
      <?php if (mysqli_num_rows($result_pegawai) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result_pegawai)): ?>
        <div class="pegawai-card reveal">
          <div class="pegawai-avatar">
            <div class="pegawai-avatar-circle">
              <?php if ($row['foto'] && file_exists('img/pegawai/' . $row['foto'])): ?>
                <img src="img/pegawai/<?= htmlspecialchars($row['foto']) ?>" alt="Foto" style="width:100%; height:100%; border-radius:50%; object-fit:cover;">
              <?php else: ?>
                <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <?php endif; ?>
            </div>
          </div>
          <div class="pegawai-info">
            <div class="pegawai-name"><?= htmlspecialchars($row['nama']) ?></div>
            <div class="pegawai-jabatan"><?= htmlspecialchars($row['jabatan']) ?></div>
            <div class="pegawai-bidang">Staff</div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="grid-column: 1/-1; text-align:center; padding:40px; color:#666;">Belum ada data pegawai.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ===== PENGADUAN (Form) ===== -->
<section class="section section-alt" id="pengaduan">
  <div class="section-inner">
    <?php if (isset($_GET['pengaduan_error'])): ?>
      <div class="tiket-alert-error reveal" style="margin-bottom:24px">
        Mohon lengkapi Nama, Kategori, Judul, dan Isi Pengaduan sebelum mengirim.
      </div>
    <?php endif; ?>
    <div class="pengaduan-wrap reveal">
      <div class="pengaduan-text">
        <div class="section-label">Layanan Aspirasi</div>
        <div class="section-title">Sampaikan Pengaduan Anda</div>
        <p>Kami berkomitmen memberikan layanan terbaik. Sampaikan keluhan, saran, atau aspirasi Anda dan kami akan merespons secepatnya.</p>
        <div style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:12px;padding:20px;margin-bottom:20px">
          <div style="font-size:12px;font-weight:700;color:var(--gold-light);text-transform:uppercase;letter-spacing:.8px;margin-bottom:10px">Cek Status Pengaduan</div>
          <form action="index.php" method="GET" class="tiket-input">
            <input type="hidden" name="page" value="pengaduan">
            <input type="text" name="no_tiket" placeholder="Masukkan nomor tiket (contoh: PGD-20260630-AB12)" required/>
            <button type="submit">Cek →</button>
          </form>
        </div>
        <div style="display:flex;gap:20px;flex-wrap:wrap">
          <div style="display:flex;align-items:center;gap:8px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gold-light)" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span style="font-size:13px;color:#A8BDD4">Respons dalam 3 hari kerja</span>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--gold-light)" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span style="font-size:13px;color:#A8BDD4">Data Anda terlindungi</span>
          </div>
        </div>
      </div>
      <div class="pengaduan-form">
        <div class="form-title">Form Pengaduan</div>
        <form action="proses_pengaduan.php" method="POST" enctype="multipart/form-data">
          <div class="form-row">
            <div class="form-group">
              <label>Nama Lengkap</label>
              <input type="text" name="nama" placeholder="Nama Anda" required/>
            </div>
            <div class="form-group">
              <label>Nomor Telepon</label>
              <input type="text" name="telepon" placeholder="08xx-xxxx-xxxx"/>
            </div>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="email@contoh.com"/>
          </div>
          <div class="form-group">
            <label>Kategori Pengaduan</label>
            <select name="kategori" required>
              <option value="">Pilih kategori...</option>
              <option value="sarana_prasarana">Sarana & Prasarana</option>
              <option value="kepegawaian">Kepegawaian</option>
              <option value="pelayanan">Pelayanan</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div class="form-group">
            <label>Judul Pengaduan</label>
            <input type="text" name="judul" placeholder="Ringkasan singkat pengaduan" required/>
          </div>
          <div class="form-group">
            <label>Isi Pengaduan</label>
            <textarea name="isi" placeholder="Jelaskan pengaduan Anda secara detail..." required></textarea>
          </div>
          <div class="form-group">
            <label>Lampiran (opsional)</label>
            <input type="file" name="lampiran" accept=".jpg,.jpeg,.png,.pdf"/>
          </div>
          <button type="submit" class="btn-submit">Kirim Pengaduan</button>
        </form>
      </div>
    </div>
  </div>
</section>