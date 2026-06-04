<?php
session_start();
include '../config/koneksi.php';

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('Location: register.php');
    exit;
}

$username = trim(mysqli_real_escape_string($conn, $_POST['username'] ?? ''));
$password = trim(mysqli_real_escape_string($conn, $_POST['password'] ?? ''));
$confirm_password = trim(mysqli_real_escape_string($conn, $_POST['confirm_password'] ?? ''));

if(empty($username) || empty($password) || empty($confirm_password)){
    header('Location: register.php?error=' . rawurlencode('Semua field harus diisi'));
    exit;
}

if($password !== $confirm_password){
    header('Location: register.php?error=' . rawurlencode('Password dan konfirmasi tidak cocok'));
    exit;
}

$existing = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
if(mysqli_num_rows($existing) > 0){
    header('Location: register.php?error=' . rawurlencode('Username sudah terdaftar'));
    exit;
}

$insert = mysqli_query($conn, "INSERT INTO users (username, password, role, points, level_member) VALUES ('$username', '$password', 'user', 0, 'Beginner')");
if(!$insert){
    header('Location: register.php?error=' . rawurlencode('Terjadi kesalahan registrasi'));
    exit;
}

header('Location: login.php?success=1');
exit;
