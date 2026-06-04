<?php
$c = mysqli_connect('localhost', 'root', '', 'perpustakaan_gamifikasi');
if(!$c){ echo 'conn fail'; exit(1); }
$r = mysqli_query($c, 'SELECT id_buku, judul, cover_url FROM buku');
if(!$r){ echo 'query fail: '.mysqli_error($c); exit(1); }
while($row = mysqli_fetch_assoc($r)){
    echo $row['id_buku'].'|'.$row['judul'].'|'.$row['cover_url']."\n";
}
