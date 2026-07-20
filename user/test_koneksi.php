<?php
// Aktifkan error reporting agar kesalahan terlihat jelas
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Panggil file koneksi
include 'koneksi.php';

echo "<h2>🔍 Hasil Tes Koneksi Database</h2>";

// Cek apakah variabel $conn ada (dari koneksi.php)
if (isset($conn)) {
    echo "✅ Variabel koneksi (\$conn) berhasil dimuat.<br>";

    // Lakukan query sederhana untuk memastikan bisa membaca tabel
    $query = "SHOW TABLES FROM disdik_sumenep";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo "✅ Query berhasil dijalankan. <br>";
        echo "📋 Daftar tabel di database <strong>disdik_sumenep</strong>: <br><ul>";
        
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_array($result)) {
                echo "<li>" . $row[0] . "</li>";
            }
        } else {
            echo "<li>Tidak ada tabel ditemukan (database mungkin kosong).</li>";
        }
        echo "</ul>";
    } else {
        // Jika query gagal, tampilkan error dari MySQL
        echo "❌ Query gagal: " . mysqli_error($conn);
    }

} else {
    echo "❌ Variabel \$conn tidak ditemukan. Periksa file koneksi.php Anda.";
}
?>