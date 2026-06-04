<?php
session_start();
include '../config/koneksi.php';

$user = $_SESSION['users'];
$id_user = $user['id'];

$query = mysqli_query($conn,"SELECT * FROM users WHERE id='$id_user'");
$data = mysqli_fetch_assoc($query);

$points = $data['points'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Badge Saya - Perpustakaan Digital</title>
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
                    <a class="nav-link active" href="badge.php"><i class="fas fa-trophy me-1"></i>Badge</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="leaderboard.php"><i class="fas fa-chart-line me-1"></i>Leaderboard</a>
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

<div class="container-fluid" style="padding: 0;">
    <div class="container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 fw-bold text-warning mb-2">
                        <i class="fas fa-trophy me-3"></i>Badge Pencapaian Saya
                    </h1>
                    <p class="lead text-muted">Lacak pencapaian membaca Anda dan buka badge baru!</p>
                    <div class="mt-3">
                        <span class="badge bg-primary fs-5 px-3 py-2">
                            <i class="fas fa-star me-2"></i><?php echo $points; ?> Points Earned
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Badges Grid -->
    <div class="row g-4">
        <!-- Beginner Reader -->
        <div class="col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow <?php echo $points >= 10 ? 'border-success' : 'border-secondary'; ?>">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <div class="display-1 <?php echo $points >= 10 ? 'text-success' : 'text-muted'; ?>">
                            <i class="fas fa-seedling"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold">Pembaca Pemula</h5>
                    <p class="card-text text-muted">Mulai perjalanan membaca Anda</p>
                    <div class="mt-auto">
                        <span class="badge bg-light text-dark mb-2">10 Points Required</span>
                        <?php if($points >= 10){ ?>
                            <span class="badge bg-success fs-6"><i class="fas fa-unlock me-1"></i>Unlocked</span>
                        <?php } else { ?>
                            <span class="badge bg-secondary fs-6"><i class="fas fa-lock me-1"></i>Locked</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Intermediate Reader -->
        <div class="col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow <?php echo $points >= 50 ? 'border-info' : 'border-secondary'; ?>">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <div class="display-1 <?php echo $points >= 50 ? 'text-info' : 'text-muted'; ?>">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold">Pencinta Buku</h5>
                    <p class="card-text text-muted">Tunjukkan dedikasi Anda terhadap membaca</p>
                    <div class="mt-auto">
                        <span class="badge bg-light text-dark mb-2">50 Points Required</span>
                        <?php if($points >= 50){ ?>
                            <span class="badge bg-success fs-6"><i class="fas fa-unlock me-1"></i>Unlocked</span>
                        <?php } else { ?>
                            <span class="badge bg-secondary fs-6"><i class="fas fa-lock me-1"></i>Locked</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Reader -->
        <div class="col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow <?php echo $points >= 100 ? 'border-warning' : 'border-secondary'; ?>">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <div class="display-1 <?php echo $points >= 100 ? 'text-warning' : 'text-muted'; ?>">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold">Pembaca Rajin</h5>
                    <p class="card-text text-muted">Anda mulai menjadi ahli membaca</p>
                    <div class="mt-auto">
                        <span class="badge bg-light text-dark mb-2">100 Points Required</span>
                        <?php if($points >= 100){ ?>
                            <span class="badge bg-success fs-6"><i class="fas fa-unlock me-1"></i>Unlocked</span>
                        <?php } else { ?>
                            <span class="badge bg-secondary fs-6"><i class="fas fa-lock me-1"></i>Locked</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Master Reader -->
        <div class="col-md-6 col-lg-3">
            <div class="card text-center h-100 shadow <?php echo $points >= 200 ? 'border-danger' : 'border-secondary'; ?>">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <div class="display-1 <?php echo $points >= 200 ? 'text-danger' : 'text-muted'; ?>">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                    <h5 class="card-title fw-bold">Master Reader</h5>
                    <p class="card-text text-muted">Juara membaca utama</p>
                    <div class="mt-auto">
                        <span class="badge bg-light text-dark mb-2">200 Points Required</span>
                        <?php if($points >= 200){ ?>
                            <span class="badge bg-success fs-6"><i class="fas fa-unlock me-1"></i>Unlocked</span>
                        <?php } else { ?>
                            <span class="badge bg-secondary fs-6"><i class="fas fa-lock me-1"></i>Locked</span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Progress Membaca Anda</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-3">
                                <h3 class="text-primary"><?php echo $points; ?></h3>
                                <p class="text-muted mb-0">Total Points</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h3 class="text-success"><?php echo ($points >= 10 ? 1 : 0) + ($points >= 50 ? 1 : 0) + ($points >= 100 ? 1 : 0) + ($points >= 200 ? 1 : 0); ?>/4</h3>
                                <p class="text-muted mb-0">Badge Terbuka</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h3 class="text-info"><?php echo min(100, ($points / 200) * 100); ?>%</h3>
                                <p class="text-muted mb-0">Menuju Level Master</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3">
                                <h3 class="text-warning"><?php echo 200 - $points; ?></h3>
                                <p class="text-muted mb-0">Poin untuk Badge Selanjutnya</p>
                            </div>
                        </div>
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
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>