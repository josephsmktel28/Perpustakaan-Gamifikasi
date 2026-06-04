
<?php
include '../config/koneksi.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $judul = mysqli_real_escape_string($conn, trim($_POST['judul']));
    $penulis = mysqli_real_escape_string($conn, trim($_POST['penulis']));
    $stok = intval($_POST['stok']);
    $kategori = mysqli_real_escape_string($conn, trim($_POST['kategori']));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi']));
    $cover_url = '';

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

    if($judul !== '' && $penulis !== '' && $stok > 0){
        $cek = mysqli_query($conn, "SELECT * FROM buku WHERE judul='$judul' AND penulis='$penulis'");
        if(mysqli_num_rows($cek) === 0){
            mysqli_query($conn, "INSERT INTO buku (judul, penulis, stok, kategori, cover_url, deskripsi) VALUES('$judul','$penulis','$stok','$kategori','$cover_url','$deskripsi')");
        }
    }

    header("Location: buku.php");
    exit;
}
?>

<form method="POST" enctype="multipart/form-data">
<input name="judul" placeholder="Judul"><br>
<input name="penulis" placeholder="Penulis"><br>
<input type="file" name="cover_image"><br>
<input type="number" name="stok" placeholder="Stok"><br>
<input type="hidden" name="kategori" value="Fiksi">
<input type="hidden" name="deskripsi" value="">
<button type="submit" name="simpan">Simpan</button>
</form>
