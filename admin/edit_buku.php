<?php
include '../config/koneksi.php';

$id = $_GET['id'];

$data = mysqli_query($conn,"SELECT * FROM buku WHERE id_buku='$id'");
$d = mysqli_fetch_array($data);
?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Buku</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css">
</head>

<body class="admin-page">

<div class="admin-layout">
    <div class="sidebar">

        <h4>📚 Admin Panel</h4>

        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="buku.php">📚 Kelola Buku</a>
        <a href="users.php">👥 Kelola User</a>
        <a href="peminjaman.php">📖 Data Pinjam</a>
        <a href="review.php">📝 Review User</a>
        <a href="challenges.php">🎯 Challenges</a>
<a href="gacha.php">🎡 Gacha Rewards</a>

    <div class="main-panel">

        <div class="admin-header">
            <div>
                <h2 class="page-title">Edit Buku</h2>
                <p class="admin-panel-title">Perbarui data buku dan cover dengan tampilan modern.</p>
            </div>
        </div>

        <div class="form-card">

<h3>Edit Buku</h3>

<form method="POST" action="update_buku.php" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?= $d['id_buku'] ?>">

<input type="text" name="judul" class="form-control mb-2" value="<?= $d['judul'] ?>">

<input type="text" name="penulis" class="form-control mb-2" value="<?= $d['penulis'] ?>">

<select name="kategori" class="form-select mb-2" required>
    <option value="Fiksi" <?= ($d['kategori'] === 'Fiksi') ? 'selected' : '' ?>>Fiksi</option>
    <option value="Non-Fiksi" <?= ($d['kategori'] === 'Non-Fiksi') ? 'selected' : '' ?>>Non-Fiksi</option>
</select>

<?php if(!empty($d['cover_url'])): ?>
<p class="text-muted">Cover saat ini: <?= htmlspecialchars($d['cover_url']) ?></p>
<?php endif; ?>

<input type="hidden" name="existing_cover_url" value="<?= htmlspecialchars($d['cover_url']) ?>">
<input type="file" name="cover_image" class="form-control mb-2" accept="image/*">

<textarea name="deskripsi" class="form-control mb-2" rows="4" placeholder="Deskripsi buku (opsional)"><?= $d['deskripsi'] ?></textarea>

<input type="number" name="stok" class="form-control mb-2" value="<?= $d['stok'] ?>">

<button class="btn btn-primary">Update</button>

<a href="buku.php" class="btn btn-secondary">Kembali</a>

</form>

        </div>

    </div>

</div>

</body>
</html>