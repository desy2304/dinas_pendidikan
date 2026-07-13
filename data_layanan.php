<?php
/**
 * Data Standar Pelayanan Dinas Pendidikan Kabupaten Sumenep
 * Sumber: dokumen "Standar Pelayanan" resmi.
 * File ini di-include oleh layanan_publik.php (daftar) dan detail_layanan.php (detail per slug).
 */

$daftar_layanan = [

    'legalisir-ijazah' => [
        'judul' => 'Legalisir Ijazah SD, SMP dan Paket A, B, C',
        'ringkasan' => 'Pengesahan salinan ijazah untuk keperluan administrasi.',
        'icon' => 'bi-patch-check',
        'persyaratan' => [
            'Ijazah Asli dan Fotokopi maksimal 10 (sepuluh) lembar yang sudah dilegalisir oleh Sekolah Asal',
            'Materai Rp10.000, 1 lembar',
            'Bersedia menandatangani surat pernyataan tanggung jawab mutlak di atas materai',
        ],
        'jangka_waktu' => '60 Menit (apabila berkas sudah lengkap)',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Legalisir Ijazah',
        'prosedur' => ['Pemohon', 'Penyampaian berkas', 'Verifikasi berkas oleh bidang', 'Penandatanganan oleh Pejabat Dinas', 'Penyerahan berkas kepada pemohon', 'Permohonan selesai'],
        'narahubung' => ['Kepala Seksi Kurikulum dan Penilaian SMP', 'Kepala Seksi Kurikulum dan Penilaian SD', 'Pengembang Kurikulum Ahli Muda PAUD dan PNF'],
    ],

    'keterangan-kesalahan-ijazah' => [
        'judul' => 'Surat Keterangan Kesalahan Penulisan Ijazah SD, SMP dan Paket',
        'ringkasan' => 'Penerbitan surat keterangan atas kesalahan penulisan pada ijazah.',
        'icon' => 'bi-file-earmark-text',
        'persyaratan' => [
            'Ijazah asli',
            'KK dan Akta Kelahiran',
            'Surat Keterangan kesalahan penulisan/kerusakan/kehilangan dari Kepala Lembaga/satuan pendidikan',
            'Bersedia menandatangani surat pernyataan tanggung jawab mutlak di atas materai',
            'Materai Rp10.000, 1 lembar',
        ],
        'jangka_waktu' => '1–3 hari',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Dokumen Surat Keterangan',
        'prosedur' => ['Pemohon', 'Penyampaian persyaratan', 'Pengecekan ijazah dengan berkas lain', 'Validasi data dan permohonan tanda tangan pimpinan', 'Setelah tanda tangan pimpinan, berkas selesai bisa diambil', 'Permohonan selesai'],
        'narahubung' => ['Kepala Seksi Kurikulum dan Penilaian SMP', 'Kepala Seksi Kurikulum dan Penilaian SD', 'Pengembang Kurikulum Ahli Muda PAUD dan PNF'],
    ],

    'keterangan-kehilangan-ijazah' => [
        'judul' => 'Surat Keterangan Kehilangan/Kerusakan Ijazah SD, SMP dan Paket',
        'ringkasan' => 'Penerbitan surat keterangan pengganti apabila ijazah hilang atau rusak.',
        'icon' => 'bi-file-earmark-x',
        'persyaratan' => [
            'Ijazah asli',
            'Surat Keterangan kesalahan penulisan/kerusakan/kehilangan dari Kepala Lembaga/satuan pendidikan',
            'Bersedia menandatangani surat pernyataan tanggung jawab mutlak di atas materai',
            'Materai Rp10.000, 1 lembar',
            'Surat pernyataan dari 2 (dua) orang saksi/teman satu angkatan pada sekolah yang sama, bermaterai, dengan menunjukkan fotokopi ijazah',
            'Fotokopi buku induk dan fotokopi ijazah pemohon',
            'Surat Keterangan dari Kepala Satuan Pendidikan yang menyatakan siswa tersebut benar-benar telah lulus',
        ],
        'jangka_waktu' => '1–3 hari',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Dokumen Surat Keterangan',
        'prosedur' => ['Pemohon', 'Penyampaian persyaratan', 'Pengecekan ijazah dengan berkas lain', 'Validasi data dan permohonan tanda tangan pimpinan', 'Setelah tanda tangan pimpinan, berkas selesai bisa diambil', 'Permohonan selesai'],
        'narahubung' => ['Kepala Seksi Kurikulum dan Penilaian SMP', 'Kepala Seksi Kurikulum dan Penilaian SD', 'Pengembang Kurikulum Ahli Muda PAUD dan PNF'],
    ],

    'ijin-operasional' => [
        'judul' => 'Pengajuan Ijin Operasional Pendirian Lembaga SD/SMP/PAUD/PNF Swasta Baru',
        'ringkasan' => 'Rekomendasi pendirian satuan pendidikan swasta baru.',
        'icon' => 'bi-building-add',
        'persyaratan' => [
            'Proposal pendirian sekolah swasta',
            'Surat izin menerima siswa baru',
            'Proses belajar mengajar',
        ],
        'jangka_waktu' => '60 hari kerja',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Surat Rekomendasi Pendirian Sekolah Swasta',
        'prosedur' => ['Pemohon', 'Penyampaian persyaratan', 'Verifikasi berkas oleh bidang', 'Supervisi oleh tim Dinas Pendidikan', 'Rapat koordinasi hasil supervisi', 'Pemberian rekomendasi pendirian oleh pejabat Dinas', 'Penyerahan berkas kepada pemohon'],
        'narahubung' => ['Kepala Seksi Kelembagaan dan Sarana Prasarana SMP', 'Kepala Seksi Kelembagaan dan Sarana Prasarana SD', 'Kepala Seksi Kelembagaan dan Sarana Prasarana PAUD dan Pendidikan Non Formal'],
    ],

    'mutasi-siswa' => [
        'judul' => 'Surat Rekomendasi Mutasi Siswa Luar Kabupaten dan Provinsi',
        'ringkasan' => 'Rekomendasi perpindahan siswa antar kabupaten/provinsi.',
        'icon' => 'bi-arrow-left-right',
        'persyaratan' => [
            'Surat permohonan pindah dari orang tua',
            'Surat pernyataan dari kepala sekolah asal',
            'Fotokopi buku rapor (identitas dan catatan mutasi pada halaman belakang rapor)',
            'Fotokopi kartu NISN siswa yang bersangkutan',
            'Bukti penerimaan di sekolah baru',
            'Bukti siswa telah dikeluarkan dari DAPODIK sekolah asal',
        ],
        'jangka_waktu' => '1–2 hari kerja',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Surat Mutasi Siswa',
        'prosedur' => ['Pemohon', 'Penyampaian persyaratan ke petugas pelayanan', 'Pemeriksaan kelengkapan berkas di bidang terkait', 'Proses dan pembuatan rekomendasi mutasi', 'Persetujuan dan penandatanganan rekomendasi mutasi oleh pejabat Dinas', 'Penyerahan surat rekomendasi mutasi kepada pemohon'],
        'narahubung' => ['Sub Koordinator Urusan Peserta Didik dan Pengembangan Karakter SMP', 'Sub Koordinator Urusan Peserta Didik dan Pengembangan Karakter SD', 'Kepala Seksi Peserta Didik dan Pengembangan Karakter PAUD dan Pendidikan Non Formal'],
    ],

    'pencairan-bos' => [
        'judul' => 'Surat Rekomendasi Pencairan Dana BOS',
        'ringkasan' => 'Rekomendasi untuk pencairan dana Bantuan Operasional Sekolah.',
        'icon' => 'bi-cash-coin',
        'persyaratan' => [
            'Pelaporan BOS Online',
            'SPJ tahap sebelumnya',
        ],
        'jangka_waktu' => '30 Menit (apabila berkas sudah lengkap)',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Surat Rekomendasi Pencairan',
        'prosedur' => ['Pemohon', 'Menuju bidang terkait', 'Verifikasi berkas', 'Apabila memenuhi syarat, pemohon menerima rekomendasi'],
        'narahubung' => ['Sub Koordinator Urusan PTP'],
    ],

    'rekomendasi-pip' => [
        'judul' => 'Surat Rekomendasi Program Indonesia Pintar (PIP)',
        'ringkasan' => 'Rekomendasi penyaluran dana PIP bagi siswa penerima.',
        'icon' => 'bi-mortarboard',
        'persyaratan' => [
            'Surat Pernyataan Kepala Sekolah',
            'SPTJM bermaterai',
            'Surat Kuasa',
            'Daftar siswa penerima dana PIP',
        ],
        'jangka_waktu' => '1 hari kerja',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Surat Rekomendasi PIP',
        'prosedur' => ['Pemohon', 'Penyampaian berkas', 'Pemeriksaan berkas di bidang', 'Pemberian rekomendasi oleh pejabat Dinas', 'Penyerahan berkas kepada pemohon'],
        'narahubung' => ['Sub Koordinator Urusan Peserta Didik dan Pengembangan Karakter SMP', 'Sub Koordinator Urusan Peserta Didik dan Pengembangan Karakter SD'],
    ],

    'pengusulan-pensiun' => [
        'judul' => 'Pengusulan Pensiun',
        'ringkasan' => 'Pengusulan SK pensiun bagi PNS di lingkungan Dinas Pendidikan.',
        'icon' => 'bi-person-check',
        'persyaratan' => [
            'Surat Pengantar dari Satuan Kerja', 'Fotokopi Karpeg', 'Fotokopi SK CPNS', 'Fotokopi SK PNS',
            'Fotokopi SK Pangkat Terakhir', 'Fotokopi SK Berkala Terakhir', 'Fotokopi SK Pengangkatan dalam Jabatan Terakhir',
            'Kartu Taspen', 'Surat Tidak Dijatuhi Hukuman Disiplin', 'Surat Pernyataan Menyimpan Barang (diketahui Kepala Dinas)',
            'Fotokopi Surat Nikah (legalisir KUA)', 'Fotokopi KTP suami-istri (legalisir Capil)',
            'Fotokopi akte anak di bawah umur 25 tahun (legalisir Capil)', 'Fotokopi Surat Keterangan Kuliah (khusus anak yang masih kuliah)',
            'Fotokopi KK (legalisir Capil)', 'Daftar Susunan Keluarga dari Instansi/Camat', 'Model C', 'SKP 2 Tahun Terakhir',
            'Fotokopi NPWP', 'Fotokopi Karis/Karsu', 'Fotokopi Rekening Bank Nasional', 'Pas foto 3x4 hitam putih (6 lembar)',
            'Surat Pernyataan tidak sedang menjalani proses pidana/pernah dipidana penjara berdasarkan putusan pengadilan berkekuatan hukum tetap (bermaterai)',
            'Seluruh berkas di-scan sesuai aslinya',
        ],
        'jangka_waktu' => 'Konsultasi/Revisi/Perbaikan: 60 menit',
        'biaya' => 'Gratis',
        'produk_layanan' => 'SK Pensiun; Catatan/keterangan revisi/perbaikan berkas',
        'prosedur' => ['Pemohon', 'Penyampaian berkas', 'Pemeriksaan berkas di bidang', 'Pengusulan berkas ke BKPSDM Kabupaten Sumenep', 'Penyerahan berkas kepada pemohon'],
        'narahubung' => ['Kasubbag Umum, Kearsipan dan Kepegawaian'],
    ],

    'pengusulan-cuti' => [
        'judul' => 'Pengusulan Cuti',
        'ringkasan' => 'Pengusulan cuti melahirkan, haji besar, haji/umroh, dan cuti sakit.',
        'icon' => 'bi-calendar2-week',
        'format' => 'grouped',
        'grup_persyaratan' => [
            'Cuti Melahirkan' => [
                'Surat Pengantar dari SKPD', 'Surat Permohonan Cuti', 'Surat Keterangan Cuti dari Bidan atau Dokter',
                'Fotokopi Buku Pemeriksaan Ibu Hamil', 'Fotokopi KK', 'Fotokopi SK Pangkat Terakhir',
                'Semuanya rangkap 2', 'Semua berkas di-scan sesuai aslinya',
            ],
            'Cuti Haji Besar' => [
                'Surat Pengantar dari SKPD', 'Surat Permohonan Cuti', 'Fotokopi SPPH (Surat Pendaftaran Pergi Haji)',
                'Fotokopi BPIH (setoran awal)', 'Fotokopi BPIH (pelunasan)', 'Fotokopi Keterangan Kesehatan',
                'Fotokopi SK Pangkat Terakhir', 'Fotokopi KTP',
                'Surat pernyataan tidak mengganggu jam kerja PNS/jam mengajar bagi PNS Guru dari Kepala OPD',
                'Semuanya rangkap 2', 'Semua berkas di-scan sesuai aslinya',
            ],
            'Cuti Haji/Umroh' => [
                'Surat Pengantar dari SKPD', 'Surat Permohonan Cuti', 'Surat Keterangan Umroh dari Yayasan',
                'Fotokopi Jadwal Keberangkatan', 'Fotokopi SK Pangkat Terakhir',
                'Surat pernyataan tidak mengganggu jam kerja PNS/jam mengajar bagi PNS Guru dari Kepala OPD',
                'Semuanya rangkap 2', 'Semua berkas di-scan sesuai aslinya',
            ],
            'Cuti Sakit' => [
                'Surat Pengantar dari SKPD', 'Surat Permohonan Cuti',
                'Surat Keterangan Hasil Pengujian Kesehatan dari RSUD', 'Fotokopi SK Pangkat Terakhir',
                'Semuanya rangkap 2', 'Semua berkas di-scan sesuai aslinya',
            ],
        ],
        'jangka_waktu' => 'Konsultasi/Revisi/Perbaikan: 60 menit',
        'biaya' => 'Gratis',
        'produk_layanan' => 'SK Cuti Melahirkan, Haji Besar, Haji/Umroh, dan Sakit; Catatan/keterangan revisi/perbaikan berkas',
        'prosedur' => ['Pemohon', 'Penyampaian berkas', 'Pemeriksaan berkas di bidang', 'Pengusulan berkas ke BKPSDM Kabupaten Sumenep', 'Penyerahan berkas kepada pemohon'],
        'narahubung' => ['Kasubbag Umum, Kearsipan dan Kepegawaian'],
    ],

    'gaji-berkala' => [
        'judul' => 'Pengusulan Gaji Berkala',
        'ringkasan' => 'Pengusulan penerbitan SK kenaikan gaji berkala.',
        'icon' => 'bi-graph-up',
        'persyaratan' => [
            'Surat Pengantar dari Lembaga Sekolah',
            'Fotokopi Pangkat Terakhir',
            'Fotokopi Berkala Terakhir',
            'Surat Keterangan Bebas NARKOBA',
            'Bukti Lunas PBB',
        ],
        'jangka_waktu' => 'Konsultasi/Revisi/Perbaikan: 30 menit',
        'biaya' => 'Gratis',
        'produk_layanan' => 'SK Berkala dari Dinas Pendidikan',
        'prosedur' => ['Pemohon', 'Penyampaian berkas', 'Pemeriksaan berkas di bidang', 'Proses SK berkala', 'Penyerahan berkas kepada pemohon'],
        'narahubung' => ['Kasubbag Umum, Kearsipan dan Kepegawaian'],
    ],

    'pelayanan-dapodik' => [
        'judul' => 'Pelayanan Dapodik',
        'ringkasan' => 'Layanan terkait akun operator, NPSN, NPYP, dan data siswa pada Dapodik.',
        'icon' => 'bi-hdd-network',
        'format' => 'grouped',
        'grup_persyaratan' => [
            'Permohonan Kode Referal Operator Yayasan / Update / Pembuatan Akun Dapodik' => [
                'Surat permohonan dari Kepala Sekolah yang ditujukan kepada Kepala Dinas Pendidikan',
                'Surat Tugas dari Kepala Sekolah sebagai Operator Dapodik Sekolah dan email yang akan digunakan',
            ],
            'Pengajuan Nomor Pokok Sekolah Nasional (NPSN)' => [
                'Surat permohonan dari Kepala Sekolah yang ditujukan kepada Kepala Dinas Pendidikan',
                'Formulir NPSN',
                'Izin penyelenggaraan pendidikan yang masih berlaku',
            ],
            'Approval Peserta Didik Baru di Luar Dapodik (Jenjang MI)' => [
                'Surat pengantar dari Kepala Sekolah yang ditujukan kepada Kepala Dinas Pendidikan Kabupaten Sumenep',
                'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) dari Kepala Sekolah bermaterai Rp10.000',
                'Rekapitulasi Penerimaan Siswa Baru',
                'Print out NISN dari website nisn.data.kemdikbud.go.id',
                'Fotokopi akte kelahiran atau Kartu Keluarga',
                'Fotokopi ijazah atau Surat Keterangan Lulus',
            ],
            'Approval Data Siswa Mutasi dari Luar Dapodik (RA, MI, dan MTs)' => [
                'Surat pengantar dari Kepala Sekolah yang ditujukan kepada Kepala Dinas Pendidikan Kabupaten Sumenep',
                'Surat Pernyataan Tanggung Jawab Mutlak (SPTJM) dari Kepala Sekolah bermaterai Rp10.000',
                'Rekapitulasi usulan persetujuan penambahan data siswa mutasi di luar Dapodik',
                'Surat mutasi dari sekolah asal (MTs)',
                'Surat menerima dari sekolah yang dituju (SMP Negeri/Swasta)',
                'Fotokopi akte kelahiran dan Kartu Keluarga',
                'Fotokopi ijazah',
                'Fotokopi rapor 2 semester terakhir',
            ],
            'Pengajuan Nomor Pokok Yayasan Pendidikan (NPYP)' => [
                'Surat permohonan dari Ketua Yayasan yang ditujukan kepada Kepala Dinas Pendidikan',
                'Formulir NPYP',
                'Akte Notaris dan SK Kemenkumham (hardfile dan softfile PDF)',
                'Foto papan nama Yayasan beserta gedung Yayasan (hardfile dan softfile PDF)',
            ],
        ],
        'jangka_waktu' => '1 hari kerja',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Terbitnya kode referal operator Yayasan / update akun Dapodik; Terbitnya NPSN; Terbitnya NPYP; Approval data siswa baru/mutasi dari luar Dapodik; Persetujuan permohonan di datadik.kemdikbud.go.id/manage',
        'prosedur' => ['Pemohon', 'Penyampaian berkas', 'Pemeriksaan berkas di bidang', 'Disposisi pimpinan', 'Proses pengajuan', 'Konfirmasi kepada operator sekolah'],
        'narahubung' => ['Sub Koordinator Urusan Peserta Didik dan Pengembangan Karakter SMP', 'Sub Koordinator Urusan Peserta Didik dan Pengembangan Karakter SD', 'Kepala Seksi Peserta Didik dan Pengembangan Karakter PAUD dan Pendidikan Non Formal'],
    ],

    'ijin-belajar' => [
        'judul' => 'Pengusulan Ijin Belajar atau Tugas Belajar',
        'ringkasan' => 'Pengusulan izin belajar/tugas belajar bagi PNS di lingkungan Dinas Pendidikan.',
        'icon' => 'bi-book',
        'persyaratan' => [
            'Usulan atasan langsung',
            'Status sebagai PNS',
            'Penilaian PKG 2 tahun terakhir',
            'Rekomendasi dari atasan',
        ],
        'jangka_waktu' => '3 hari kerja',
        'biaya' => 'Gratis',
        'produk_layanan' => 'Usulan ke BKPSDM Kabupaten Sumenep',
        'prosedur' => ['Pemohon', 'Pemeriksaan berkas di bidang', 'Proses pengusulan'],
        'narahubung' => ['Kepala Bidang Ketenagaan'],
    ],

];