<?php
session_start();
include '../config/koneksi.php';

# 🔥 PROSES HAPUS REVIEW
if(isset($_GET['hapus'])){
    $id = $_GET['hapus'];

    mysqli_query($conn,"UPDATE peminjaman 
    SET review=NULL, rating=NULL 
    WHERE id_pinjam='$id'");

    echo "<script>alert('Review berhasil dihapus');window.location='review.php';</script>";
}

# AMBIL DATA REVIEW
$review = mysqli_query($conn,"
SELECT p.id_pinjam, p.review, p.rating, p.tanggal_kembali, 
       b.judul, u.username 
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
JOIN users u ON p.id_user = u.id
WHERE p.status='kembali' 
AND p.review IS NOT NULL
ORDER BY p.id_pinjam DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Data Review</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/admin.css">

</head>

<body class="admin-page">

<div class="admin-layout">

<!-- SIDEBAR -->
<div class="sidebar">

<h4>📚 Admin Panel</h4>

<a href="dashboard.php">🏠 Dashboard</a>
<a href="buku.php">📚 Kelola Buku</a>
<a href="users.php">👥 Kelola User</a>
<a href="peminjaman.php">📖 Data Pinjam</a>
<a href="review.php">📝 Review User</a>
<a href="challenges.php">🎯 Challenges</a>
<a href="gacha.php">🎡 Gacha Rewards</a>
<a href="../auth/logout.php">🚪 Logout</a>

</div>

<!-- CONTENT -->
<div class="main-panel">
    <div class="admin-header">
        <div>
            <h2 class="page-title">Review User</h2>
            <p class="admin-panel-title">Ulasan buku terbaru dari pengguna.</p>
        </div>
    </div>

    <div class="row">

<?php while($r = mysqli_fetch_assoc($review)){ ?>

<div class="col-md-4">

<div class="review-card">

<h6 class="text-primary fw-bold">
<?php echo $r['judul']; ?>
</h6>

<small class="text-muted">
👤 <?php echo $r['username']; ?><br>
📅 <?php echo $r['tanggal_kembali']; ?>
</small>

<br>

<!-- ⭐ RATING -->
<p>
<?php
for($i=1; $i<=5; $i++){
    echo ($i <= $r['rating']) ? "⭐" : "☆";
}
?>
</p>

<!-- 📝 REVIEW -->
<p>
"<?php echo $r['review']; ?>"
</p>

<!-- 🔥 BUTTON HAPUS -->
<a href="review.php?hapus=<?php echo $r['id_pinjam']; ?>" 
   class="btn btn-danger btn-sm"
   onclick="return confirm('Yakin ingin menghapus review ini?')">
   🗑 Hapus
</a>

</div>

</div>

<?php } ?>

    </div>

</div>

</div>

</body>
</html>