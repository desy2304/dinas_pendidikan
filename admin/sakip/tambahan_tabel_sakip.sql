-- =====================================================================
-- TAMBAHAN TABEL: sakip
-- Fungsi   : Arsip dokumen SAKIP (Sistem Akuntabilitas Kinerja Instansi
--            Pemerintah) -- Renstra & Perjanjian Kinerja, LKjIP, IKU.
-- Catatan  : Tabel ini BELUM ADA di disdik_sumenep.sql awal, jadi perlu
--            dijalankan terpisah (via phpMyAdmin / mysql client) sebelum
--            halaman sakip.php bisa dipakai.
-- =====================================================================

USE disdik_sumenep;

DROP TABLE IF EXISTS sakip;
CREATE TABLE sakip (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id    INT UNSIGNED NULL,
  kategori    ENUM('renstra_pk', 'lkjip', 'iku') NOT NULL,
  judul       VARCHAR(200) NOT NULL,
  tahun       YEAR NOT NULL,
  file        VARCHAR(255) NOT NULL,   -- path file PDF, contoh: files/sakip/xxx.pdf
  keterangan  TEXT NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                          ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_sakip_admin
    FOREIGN KEY (admin_id) REFERENCES admin(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  INDEX idx_sakip_kategori (kategori),
  INDEX idx_sakip_tahun (tahun)
) ENGINE=InnoDB;
