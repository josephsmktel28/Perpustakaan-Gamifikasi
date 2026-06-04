<?php
include __DIR__ . '/../config/koneksi.php';

$tables = [
    'gacha_rewards' => "CREATE TABLE gacha_rewards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_hadiah VARCHAR(100) NOT NULL,
        deskripsi TEXT NOT NULL,
        reward_points INT DEFAULT 0,
        chance INT DEFAULT 10,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'gacha_history' => "CREATE TABLE gacha_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_user INT NOT NULL,
        id_reward INT NOT NULL,
        awarded_points INT DEFAULT 0,
        cost_points INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach($tables as $table => $ddl){
    $exists = mysqli_query($conn, "SHOW TABLES LIKE '$table'");
    if(!$exists){
        die('Error checking table ' . $table . ': ' . mysqli_error($conn));
    }
    if(mysqli_num_rows($exists) === 0){
        $create = mysqli_query($conn, $ddl);
        if(!$create){
            die('Error creating table ' . $table . ': ' . mysqli_error($conn));
        }
        echo "Tabel '$table' berhasil dibuat.\n";
    } else {
        echo "Tabel '$table' sudah ada.\n";
    }
}

echo "Migrasi gacha selesai.\n";
