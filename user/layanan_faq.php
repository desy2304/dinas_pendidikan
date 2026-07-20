<?php
$faq = [
    [
        'q' => 'Bagaimana cara mendaftar PPDB (Penerimaan Peserta Didik Baru)?',
        'a' => 'Pendaftaran PPDB dapat dilakukan secara daring melalui portal PPDB resmi pada periode yang telah ditentukan. Informasi jadwal dan syarat dapat dilihat pada halaman Layanan Publik.',
    ],
    [
        'q' => 'Berapa lama proses legalisir ijazah?',
        'a' => 'Proses legalisir ijazah umumnya diselesaikan dalam 1–3 hari kerja, tergantung jumlah dokumen yang diajukan.',
    ],
    [
        'q' => 'Bagaimana cara menyampaikan pengaduan ke Dinas Pendidikan?',
        'a' => 'Pengaduan dapat disampaikan melalui menu Pengaduan pada situs ini dengan mengisi formulir yang tersedia. Anda akan mendapatkan nomor tiket untuk memantau status pengaduan.',
    ],
    [
        'q' => 'Apakah data yang saya kirimkan melalui formulir pengaduan bersifat rahasia?',
        'a' => 'Ya, data pelapor hanya digunakan untuk keperluan verifikasi dan tindak lanjut, dan tidak dipublikasikan kepada pihak yang tidak berkepentingan.',
    ],
    [
        'q' => 'Bagaimana cara mengetahui status pengajuan izin operasional sekolah?',
        'a' => 'Status pengajuan dapat ditanyakan langsung ke Bidang terkait di kantor Dinas Pendidikan, atau melalui kontak yang tercantum pada halaman Profil.',
    ],
];
?>

<section class="section" style="padding-bottom:10px">
  <div class="section-inner">
    <a href="?page=home" class="btn-back">&larr; Kembali ke Beranda</a>
    <div class="section-label">Bantuan</div>
    <div class="section-title">FAQ</div>
    <p class="section-sub">Pertanyaan yang sering diajukan seputar layanan Dinas Pendidikan Kabupaten Sumenep.</p>
  </div>
</section>

<section class="section section-alt" style="padding-top:20px">
  <div class="section-inner">
    <div class="faq-list">
      <?php foreach ($faq as $f): ?>
      <details class="faq-item reveal">
        <summary><?= htmlspecialchars($f['q']) ?></summary>
        <p><?= htmlspecialchars($f['a']) ?></p>
      </details>
      <?php endforeach; ?>
    </div>

    <p style="font-size:13px;color:var(--muted);margin-top:24px">
      *Daftar pertanyaan masih berupa draf. Silakan tambah/ubah isinya langsung di array <code>$faq</code> pada file ini.
    </p>
  </div>
</section>
