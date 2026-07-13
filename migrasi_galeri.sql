-- Migrasi: tambah dukungan kategori & video pada tabel galeri
-- Jalankan sekali saja di phpMyAdmin (tab SQL) pada database disdik_sumenep

ALTER TABLE galeri
  ADD COLUMN kategori ENUM('foto','video','kegiatan','prestasi') NOT NULL DEFAULT 'foto' AFTER judul,
  ADD COLUMN video_url VARCHAR(255) NULL AFTER gambar;

-- Catatan:
-- - Untuk item foto/kegiatan/prestasi: isi kolom `gambar`, kolom `video_url` boleh dikosongkan.
-- - Untuk item video: isi kolom `video_url` dengan link YouTube (boleh link biasa
--   https://www.youtube.com/watch?v=XXXXX atau https://youtu.be/XXXXX, otomatis
--   dikonversi ke format embed oleh kode PHP). Kolom `gambar` boleh dikosongkan
--   atau diisi thumbnail custom kalau ada.

-- Contoh data:
-- INSERT INTO galeri (judul, kategori, gambar, video_url, keterangan, tanggal) VALUES
-- ('Upacara Bendera', 'kegiatan', 'upacara.jpg', NULL, 'Upacara rutin hari Senin', '2026-06-15'),
-- ('Juara 1 Olimpiade Sains', 'prestasi', 'olimpiade.jpg', NULL, 'Tingkat Kabupaten', '2026-06-10'),
-- ('Profil Dinas Pendidikan', 'video', NULL, 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'Video profil singkat', '2026-06-01');
