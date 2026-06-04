<?php
include __DIR__ . '/../config/koneksi.php';

$columns = [
    'tanggal_deadline' => "tanggal_deadline DATE DEFAULT NULL",
    'denda' => "denda INT DEFAULT 0",
    'pelanggaran' => "pelanggaran VARCHAR(100) DEFAULT NULL",
    'review' => "review TEXT DEFAULT NULL",
    'rating' => "rating TINYINT DEFAULT NULL"
];

foreach ($columns as $column => $definition) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM peminjaman LIKE '$column'");
    if (!$check) {
        die('Error checking columns: ' . mysqli_error($conn));
    }
    if (mysqli_num_rows($check) === 0) {
        $alter = mysqli_query($conn, "ALTER TABLE peminjaman ADD COLUMN $definition");
        if (!$alter) {
            die('Error adding column ' . $column . ': ' . mysqli_error($conn));
        }
        echo "Kolom '$column' berhasil ditambahkan.\n";
    } else {
        echo "Kolom '$column' sudah ada.\n";
    }
}

echo "Migrasi selesai.\n";
