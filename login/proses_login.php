<?php
session_start();
include_once '../koneksi.php';

function checkLogin($data, &$errors)
{
    global $koneksi;

    $email    = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Email dan password wajib diisi.";
        return;
    }

    $emailEsc = mysqli_real_escape_string($koneksi, $email);
    $sql      = "SELECT * FROM admin WHERE email = '$emailEsc' LIMIT 1";
    $result   = mysqli_query($koneksi, $sql);

    if (!$result) {
        $errors[] = "Terjadi kesalahan pada server. Coba lagi nanti.";
        return;
    }

    $user = mysqli_fetch_assoc($result);

    if (!$user) {
        $errors[] = "Email tidak ditemukan.";
        return;
    }

    if (!password_verify($password, $user['password'])) {
        $errors[] = "Password salah.";
        return;
    }

    $_SESSION['user'] = $user;
    header("Location: ../index.php");
    exit;
}
?>
