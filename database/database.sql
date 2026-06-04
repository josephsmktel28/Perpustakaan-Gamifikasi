
CREATE DATABASE perpustakaan_gamifikasi;
USE perpustakaan_gamifikasi;

CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(50),
password VARCHAR(50),
role ENUM('admin','user'),
points INT DEFAULT 0,
level_member VARCHAR(50) DEFAULT 'Beginner'
);

CREATE TABLE buku (
id_buku INT AUTO_INCREMENT PRIMARY KEY,
judul VARCHAR(100),
penulis VARCHAR(100),
stok INT,
kategori VARCHAR(50) DEFAULT 'Fiksi',
cover_url VARCHAR(255) DEFAULT NULL,
deskripsi TEXT DEFAULT NULL
);

CREATE TABLE peminjaman (
id_pinjam INT AUTO_INCREMENT PRIMARY KEY,
id_user INT,
id_buku INT,
tanggal_pinjam DATE,
tanggal_deadline DATE DEFAULT NULL,
tanggal_kembali DATE,
    status VARCHAR(50),
    denda INT DEFAULT 0,
    pelanggaran VARCHAR(100) DEFAULT NULL,
    review TEXT DEFAULT NULL,
    rating TINYINT DEFAULT NULL
);

CREATE TABLE badge (
    id_badge INT AUTO_INCREMENT PRIMARY KEY,
    nama_badge VARCHAR(100),
    deskripsi TEXT,
    syarat_point INT
);

CREATE TABLE user_badge (
id INT AUTO_INCREMENT PRIMARY KEY,
id_user INT,
id_badge INT
);

INSERT INTO users VALUES
(1,'admin','admin','admin',0,'Beginner'),
(2,'user','user','user',0,'Beginner');

INSERT INTO badge VALUES
(1,'Reader Pemula','Meminjam buku pertama',10),
(2,'Book Lover','Meminjam 5 buku',50),
(3,'Master Reader','Meminjam 20 buku',200);

-- Tabel untuk Challenges/Missions
CREATE TABLE challenges (
    id_challenge INT AUTO_INCREMENT PRIMARY KEY,
    nama_challenge VARCHAR(100),
    deskripsi TEXT,
    syarat_type VARCHAR(50), -- e.g., 'pinjam_buku', 'return_buku'
    syarat_value INT, -- e.g., jumlah buku
    reward_points INT DEFAULT 0,
    reward_badge INT NULL -- id_badge jika ada
);

-- Tabel untuk progress user pada challenges
CREATE TABLE user_challenges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_challenge INT,
    progress INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    completed_date DATE NULL
);

-- Tabel untuk Streak system
CREATE TABLE streaks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    streak_type VARCHAR(50), -- e.g., 'daily_pinjam', 'daily_return'
    current_streak INT DEFAULT 0,
    last_date DATE NULL
);

-- Tabel untuk Gacha Roulette
CREATE TABLE gacha_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_hadiah VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL,
    reward_points INT DEFAULT 0,
    chance INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE gacha_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_reward INT NOT NULL,
    awarded_points INT DEFAULT 0,
    cost_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample challenges
INSERT INTO challenges (nama_challenge, deskripsi, syarat_type, syarat_value, reward_points) VALUES
('First Borrow', 'Pinjam buku pertama Anda', 'pinjam_buku', 1, 10),
('Book Enthusiast', 'Pinjam 5 buku', 'pinjam_buku', 5, 50),
('Return Champion', 'Kembalikan 3 buku', 'return_buku', 3, 30),
('Daily Reader', 'Pinjam buku setiap hari selama 7 hari', 'daily_pinjam', 7, 100);
