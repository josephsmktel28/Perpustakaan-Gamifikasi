<?php
include '../config/koneksi.php';

$columns = [
    'kategori' => "kategori VARCHAR(50) DEFAULT 'Fiksi'",
    'cover_url' => "cover_url VARCHAR(255) DEFAULT NULL",
    'deskripsi' => "deskripsi TEXT DEFAULT NULL",
];

foreach ($columns as $column => $definition) {
    $check = mysqli_query($conn, "SHOW COLUMNS FROM buku LIKE '$column'");
    if (!$check) {
        die('Error checking columns: ' . mysqli_error($conn));
    }
    if (mysqli_num_rows($check) === 0) {
        $alter = mysqli_query($conn, "ALTER TABLE buku ADD COLUMN $definition");
        if (!$alter) {
            die('Error adding column ' . $column . ': ' . mysqli_error($conn));
        }
        echo "Kolom '$column' berhasil ditambahkan.\n";
    } else {
        echo "Kolom '$column' sudah ada.\n";
    }
}

echo "Migrasi selesai.\n";
