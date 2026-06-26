<?php
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

$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$db   = getenv('DB_DATABASE') ?: 'perpustakaan_gamifikasi';
$port = getenv('DB_PORT') ?: 3306;
$ssl_mode = getenv('MYSQL_ATTR_SSL_CA') ? true : false; // For mysqli we can initialize with ssl if needed

$conn = mysqli_init();
if(getenv('DB_HOST')) {
    // If using external DB like Aiven, require SSL
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_real_connect($conn, $host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL);
} else {
    mysqli_real_connect($conn, $host, $user, $pass, $db, $port);
}

if(!$conn){
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
