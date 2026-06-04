<?php
session_start();
include '../config/koneksi.php';

$query = mysqli_query($conn,"SELECT * FROM users ORDER BY points DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
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
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i>Dashboard</a>
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
                    <a class="nav-link active" href="leaderboard.php"><i class="fas fa-chart-line me-1"></i>Leaderboard</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo $_SESSION['users']['username']; ?>
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
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 fw-bold text-warning mb-2">
                        <i class="fas fa-trophy me-3"></i>Leaderboard
                    </h1>
                    <p class="lead text-muted">Lihat bagaimana peringkat Anda di antara pembaca top kami!</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Leaderboard Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="fas fa-crown me-2"></i>Top Readers</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-warning">
                                <tr>
                                    <th class="text-center" style="width: 80px;">Peringkat</th>
                                    <th>Pembaca</th>
                                    <th class="text-center">Poin</th>
                                    <th class="text-center">Badge</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $rank = 1;
                                while($d = mysqli_fetch_assoc($query)){
                                    $badge = '';
                                    if($rank == 1) $badge = '<i class="fas fa-crown text-warning"></i> Champion';
                                    elseif($rank == 2) $badge = '<i class="fas fa-medal text-secondary"></i> Runner-up';
                                    elseif($rank == 3) $badge = '<i class="fas fa-award text-warning"></i> Third Place';
                                    elseif($d['points'] >= 200) $badge = '<i class="fas fa-star text-primary"></i> Master';
                                    elseif($d['points'] >= 100) $badge = '<i class="fas fa-star text-info"></i> Expert';
                                    elseif($d['points'] >= 50) $badge = '<i class="fas fa-star text-success"></i> Reader';
                                    else $badge = '<i class="fas fa-seedling text-muted"></i> Beginner';
                                ?>
                                <tr class="<?php echo ($d['username'] == $_SESSION['users']['username']) ? 'table-primary' : ''; ?>">
                                    <td class="text-center fw-bold">
                                        <?php
                                        if($rank == 1) echo '<span class="badge bg-warning text-dark fs-6">🥇</span>';
                                        elseif($rank == 2) echo '<span class="badge bg-secondary fs-6">🥈</span>';
                                        elseif($rank == 3) echo '<span class="badge bg-warning fs-6">🥉</span>';
                                        else echo '<span class="text-muted">' . $rank . '</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-primary text-white me-3" style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                                <?php echo strtoupper(substr($d['username'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo $d['username']; ?></strong>
                                                <?php if($d['username'] == $_SESSION['users']['username']) echo '<small class="text-primary ms-2">(You)</small>'; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success fs-6"><?php echo $d['points']; ?> pts</span>
                                    </td>
                                    <td class="text-center">
                                        <?php echo $badge; ?>
                                    </td>
                                </tr>
                                <?php
                                $rank++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="row mt-4">
        <div class="col-12 text-center">
            <a href="dashboard.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>