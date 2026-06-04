<?php
session_start();
include '../config/koneksi.php';

$user = $_SESSION['users'];
$id_user = $user['id'];

# Query untuk menampilkan semua peminjaman user
$peminjaman = mysqli_query($conn,"
SELECT p.id_pinjam, p.tanggal_pinjam, p.tanggal_deadline, p.tanggal_kembali, p.status,
       p.denda, p.pelanggaran,
       b.judul, b.penulis
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
WHERE p.id_user = '$id_user'
ORDER BY p.tanggal_pinjam DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Peminjaman - Perpustakaan Digital</title>
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
                    <a class="nav-link active" href="peminjaman.php"><i class="fas fa-history me-1"></i>Riwayat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="badge.php"><i class="fas fa-trophy me-1"></i>Badge</a>
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

<div class="container">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 fw-bold text-primary mb-2">
                        <i class="fas fa-history me-3"></i>Riwayat Peminjaman Saya
                    </h1>
                    <p class="lead text-muted">Lacak semua aktivitas peminjaman buku Anda</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrowing History -->
    <div class="row">
        <?php 
        if(mysqli_num_rows($peminjaman) > 0){
            while($p = mysqli_fetch_assoc($peminjaman)){ 
        ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card book-card h-100">
                <div class="card-body d-flex flex-column">
                    <div class="mb-3">
                        <h5 class="book-title">
                            <i class="fas fa-book text-primary me-2"></i><?php echo $p['judul']; ?>
                        </h5>
                        <p class="book-author mb-2">
                            <i class="fas fa-user-edit me-1"></i><?php echo $p['penulis']; ?>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted d-block">Pinjam</small>
                                <span class="fw-bold"><?php echo date('M d, Y', strtotime($p['tanggal_pinjam'])); ?></span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Deadline</small>
                                <span class="fw-bold"><?php echo $p['tanggal_deadline'] ? date('M d, Y', strtotime($p['tanggal_deadline'])) : '-'; ?></span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">Kembali</small>
                                <span class="fw-bold"><?php echo $p['tanggal_kembali'] ? date('M d, Y', strtotime($p['tanggal_kembali'])) : '-'; ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3 text-center">
                        <?php
                            if($p['status'] == 'dipinjam'){
                                $isOverdue = $p['tanggal_deadline'] && strtotime(date('Y-m-d')) > strtotime($p['tanggal_deadline']);
                                echo '<span class="status-badge '.($isOverdue ? 'bg-borrowed' : 'bg-warning').' w-100 d-inline-block text-center">'.($isOverdue ? '<i class="fas fa-exclamation-triangle me-1"></i>Terlambat' : '<i class="fas fa-clock me-1"></i>Dipinjam').'</span>';
                            } elseif($p['status'] == 'kembali'){
                                echo '<span class="status-badge bg-returned w-100 d-inline-block text-center"><i class="fas fa-check me-1"></i>Dikembalikan</span>';
                            } else {
                                echo '<span class="status-badge bg-secondary w-100 d-inline-block text-center">' . htmlspecialchars($p['status']) . '</span>';
                            }
                        ?>
                    </div>
                    <?php if($p['denda'] > 0 || !empty($p['pelanggaran'])){ ?>
                        <div class="mb-3 text-center text-danger">
                            <div class="fw-bold">Denda: <?php echo $p['denda']; ?> poin</div>
                            <?php if(!empty($p['pelanggaran'])){ ?>
                                <small class="text-muted"><?php echo htmlspecialchars($p['pelanggaran']); ?></small>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <div class="mt-auto"></div>
                </div>
            </div>
        </div>
        <?php 
            }
        } else {
        ?>
        <div class="col-12">
            <div class="card shadow text-center py-5">
                <div class="card-body">
                    <div class="display-1 text-muted mb-3">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h4 class="text-muted">Belum Ada Riwayat Peminjaman</h4>
                    <p class="text-muted">Anda belum meminjam buku apapun. Mulai perjalanan membaca Anda!</p>
                    <a href="pinjam.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Pinjam Buku Pertama Anda
                    </a>
                </div>
            </div>
        </div>
        <?php } ?>
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
