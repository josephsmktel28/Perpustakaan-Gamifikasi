<?php
include '../config/koneksi.php';

$data = mysqli_query($conn,"SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>

<title>Kelola User</title>

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
        <h2 class="page-title">Kelola User</h2>
        <p class="admin-panel-title">Daftar pengguna dan status point.</p>
    </div>
</div>

<div class="glass-table-card">

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="dashboard.php" class="btn btn-secondary">⬅ Dashboard</a>
</div>

<table class="table table-admin">

<tr>

<th>ID</th>
<th>Username</th>
<th>Points</th>
<th>Role</th>

</tr>

<?php while($d=mysqli_fetch_assoc($data)){ ?>

<tr>

<td><?php echo $d['id'] ?></td>
<td><?php echo $d['username'] ?></td>
<td><?php echo $d['points'] ?></td>
<td><?php echo $d['role'] ?></td>

</tr>

<?php } ?>

</table>

</div>

    </div>

</div>

</body>
</html>