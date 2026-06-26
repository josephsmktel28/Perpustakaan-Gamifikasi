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

// Ambil konfigurasi dari Environment Variables (.env lokal atau Render Dashboard)
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$db   = getenv('DB_DATABASE') ?: 'perpustakaan_gamifikasi';
$port = getenv('DB_PORT') ?: 3306;

$conn = mysqli_init();

// Jika host bukan localhost/127.0.0.1 (berarti koneksi remote/Aiven), gunakan SSL
if ($host !== 'localhost' && $host !== '127.0.0.1') {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    // Jika lokal, jangan gunakan SSL
    mysqli_real_connect($conn, $host, $user, $pass, $db, $port);
}

if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
