<?php
// =====================================================================
// auth.php — Proteksi halaman admin
// Letakkan file ini di: admin_updated/auth.php
// Cara pakai: tambahkan baris berikut di PALING ATAS setiap file PHP
//   require_once __DIR__ . '/../auth.php';  (dari subfolder seperti berita/, galeri/, dll)
//   require_once __DIR__ . '/auth.php';     (dari root folder admin_updated/)
// =====================================================================

// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentukan path ke halaman login secara dinamis
// Menghitung berapa level subfolder dari root admin_updated
$loginPath = '../login/login.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {

    // Simpan halaman yang ingin diakses agar bisa redirect setelah login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    header("Location: " . $loginPath);
    exit();
}

// Cek apakah session sudah kadaluarsa (timeout 2 jam tidak aktif)
$sessionTimeout = 2 * 60 * 60; // 2 jam dalam detik
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $sessionTimeout) {
    // Session kadaluarsa — hapus semua session dan redirect ke login
    session_unset();
    session_destroy();
    header("Location: " . $loginPath . "?notif=timeout");
    exit();
}

// Perbarui waktu aktivitas terakhir
$_SESSION['last_activity'] = time();
