-- =====================================================================
-- DATABASE: disdik_sumenep
-- Deskripsi : Skema basis data untuk Sistem Website Dinas Pendidikan
--             Kabupaten Sumenep
-- Mesin     : MySQL 8 / MariaDB 10.4+ (utf8mb4, InnoDB)
-- Disesuaikan PERSIS dengan ERD final (erd_disdik_sumenep_final.html):
--   - penamaan kolom gaya Laravel: id, admin_id, kategori_id, bidang_id,
--     pengaduan_id (bukan id_berita / id_admin dst seperti versi awal)
--   - entitas ADMIN (bukan ADMIN)
--   - galeri kini punya admin_id FK
-- =====================================================================

CREATE DATABASE IF NOT EXISTS disdik_sumenep
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE disdik_sumenep;

SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================================
-- 1. ADMIN
-- Fungsi   : Autentikasi admin (use case: Login)
-- =====================================================================
DROP TABLE IF EXISTS admin;
CREATE TABLE admin (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(100)  NOT NULL,
  email        VARCHAR(150)  NOT NULL UNIQUE,
  password     VARCHAR(255)  NOT NULL,      -- simpan dalam bentuk hash (bcrypt/argon2)
  created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- 2. KATEGORI_BERITA
-- Fungsi   : Pengelompokan berita (use case: Kelola kategori berita)
-- Relasi   : "Memiliki" -> kategori_berita 1 ke banyak berita
-- =====================================================================
DROP TABLE IF EXISTS kategori_berita;
CREATE TABLE kategori_berita (
  id    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama  VARCHAR(100) NOT NULL,
  slug  VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- =====================================================================
-- 3. BERITA
-- Fungsi   : Fitur utama website (use case: Kelola berita / Lihat berita)
-- Relasi   : "Menulis" -> admin menulis berita (admin_id)
--            "Memiliki" -> kategori_berita memiliki berita (kategori_id)
-- =====================================================================
DROP TABLE IF EXISTS berita;
CREATE TABLE berita (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id         INT UNSIGNED NULL,
  kategori_id     INT UNSIGNED NULL,
  judul           VARCHAR(200) NOT NULL,
  slug            VARCHAR(220) NOT NULL UNIQUE,
  isi             LONGTEXT     NOT NULL,
  gambar          VARCHAR(255) NULL,
  status          ENUM('draf','terbit') NOT NULL DEFAULT 'draf',
  tanggal_publish DATE         NULL,
  created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_berita_admin
    FOREIGN KEY (admin_id) REFERENCES admin(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_berita_kategori
    FOREIGN KEY (kategori_id) REFERENCES kategori_berita(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_berita_status (status)
) ENGINE=InnoDB;

-- =====================================================================
-- 4. PENGUMUMAN
-- Fungsi   : Fitur utama website (use case: Kelola pengumuman / Lihat pengumuman)
-- Relasi   : "Membuat" -> admin membuat pengumuman (admin_id)
-- =====================================================================
DROP TABLE IF EXISTS pengumuman;
CREATE TABLE pengumuman (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id     INT UNSIGNED NULL,
  judul       VARCHAR(200) NOT NULL,
  slug        VARCHAR(220) NOT NULL UNIQUE,
  isi         LONGTEXT     NOT NULL,
  gambar      VARCHAR(255) NULL,
  tanggal     DATE         NOT NULL,
  status      ENUM('draf','terbit') NOT NULL DEFAULT 'draf',
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pengumuman_admin
    FOREIGN KEY (admin_id) REFERENCES admin(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_pengumuman_status (status)
) ENGINE=InnoDB;

-- =====================================================================
-- 5. GALERI
-- Fungsi   : Fitur utama website (use case: Kelola galeri / Lihat galeri kegiatan)
-- Relasi   : galeri kini punya admin_id FK sesuai ERD final (siapa yang
--            mengunggah foto)
-- =====================================================================
DROP TABLE IF EXISTS galeri;
CREATE TABLE galeri (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id     INT UNSIGNED NULL,
  judul       VARCHAR(200) NOT NULL,
  gambar      VARCHAR(255) NOT NULL,
  keterangan  TEXT         NULL,
  tanggal     DATE         NOT NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_galeri_admin
    FOREIGN KEY (admin_id) REFERENCES admin(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- 6. PROFIL
-- Fungsi   : Data organisasi statis (use case: Kelola profil instansi /
--            Lihat profil instansi). Hanya 1 baris (singleton row).
-- Catatan  : ERD final tidak punya created_at untuk profil, hanya
--            updated_at.
-- =====================================================================
DROP TABLE IF EXISTS profil;
CREATE TABLE profil (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  selayang_pandang  VARCHAR(255) NULL,
  visi              VARCHAR(255) NULL,
  misi              TEXT         NULL,
  alamat            VARCHAR(255) NULL,
  telepon           VARCHAR(30)  NULL,
  email             VARCHAR(150) NULL,
  facebook          VARCHAR(255) NULL,
  youtube           VARCHAR(255) NULL,
  instagram         VARCHAR(255) NULL,
  updated_at        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                   ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- 7. BIDANG
-- Fungsi   : Relasi ke pegawai (use case: Kelola data bidang /
--            Lihat pegawai dan bidang)
-- =====================================================================
DROP TABLE IF EXISTS bidang;
CREATE TABLE bidang (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(150) NOT NULL,
  tugas       TEXT         NULL,
  fungsi      TEXT         NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================================
-- 8. PEGAWAI
-- Fungsi   : Halaman pimpinan & staff (use case: Kelola data pegawai /
--            Lihat pegawai dan bidang)
-- Relasi   : "Menaungi" -> bidang menaungi banyak pegawai (bidang_id)
-- =====================================================================
DROP TABLE IF EXISTS pegawai;
CREATE TABLE pegawai (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  bidang_id   INT UNSIGNED NULL,
  nama        VARCHAR(150) NOT NULL,
  nip         VARCHAR(30)  NULL UNIQUE,
  jabatan     VARCHAR(150) NOT NULL,
  foto        VARCHAR(255) NULL,
  email       VARCHAR(150) NULL,
  status      ENUM('aktif','nonaktif') NOT NULL DEFAULT 'aktif',
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pegawai_bidang
    FOREIGN KEY (bidang_id) REFERENCES bidang(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_pegawai_status (status)
) ENGINE=InnoDB;

-- =====================================================================
-- 9. PENGADUAN
-- Fungsi   : Fitur tambahan (use case: Ajukan pengaduan / Dapat nomor
--            tiket / Cek status tiket / Lihat daftar pengaduan -- Admin)
-- Catatan  : kategori kini bertipe ENUM sesuai ERD final (bukan teks
--            bebas seperti versi sebelumnya). Sesuaikan daftar nilai
--            di bawah dengan kategori riil yang dipakai aplikasi.
-- =====================================================================
DROP TABLE IF EXISTS pengaduan;
CREATE TABLE pengaduan (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama        VARCHAR(150) NOT NULL,
  email       VARCHAR(150) NULL,
  telepon     VARCHAR(30)  NULL,
  no_tiket    VARCHAR(20)  NOT NULL UNIQUE,  -- contoh: PGD-20260630-AB12
  kategori    ENUM('sarana_prasarana','kepegawaian','pelayanan','lainnya')
               NOT NULL DEFAULT 'lainnya',
  judul       VARCHAR(200) NOT NULL,
  isi         LONGTEXT     NOT NULL,
  lampiran    VARCHAR(255) NULL,
  status      ENUM('diajukan','diproses','ditanggapi','ditutup')
               NOT NULL DEFAULT 'diajukan',
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                             ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pengaduan_status (status),
  INDEX idx_pengaduan_notiket (no_tiket)
) ENGINE=InnoDB;

-- =====================================================================
-- 10. TANGGAPAN_PENGADUAN
-- Fungsi   : Relasi ke pengaduan (use case: Balas pengaduan -- Admin)
-- Relasi   : "Memiliki" -> pengaduan memiliki banyak tanggapan (pengaduan_id)
--            "Membalas" -> admin membalas lewat tanggapan_pengaduan (admin_id)
-- =====================================================================
DROP TABLE IF EXISTS tanggapan_pengaduan;
CREATE TABLE tanggapan_pengaduan (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pengaduan_id  INT UNSIGNED NOT NULL,
  admin_id       INT UNSIGNED NULL,   -- admin yang menanggapi
  isi           LONGTEXT     NOT NULL,
  created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_tanggapan_pengaduan
    FOREIGN KEY (pengaduan_id) REFERENCES pengaduan(id)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_tanggapan_admin
    FOREIGN KEY (admin_id) REFERENCES admin(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- TRIGGER opsional: otomatis ubah status pengaduan jadi 'ditanggapi'
-- saat admin menambahkan tanggapan pertama. Sesuai use case "Balas
-- pengaduan" yang meng-extend "Lihat daftar pengaduan".
-- =====================================================================
DELIMITER $$
CREATE TRIGGER trg_tanggapan_update_status
AFTER INSERT ON tanggapan_pengaduan
FOR EACH ROW
BEGIN
  UPDATE pengaduan
  SET status = 'ditanggapi'
  WHERE id = NEW.pengaduan_id
    AND status IN ('diajukan','diproses');
END$$
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================================
-- DATA AWAL (SEED) -- opsional, untuk kebutuhan testing
-- =====================================================================

-- Email   : admin@disdiksumenep.go.id
-- Password: admin123 (plain, untuk login saat testing)
INSERT INTO admin (name, email, password) VALUES
('Administrator', 'admin@disdiksumenep.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO kategori_berita (nama, slug) VALUES
('Kegiatan', 'kegiatan'),
('Kebijakan', 'kebijakan'),
('Prestasi', 'prestasi');

INSERT INTO bidang (nama, tugas, fungsi) VALUES
('Sekretariat', 'Mengoordinasikan administrasi umum dinas', 'Perencanaan, keuangan, dan umum'),
('Bidang Pembinaan SD', 'Membina satuan pendidikan jenjang SD', 'Kurikulum dan kesiswaan jenjang SD'),
('Bidang Pembinaan SMP', 'Membina satuan pendidikan jenjang SMP', 'Kurikulum dan kesiswaan jenjang SMP');

INSERT INTO profil (selayang_pandang, visi, misi, alamat, telepon, email) VALUES
('Dinas Pendidikan Kabupaten Sumenep merupakan unsur pelaksana urusan pemerintahan daerah di bidang pendidikan.',
 'Terwujudnya layanan pendidikan yang merata, bermutu, dan berkarakter.',
 'Meningkatkan akses dan mutu pendidikan serta tata kelola yang transparan.',
 'Jl. DR. Cipto No.35, Desa Kolor, Sumenep',
 '0328 662322',
 'disdik@sumenepkab.go.id');

-- =====================================================================
-- CATATAN IMPLEMENTASI
-- 1. Kolom password WAJIB disimpan dalam bentuk hash (bcrypt/argon2),
--    jangan plain text.
-- 2. no_tiket pada PENGADUAN sebaiknya digenerate di sisi aplikasi
--    (bukan auto increment) dengan format unik, contoh:
--    PGD-YYYYMMDD-XXXX (XXXX = kode acak alfanumerik).
-- 3. Semua kolom gambar/foto/lampiran hanya menyimpan PATH file,
--    file fisik disimpan di folder storage/uploads pada server.
-- 4. Tabel PROFIL didesain sebagai singleton (idealnya hanya 1 baris).
--    Validasi ini perlu ditegakkan di level aplikasi, bukan di DB.
-- 5. Nilai ENUM kategori pada PENGADUAN ('sarana_prasarana',
--    'kepegawaian','pelayanan','lainnya') adalah asumsi awal -- ganti
--    sesuai daftar kategori riil yang dipakai di formulir pengaduan.
-- 6. Penamaan kolom di skema ini sengaja dibuat gaya Laravel (id,
--    admin_id, kategori_id, dst) agar langsung kompatibel dengan
--    Eloquent ORM bila proyek dibangun dengan Laravel.
-- 7. Tabel "admin" pada skema ini menggantikan "users" di ERD final
--    sesuai permintaan terbaru -- isi kolom (name, email, password)
--    tetap sama persis dengan ERD.
-- =====================================================================
