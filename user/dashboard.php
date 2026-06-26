<?php
session_start();
include '../config/koneksi.php';

$user = $_SESSION['users'];
$id = $user['id'];

$query = mysqli_query($conn,"SELECT * FROM users WHERE id='$id'");
$data = mysqli_fetch_assoc($query);

$points = $data['points'];

# DEFAULT LEVEL
$level = "Beginner";

# LEVEL SYSTEM
if($points >= 200){
    $level = "🏆 Master Reader";
}elseif($points >= 100){
    $level = "🥇 Book Lover";
}elseif($points >= 50){
    $level = "🥈 Intermediate";
}else{
    $level = "🥉 Beginner";
}

# PROGRESS BAR
$next = 200;
$progress = ($points/$next)*100;

if($progress > 100){
    $progress = 100;
}

# 🔥 TAMBAHAN QUERY REVIEW
$review = mysqli_query($conn,"
SELECT p.review, p.rating, p.tanggal_kembali, p.status,
       b.judul, u.username AS nama
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
JOIN users u ON p.id_user = u.id
WHERE p.status IN ('kembali', 'kembali terlambat')
AND p.review IS NOT NULL
ORDER BY p.id_pinjam DESC
LIMIT 6
");
$review_error = null;
if($review === false){
    $review_error = mysqli_error($conn);
}

# Query untuk Challenges
$challenges = mysqli_query($conn,"
SELECT c.nama_challenge, c.deskripsi, c.syarat_value, c.reward_points, 
       uc.progress, uc.completed
FROM challenges c
LEFT JOIN user_challenges uc ON c.id_challenge = uc.id_challenge AND uc.id_user = '$id'
");

# Query untuk Streak
$streak_query = mysqli_query($conn,"SELECT * FROM streaks WHERE id_user='$id' AND streak_type='daily_pinjam'");
$streak_data = mysqli_fetch_assoc($streak_query);
$current_streak = $streak_data ? $streak_data['current_streak'] : 0;

# Query untuk Chart - Statistik Aktivitas
$pinjam_count = mysqli_query($conn,"SELECT COUNT(*) as total FROM peminjaman WHERE id_user='$id' AND status='dipinjam'");
$pinjam_data = mysqli_fetch_assoc($pinjam_count);
$total_pinjam = $pinjam_data['total'];

$return_count = mysqli_query($conn,"SELECT COUNT(*) as total FROM peminjaman WHERE id_user='$id' AND status='kembali'");
$return_data = mysqli_fetch_assoc($return_count);
$total_return = $return_data['total'];
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<!-- Navigation Header -->
<nav class="navbar navbar-expand-lg navbar-light navbar-site sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="fas fa-book-open me-2"></i>Perpustakaan Digital
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="catalog.php"><i class="fas fa-book-open me-1"></i>Catalog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pinjam.php"><i class="fas fa-plus me-1"></i>Pinjam Buku</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="peminjaman.php"><i class="fas fa-history me-1"></i>Riwayat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="badge.php"><i class="fas fa-trophy me-1"></i>Badge</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gacha.php"><i class="fas fa-dice me-1"></i>Gacha</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="leaderboard.php"><i class="fas fa-chart-line me-1"></i>Leaderboard</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo $data['username']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 fw-bold text-primary mb-2">
                        <i class="fas fa-school me-3"></i>Selamat Datang di Perpustakaan Digital
                    </h1>
                    <p class="lead text-muted">Jelajahi, pinjam, dan tingkatkan perjalanan membaca Anda dengan gamifikasi!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow text-center h-100">
                <div class="card-body">
                    <div class="display-4 text-warning mb-2">
                        <i class="fas fa-star"></i>
                    </div>
                    <h5 class="card-title">Poin</h5>
                    <h2 class="text-warning fw-bold"><?php echo $points ?></h2>
                    <p class="text-muted">Poin membaca Anda</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow text-center h-100">
                <div class="card-body">
                    <div class="display-4 text-info mb-2">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h5 class="card-title">Level</h5>
                    <h2 class="text-info fw-bold"><?php echo $level ?></h2>
                    <p class="text-muted">Level Anda saat ini</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow text-center h-100">
                <div class="card-body">
                    <div class="display-4 text-success mb-2">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="card-title">Progress</h5>
                    <div class="progress mb-2" style="height: 20px;">
                        <div class="progress-bar bg-success" style="width:<?php echo $progress ?>%">
                            <?php echo round($progress) ?>%
                        </div>
                    </div>
                    <p class="text-muted">Progress level selanjutnya</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <a href="pinjam.php" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-plus-circle fa-2x d-block mb-2"></i>
                                <span>Pinjam Buku</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="return.php" class="btn btn-success w-100 py-3">
                                <i class="fas fa-undo fa-2x d-block mb-2"></i>
                                <span>Kembalikan Buku</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="peminjaman.php" class="btn btn-info w-100 py-3">
                                <i class="fas fa-history fa-2x d-block mb-2"></i>
                                <span>Riwayat Peminjaman</span>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="gacha.php" class="btn btn-warning w-100 py-3">
                                <i class="fas fa-dice fa-2x d-block mb-2"></i>
                                <span>Gacha</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Chart -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Statistik Aktivitas</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartPinjam" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Streak Membaca</h5>
                </div>
                <div class="card-body text-center">
                    <div class="display-3 text-warning mb-3">
                        <i class="fas fa-fire"></i>
                    </div>
                    <h3><?php echo $current_streak; ?> Hari</h3>
                    <p class="text-muted">Pertahankan pinjam buku harian untuk mempertahankan streak!</p>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-warning" style="width: <?php echo min($current_streak * 10, 100); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Review Buku Terbaru</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if($review && mysqli_num_rows($review) > 0){ ?>
                            <?php while($r = mysqli_fetch_assoc($review)){ ?>
                            <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h6 class="card-title text-primary fw-bold">
                                        <i class="fas fa-book me-2"></i><?php echo $r['judul']; ?>
                                    </h6>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i><?php echo $r['nama']; ?> • 
                                            <i class="fas fa-calendar me-1"></i><?php echo date('M d, Y', strtotime($r['tanggal_kembali'])); ?>
                                        </small>
                                    </div>
                                    <div class="mb-2">
                                        <?php for($i=1; $i<=5; $i++){ ?>
                                            <i class="fas fa-star <?php echo $i <= $r['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                        <?php } ?>
                                    </div>
                                    <?php if($r['status'] === 'kembali terlambat'){ ?>
                                        <div class="mb-2">
                                            <span class="badge bg-danger">Terlambat</span>
                                        </div>
                                    <?php } ?>
                                    <p class="card-text">"<?php echo $r['review']; ?>"</p>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                        <?php } else { ?>
                            <div class="col-12 text-center py-5 text-muted">
                                <p class="mb-2">Belum ada review buku yang ditampilkan.</p>
                                <?php if($review_error){ ?>
                                    <small class="text-danger">Error query review: <?php echo htmlspecialchars($review_error); ?></small>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Challenges -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Tantangan Aktif</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php while($c = mysqli_fetch_assoc($challenges)){ ?>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 <?php echo $c['completed'] ? 'border-success' : 'border-warning'; ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="card-title fw-bold"><?php echo $c['nama_challenge']; ?></h6>
                                        <?php if($c['completed']){ ?>
                                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>Completed</span>
                                        <?php } else { ?>
                                            <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>In Progress</span>
                                        <?php } ?>
                                    </div>
                                    <p class="card-text text-muted"><?php echo $c['deskripsi']; ?></p>
                                    <div class="mb-2">
                                        <small class="text-muted">Progress: <?php echo $c['progress'] ?? 0; ?>/<?php echo $c['syarat_value']; ?></small>
                                        <div class="progress mt-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" style="width: <?php echo (($c['progress'] ?? 0) / $c['syarat_value']) * 100; ?>%"></div>
                                        </div>
                                    </div>
                                    <small class="text-success fw-bold">Reward: <?php echo $c['reward_points']; ?> points</small>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const ctx = document.getElementById('chartPinjam');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Pinjam Buku', 'Kemballikan Buku'],
        datasets: [{
            label: 'Activity',
            data: [<?php echo $total_pinjam; ?>, <?php echo $total_return; ?>],
            backgroundColor: ['#007bff', '#28a745'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>

</body>
</html>