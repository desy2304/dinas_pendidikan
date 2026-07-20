<?php
$host = "localhost";
$user = "root";
$pass = "";           // kosong kalau pakai Laragon default
$db   = "disdik_sumenep"; // sesuaikan nama database kamu

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die(
        "Koneksi ke database gagal.<br>" .
        "Pesan error: " . mysqli_connect_error() . "<br><br>" .
        "Coba cek:<br>" .
        "1. Apakah service MySQL/MariaDB sudah berjalan? (Laragon/XAMPP)<br>" .
        "2. Apakah database '" . htmlspecialchars($db) . "' sudah di-import dari file disdik_sumenep.sql?<br>" .
        "3. Apakah \$host, \$user, \$pass di file ini sudah sesuai dengan setting MySQL kamu?"
    );
}