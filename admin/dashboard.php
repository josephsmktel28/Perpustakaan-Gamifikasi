<?php
session_start();
include '../config/koneksi.php';

# TOTAL BUKU
$buku = mysqli_query($conn,"SELECT COUNT(*) as total FROM buku");
$total_buku = mysqli_fetch_assoc($buku)['total'];

# TOTAL USER
$user = mysqli_query($conn,"SELECT COUNT(*) as total FROM users");
$total_user = mysqli_fetch_assoc($user)['total'];

# TOTAL PINJAM
$pinjam = mysqli_query($conn,"SELECT COUNT(*) as total FROM peminjaman");
$total_pinjam = mysqli_fetch_assoc($pinjam)['total'];

# TOP USER
$top = mysqli_query($conn,"SELECT username,points FROM users ORDER BY points DESC LIMIT 5");

?>

<!DOCTYPE html>
<html>

<head>

<title>Admin Dashboard</title>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/admin.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

<a href="review.php">📖 Review</a>

<a href="challenges.php">🎯 Challenges</a>

<a href="gacha.php">🎡 Gacha Rewards</a>

<a href="../auth/logout.php">🚪 Logout</a>

</div>


<!-- CONTENT -->

<div class="main-panel">

<div class="admin-header">
    <div>
        <h2 class="page-title">Dashboard Admin</h2>
        <p class="admin-panel-title">Ringkasan metrik perpustakaan dan aktivitas pengguna.</p>
    </div>
</div>

<div class="cards-grid">

<div class="metric-card">
    <div>
        <p class="metric-title">Total Buku</p>
        <h2 class="metric-value"><?php echo $total_buku ?></h2>
    </div>
    <div class="metric-icon">📚</div>
</div>

<div class="metric-card">
    <div>
        <p class="metric-title">Total User</p>
        <h2 class="metric-value"><?php echo $total_user ?></h2>
    </div>
    <div class="metric-icon">👥</div>
</div>

<div class="metric-card">
    <div>
        <p class="metric-title">Total Peminjaman</p>
        <h2 class="metric-value"><?php echo $total_pinjam ?></h2>
    </div>
    <div class="metric-icon">📖</div>
</div>

</div>


<div class="row mt-4">

<div class="col-md-8">

<div class="glass-card p-3">

<h5>📊 Statistik Aktivitas</h5>

<canvas id="chart"></canvas>

</div>

</div>


<div class="col-md-4">

<div class="glass-table-card p-3">

<h5>🏆 Top User</h5>

<table class="table table-admin">

<tr>

<th>User</th>
<th>Points</th>

</tr>

<?php while($t=mysqli_fetch_assoc($top)){ ?>

<tr>

<td><?php echo $t['username'] ?></td>

<td><?php echo $t['points'] ?></td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</div>

</div>


<script>

const ctx=document.getElementById('chart');

new Chart(ctx,{

type:'bar',

data:{

labels:['Buku','User','Peminjaman'],

datasets:[{

label:'Statistik Sistem',

data:[
<?php echo $total_buku ?>,
<?php echo $total_user ?>,
<?php echo $total_pinjam ?>
],

backgroundColor:['blue','green','orange']

}]

}

});

</script>

</body>

</html>