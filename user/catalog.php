<?php
session_start();
include '../config/koneksi.php';

$user = $_SESSION['users'];
$id_user = $user['id'];

$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';
$filterKategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : '';
$where = '';
$conditions = array();

if($searchQuery !== ''){
    $safeSearch = mysqli_real_escape_string($conn, $searchQuery);
    $conditions[] = "(judul LIKE '%$safeSearch%' OR penulis LIKE '%$safeSearch%' OR kategori LIKE '%$safeSearch%' OR deskripsi LIKE '%$safeSearch%')";
}

if($filterKategori !== ''){
    $safeKategori = mysqli_real_escape_string($conn, $filterKategori);
    $conditions[] = "kategori = '$safeKategori'";
}

if(!empty($conditions)){
    $where = "WHERE " . implode(" AND ", $conditions);
}

$buku = mysqli_query($conn, "SELECT * FROM buku $where ORDER BY judul ASC");
$resultCount = mysqli_num_rows($buku);

function resolveCoverPath($coverPath) {
    if(empty($coverPath)){
        return 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80';
    }
    if(preg_match('#^https?://#i', $coverPath) || preg_match('#^/#', $coverPath)){
        return $coverPath;
    }
    return '../' . ltrim($coverPath, '/');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog Buku - Perpustakaan Digital</title>
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
                    <a class="nav-link active" href="catalog.php"><i class="fas fa-book-open me-1"></i>Catalog</a>
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
                    <a class="nav-link" href="leaderboard.php"><i class="fas fa-chart-line me-1"></i>Leaderboard</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['username']); ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 fw-bold text-primary mb-2">
                        <i class="fas fa-book-open me-3"></i>Catalog Buku
                    </h1>
                    <p class="lead text-muted">Jelajahi koleksi buku lengkap dengan pencarian cepat.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <form class="d-flex gap-2" method="GET" action="catalog.php">
                <input type="search" name="q" class="form-control" placeholder="Cari judul, penulis, kategori, atau deskripsi..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                <?php if($filterKategori !== ''){ ?>
                    <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($filterKategori); ?>">
                <?php } ?>
                <button class="btn btn-primary" type="submit"><i class="fas fa-search me-2"></i>Cari</button>
            </form>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-12 text-center">
            <a href="catalog.php<?php echo $searchQuery !== '' ? '?q='.urlencode($searchQuery) : ''; ?>" class="btn btn-outline-primary btn-sm me-2 <?= $filterKategori === '' ? 'active btn-primary' : '' ?>">Semua</a>
            <a href="catalog.php?kategori=Fiksi<?php echo $searchQuery !== '' ? '&q='.urlencode($searchQuery) : ''; ?>" class="btn btn-outline-primary btn-sm me-2 <?= $filterKategori === 'Fiksi' ? 'active btn-primary' : '' ?>">Fiksi</a>
            <a href="catalog.php?kategori=Non-Fiksi<?php echo $searchQuery !== '' ? '&q='.urlencode($searchQuery) : ''; ?>" class="btn btn-outline-primary btn-sm <?= $filterKategori === 'Non-Fiksi' ? 'active btn-primary' : '' ?>">Non-Fiksi</a>
        </div>
    </div>
    <?php if($resultCount === 0){ ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-warning text-center" role="alert">
                    <strong>Notifikasi:</strong> Tidak ada buku yang cocok dengan pencarian Anda<?php echo $searchQuery !== '' ? ' untuk "'.htmlspecialchars($searchQuery).'"' : ''; ?>.
                </div>
            </div>
        </div>
    <?php } ?>

    <div class="row">
        <?php if($resultCount > 0){ ?>
            <?php while($b = mysqli_fetch_assoc($buku)){ ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card book-card h-100">
                        <div class="book-cover-container mb-3">
                            <?php $coverPath = resolveCoverPath($b['cover_url']); ?>
                            <img src="<?php echo htmlspecialchars($coverPath); ?>" alt="Cover <?php echo htmlspecialchars($b['judul']); ?>" class="book-cover">
                        </div>
                        <div class="card-body d-flex flex-column text-center">
                            <h5 class="book-title"><?php echo htmlspecialchars($b['judul']); ?></h5>
                            <p class="book-author"><i class="fas fa-user-edit me-1"></i><?php echo htmlspecialchars($b['penulis']); ?></p>
                            <?php if(!empty($b['kategori'])){ ?>
                                <span class="badge bg-light text-dark mb-3"><?php echo htmlspecialchars($b['kategori']); ?></span>
                            <?php } ?>
                            <p class="text-muted small mb-4"><?php echo !empty($b['deskripsi']) ? htmlspecialchars(substr($b['deskripsi'], 0, 100)).'...' : 'Deskripsi belum tersedia.'; ?></p>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
