<?php
include_once __DIR__ . '/../koneksi.php';

function checkRegister($data, &$errors)
{
    global $koneksi;

    $name             = trim($data['name'] ?? '');
    $email            = trim($data['email'] ?? '');
    $password         = $data['password'] ?? '';
    $password_confirm = $data['password_confirm'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = "Semua kolom wajib diisi.";
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
        return;
    }

    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
        return;
    }

    if ($password !== $password_confirm) {
        $errors[] = "Konfirmasi password tidak cocok.";
        return;
    }

    $emailEsc = mysqli_real_escape_string($koneksi, $email);
    $cek      = mysqli_query($koneksi, "SELECT id FROM admin WHERE email = '$emailEsc' LIMIT 1");

    if ($cek && mysqli_num_rows($cek) > 0) {
        $errors[] = "Email sudah terdaftar. Gunakan email lain atau login.";
        return;
    }

    $nameEsc = mysqli_real_escape_string($koneksi, $name);
    $hash    = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO admin (name, email, password) VALUES ('$nameEsc', '$emailEsc', '$hash')";

    if (!mysqli_query($koneksi, $sql)) {
        $errors[] = "Gagal mendaftarkan akun: " . mysqli_error($koneksi);
        return;
    }

    header("Location: register.php?sukses=1");
    exit;
}
?>
