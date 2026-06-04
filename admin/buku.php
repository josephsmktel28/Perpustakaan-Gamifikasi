<?php
include '../config/koneksi.php';
$data = mysqli_query($conn,"SELECT * FROM buku");
?>

<!DOCTYPE html>
<html>

<head>

<title>Data Buku</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<div class="main-panel">

<div class="admin-header">
    <div>
        <h2 class="page-title">Data Buku</h2>
        <p class="admin-panel-title">Kelola katalog buku dengan mudah.</p>
    </div>
</div>

<div class="d-flex flex-wrap gap-2 align-items-center mb-4">

<a href="../admin/dashboard.php" class="btn btn-secondary">
⬅ Back Dashboard
</a>

<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambah">
➕ Tambah Buku
</button>

</div>

<div class="glass-table-card">
<table class="table table-admin">

<thead>

<tr>

<th>ID</th>
<th>Judul</th>
<th>Penulis</th>
<th>Kategori</th>
<th>Stok</th>
<th width="150">Aksi</th>

</tr>

</thead>

<tbody>

<?php while($d=mysqli_fetch_array($data)){ ?>

<tr>

<td><?= $d['id_buku'] ?></td>
<td><?= $d['judul'] ?></td>
<td><?= $d['penulis'] ?></td>
<td><?= $d['kategori'] ?></td>
<td><?= $d['stok'] ?></td>

<td>

<a href="edit_buku.php?id=<?= $d['id_buku'] ?>" class="btn btn-warning btn-sm">

✏ Edit

</a>

<a href="hapus_buku.php?id=<?= $d['id_buku'] ?>" 
class="btn btn-danger btn-sm"
onclick="return confirm('Yakin hapus buku ini?')">

🗑 Delete

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>
</div>
</div>

<!-- MODAL TAMBAH BUKU -->

<div class="modal fade" id="tambah">

<div class="modal-dialog">

<div class="modal-content">

<div class="modal-header">

<h5 class="modal-title">Tambah Buku</h5>

<button class="btn-close" data-bs-dismiss="modal"></button>

</div>

<form method="POST" action="tambah_buku.php" enctype="multipart/form-data">

<div class="modal-body">

<input type="text" name="judul" class="form-control mb-2" placeholder="Judul Buku" required>

<input type="text" name="penulis" class="form-control mb-2" placeholder="Penulis" required>

<select name="kategori" class="form-select mb-2" required>
    <option value="Fiksi">Fiksi</option>
    <option value="Non-Fiksi">Non-Fiksi</option>
</select>

<input type="file" name="cover_image" class="form-control mb-2" accept="image/*">

<textarea name="deskripsi" class="form-control mb-2" rows="3" placeholder="Deskripsi buku (opsional)"></textarea>

<input type="number" name="stok" class="form-control mb-2" placeholder="Stok" required>

</div>

<div class="modal-footer">

<button type="submit" name="simpan" class="btn btn-success">Simpan</button>

</div>

</form>

</div>

</div>

</div>

</body>
</html>