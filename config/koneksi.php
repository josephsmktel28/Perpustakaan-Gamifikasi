<?php
// Cek apakah ada file .env (opsional, Render akan mengabaikan ini)
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if(count($parts) == 2) {
            putenv(trim($parts[0]) . '=' . trim($parts[1]));
        }
    }
}

// Deteksi cerdas: jika DB_HOST ada di environment variable (di set di Render), 
// maka otomatis kita anggap sedang berjalan di server (Production/Aiven).
if (getenv('DB_HOST')) {
    // ==========================================
    // KONEKSI AIVEN (PRODUCTION DI RENDER)
    // ==========================================
    $host = getenv('DB_HOST');
    $user = getenv('DB_USERNAME');
    $pass = getenv('DB_PASSWORD');
    $db   = getenv('DB_DATABASE');
    $port = getenv('DB_PORT');

    $conn = mysqli_init();
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    // ==========================================
    // KONEKSI LOKAL (XAMPP)
    // ==========================================
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "perpustakaan_gamifikasi";
    $port = 3306;

    $conn = mysqli_init();
    mysqli_real_connect($conn, $host, $user, $pass, $db, $port);
}

if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
