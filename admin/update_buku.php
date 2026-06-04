<?php

include '../config/koneksi.php';

$id = $_POST['id'];
$judul = mysqli_real_escape_string($conn, trim($_POST['judul']));
$penulis = mysqli_real_escape_string($conn, trim($_POST['penulis']));
$stok = intval($_POST['stok']);
$kategori = mysqli_real_escape_string($conn, trim($_POST['kategori']));
$deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
$cover_url = mysqli_real_escape_string($conn, trim($_POST['existing_cover_url']));

if(isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK){
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $fileType = mime_content_type($_FILES['cover_image']['tmp_name']);
    if(in_array($fileType, $allowedTypes)){
        $uploadDir = '../assets/uploads/covers/';
        if(!is_dir($uploadDir)){
            mkdir($uploadDir, 0755, true);
        }
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['cover_image']['name']));
        $targetFile = $uploadDir . $filename;
        if(move_uploaded_file($_FILES['cover_image']['tmp_name'], $targetFile)){
            $cover_url = 'assets/uploads/covers/' . $filename;
        }
    }
}

mysqli_query($conn,"UPDATE buku SET
judul='$judul',
penulis='$penulis',
stok='$stok',
kategori='$kategori',
cover_url='$cover_url',
deskripsi='$deskripsi'
WHERE id_buku='$id'");

header("location:buku.php");

?>