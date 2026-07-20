<?php
/**
 * KONFIGURASI UPLOAD BERSAMA  --  taruh file ini di admin_updated/
 * (sejajar dengan koneksi.php)
 * ---------------------------------------------------------------------
 * Versi ini menyimpan file upload LANGSUNG ke dalam folder img/ milik
 * branch "main" (user), bukan ke folder terpisah. Keuntungannya: sisi
 * main/user tidak perlu perubahan apa pun, karena file memang ada di
 * dalam foldernya sendiri secara normal.
 *
 * Struktur folder yang diasumsikan (SESUAIKAN "main" kalau nama
 * folder branch user kamu berbeda):
 *
 *   htdocs/
 *   ├── admin_updated/     <-- branch "admin" (file ini ada di sini)
 *   └── main/               <-- branch "main"/user
 *       └── img/            <-- file fisik upload disimpan ke sini
 *
 * WAJIB: tambahkan baris berikut ke .gitignore di branch "main",
 * supaya folder ini tidak ikut ter-commit / tidak konflik saat
 * checkout, pull, atau clean:
 *
 *   img/galeri/*
 *   img/berita/*
 *   img/pengumuman/*
 *   img/kegiatan/*
 *   !img/galeri/.gitkeep
 *   !img/berita/.gitkeep
 *   !img/pengumuman/.gitkeep
 *   !img/kegiatan/.gitkeep
 *
 * (baris "!...gitkeep" opsional, cuma supaya folder kosongnya tetap
 * ke-track strukturnya meski isinya diabaikan)
 *
 * Cara pakai di file upload (misal admin_updated/galeri/proses_tambah_prestasi.php):
 *
 *   include __DIR__ . '/../konfigurasi_upload.php';
 *   $folderUploadPath = UPLOAD_DIR . 'img/galeri/';   // untuk mkdir/move_uploaded_file
 *   ...
 *   $namaGambar = 'img/galeri/' . $namaFile;          // disimpan ke DB apa adanya
 *
 * Sisi user (branch main) TIDAK perlu file ini -- tinggal pakai
 * <img src="img/galeri/<?= $row['gambar'] ?>"> seperti biasa, karena
 * filenya memang sudah ada langsung di foldernya sendiri.
 */

// Path FISIK di server, menunjuk ke folder project branch "main".
// __DIR__ di sini = admin_updated/, jadi naik 1 tingkat ke htdocs,
// lalu masuk ke folder project "main".
// GANTI 'main' di bawah ini sesuai nama folder branch user kamu yang sebenarnya.
define('UPLOAD_DIR', dirname(__DIR__) . '/main/');

// Path relatif untuk <img src="..."> DARI SISI ADMIN (bukan sisi user).
// Semua halaman admin ada 1 folder di dalam admin_updated (misal galeri/, berita/),
// jadi untuk menampilkan preview di admin perlu naik 2 tingkat lalu masuk ke folder main.
define('UPLOAD_URL_ADMIN', '../../main/');
