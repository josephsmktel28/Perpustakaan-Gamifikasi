
<?php
session_start();
include '../config/koneksi.php';

$username = $_POST['username'];
$password = $_POST['password'];

$q = mysqli_query($conn,"SELECT * FROM users WHERE username='$username' AND password='$password'");
$data = mysqli_fetch_assoc($q);

if($data){
$_SESSION['users'] = $data;

// Reset user challenges - clear all progress untuk fresh start
$user_id = $data['id'];
mysqli_query($conn,"DELETE FROM user_challenges WHERE id_user='$user_id'");

if($data['role']=='admin'){
header("location:../admin/dashboard.php");
}else{
header("location:../user/dashboard.php");
}

}else{
echo "Login gagal";
}
?>
