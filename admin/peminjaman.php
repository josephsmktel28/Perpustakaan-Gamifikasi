<?php
include '../config/koneksi.php';

$data = mysqli_query($conn,"
SELECT peminjaman.*, users.username, buku.judul
FROM peminjaman
JOIN users ON peminjaman.id_user = users.id
JOIN buku ON peminjaman.id_buku = buku.id_buku
");
?>

<!DOCTYPE html>
<html>

<head>

<title>Data Peminjaman</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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

<div class="main-panel">
    <div class="admin-header">
        <div>
            <h2 class="page-title">Data Peminjaman</h2>
            <p class="admin-panel-title">Lihat riwayat peminjaman dan status pengembalian.</p>
        </div>
    </div>

    <div class="glass-table-card">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="dashboard.php" class="btn btn-secondary">⬅ Dashboard</a>
        </div>
        <?php if(isset($_GET['updated']) && $_GET['updated'] == 1){ ?>
            <div class="alert alert-success">Perubahan peminjaman berhasil disimpan.</div>
        <?php } ?>

        <table class="table table-admin">
            <tr>
                <th>ID</th>
                <th>Nama User</th>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Batas Kembali</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
                <th>Denda</th>
                <th>Pelanggaran</th>
                <th>Aksi</th>
            </tr>

            <?php while($d=mysqli_fetch_assoc($data)){ ?>

            <tr>

<td><?php echo $d['id_pinjam']; ?></td>

<td><?php echo $d['username']; ?></td>

<td><?php echo $d['judul']; ?></td>

<td><?php echo $d['tanggal_pinjam']; ?></td>

<td><?php echo $d['tanggal_deadline'] ? $d['tanggal_deadline'] : '-'; ?></td>

<td><?php echo $d['tanggal_kembali'] ? $d['tanggal_kembali'] : '-'; ?></td>

<td><?php echo $d['status']; ?></td>

<td><?php echo $d['denda']; ?></td>

<td><?php echo $d['pelanggaran'] ? $d['pelanggaran'] : '-'; ?></td>
                <td>
                    <a href="edit_peminjaman.php?id=<?php echo $d['id_pinjam']; ?>" class="btn btn-sm btn-primary">Edit</a>
                </td>
            </tr>

            <?php } ?>

        </table>

</div>

</div>

</body>
</html>