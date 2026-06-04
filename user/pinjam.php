<?php

session_start();
include '../config/koneksi.php';

$user = $_SESSION['users'];
$id_user = $user['id'];

$message = "";

$active_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM peminjaman WHERE id_user='$id_user' AND status='dipinjam'");
$active_row = mysqli_fetch_assoc($active_check);
$active_count = $active_row ? intval($active_row['cnt']) : 0;
$hasActiveBorrow = $active_count > 0;

if(isset($_POST['pinjam'])){

    $id_buku = mysqli_real_escape_string($conn, $_POST['id_buku']);

    // Cek stok buku sebelum meminjam
    $stok_res = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku='$id_buku'");
    $stok_data = mysqli_fetch_assoc($stok_res);
    $stok_now = $stok_data ? intval($stok_data['stok']) : 0;

    if($stok_now <= 0){
        $message = "❌ Stok buku kosong. Tidak dapat meminjam.";
    } else {
        if($hasActiveBorrow){
            $message = "❌ Anda sudah memiliki buku yang sedang dipinjam. Kembalikan dulu sebelum meminjam lagi.";
        } else {
            mysqli_query($conn,"INSERT INTO peminjaman 
            (id_user, id_buku, tanggal_pinjam, tanggal_deadline, status, denda, pelanggaran) 
            VALUES ('$id_user','$id_buku', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'dipinjam', 0, NULL)");

            // Kurangi stok buku
            mysqli_query($conn, "UPDATE buku SET stok = GREATEST(stok - 1, 0) WHERE id_buku='$id_buku'");
            $hasActiveBorrow = true;

            $buku_result = mysqli_query($conn,"SELECT kategori FROM buku WHERE id_buku='$id_buku'");
            $buku_data = mysqli_fetch_assoc($buku_result);
            $kategori = $buku_data['kategori'];

            // Tentukan poin berdasarkan kategori
            if(strtolower($kategori) == 'fiksi'){
                $poin = 2;
            } elseif(strtolower($kategori) == 'non-fiksi' || strtolower($kategori) == 'nonfiksi'){
                $poin = 5;
            } else {
                $poin = 5; // Default poin untuk kategori lain
            }

            mysqli_query($conn,"UPDATE users SET points = points + $poin WHERE id='$id_user'");

            # Update Challenges Progress
            $challenges_pinjam = mysqli_query($conn,"SELECT * FROM challenges WHERE syarat_type='pinjam_buku'");
            while($ch = mysqli_fetch_assoc($challenges_pinjam)){
                $id_ch = $ch['id_challenge'];
                $check = mysqli_query($conn,"SELECT * FROM user_challenges WHERE id_user='$id_user' AND id_challenge='$id_ch'");
                if(mysqli_num_rows($check) == 0){
                    mysqli_query($conn,"INSERT INTO user_challenges (id_user, id_challenge, progress) VALUES ('$id_user', '$id_ch', 1)");
                } else {
                    mysqli_query($conn,"UPDATE user_challenges SET progress = progress + 1 WHERE id_user='$id_user' AND id_challenge='$id_ch'");
                }
                # Check if completed
                $user_ch = mysqli_query($conn,"SELECT progress, completed FROM user_challenges WHERE id_user='$id_user' AND id_challenge='$id_ch'");
                $uc = mysqli_fetch_assoc($user_ch);
                if($uc && $uc['progress'] >= $ch['syarat_value'] && !$uc['completed']){
                    mysqli_query($conn,"UPDATE user_challenges SET completed=1, completed_date=NOW() WHERE id_user='$id_user' AND id_challenge='$id_ch'");
                    mysqli_query($conn,"UPDATE users SET points = points + {$ch['reward_points']} WHERE id='$id_user'");
                }
            }

            # Update Streak
            $today = date('Y-m-d');
            $streak_check = mysqli_query($conn,"SELECT * FROM streaks WHERE id_user='$id_user' AND streak_type='daily_pinjam'");
            if(mysqli_num_rows($streak_check) == 0){
                mysqli_query($conn,"INSERT INTO streaks (id_user, streak_type, current_streak, last_date) VALUES ('$id_user', 'daily_pinjam', 1, '$today')");
            } else {
                $streak = mysqli_fetch_assoc($streak_check);
                $last_date = $streak['last_date'];
                if($last_date == date('Y-m-d', strtotime('-1 day'))){
                    mysqli_query($conn,"UPDATE streaks SET current_streak = current_streak + 1, last_date='$today' WHERE id_user='$id_user' AND streak_type='daily_pinjam'");
                } elseif($last_date != $today){
                    mysqli_query($conn,"UPDATE streaks SET current_streak = 1, last_date='$today' WHERE id_user='$id_user' AND streak_type='daily_pinjam'");
                }
            }

            $message = "Pinjam berhasil! +$poin poin (Kategori: $kategori)";
        }
    }
}

$filterKategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, trim($_GET['kategori'])) : '';
$whereKategori = '';
if($filterKategori){
    $whereKategori = "WHERE kategori='$filterKategori'";
}
$buku = mysqli_query($conn,"SELECT * FROM buku $whereKategori");

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
    <title>Pinjam Buku - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/notification.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<!-- Celebration Notification Modal -->
<div id="notificationModal" class="notification-modal">
    <div class="notification-content success">
        <button class="close-notification" onclick="closeNotification()">&times;</button>
        <div class="notification-icon">📚</div>
        <div class="notification-title">Pinjam Berhasil!</div>
        <div class="notification-subtitle">Selamat! Anda telah meminjam buku</div>
        <div class="points-display">
            <div class="category-badge">Fiksi</div>
            <div class="points-label">Poin Diterima</div>
            <div class="points-number shimmer">+2 🎯</div>
            <div class="book-title-notification">"Judul Buku"</div>
        </div>
    </div>
</div>

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
                    <a class="nav-link active" href="pinjam.php"><i class="fas fa-plus me-1"></i>Pinjam Buku</a>
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
                        <i class="fas fa-plus-circle me-3"></i>Pinjam Buku
                    </h1>
                    <p class="lead text-muted">Pilih dari koleksi buku menarik kami</p>
                </div>
            </div>
        </div>
    </div>
    <?php if($hasActiveBorrow){ ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning shadow-sm" role="alert">
                <strong>Perhatian:</strong> Anda sudah memiliki buku yang sedang dipinjam. Silakan kembalikan buku tersebut terlebih dahulu agar dapat meminjam lagi.
            </div>
        </div>
    </div>
    <?php } ?>
    <?php if(!empty($message)){ ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info shadow-sm" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- Notification Modal will be triggered via JavaScript -->

    <!-- Category Filter -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <a href="pinjam.php" class="btn btn-outline-light btn-sm me-2 <?= $filterKategori === '' ? 'active btn-primary' : '' ?>">Semua</a>
            <a href="pinjam.php?kategori=Fiksi" class="btn btn-outline-light btn-sm me-2 <?= $filterKategori === 'Fiksi' ? 'active btn-primary' : '' ?>">Fiksi</a>
            <a href="pinjam.php?kategori=Non-Fiksi" class="btn btn-outline-light btn-sm <?= $filterKategori === 'Non-Fiksi' ? 'active btn-primary' : '' ?>">Non-Fiksi</a>
        </div>
    </div>

    <!-- Book Grid -->
    <div class="row">
        <?php while($b = mysqli_fetch_assoc($buku)){ ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card book-card h-100">
                <div class="book-cover-container mb-3">
                    <?php $coverPath = resolveCoverPath($b['cover_url']); ?>
                    <img src="<?php echo htmlspecialchars($coverPath); ?>" alt="Cover <?php echo htmlspecialchars($b['judul']); ?>" class="book-cover">
                </div>
                <div class="card-body d-flex flex-column text-center">
                    <h5 class="book-title"><?php echo $b['judul']; ?></h5>
                    <p class="book-author">
                        <i class="fas fa-user-edit me-1"></i><?php echo $b['penulis']; ?>
                    </p>
                    <?php if(isset($b['kategori'])){ ?>
                    <span class="badge bg-light text-dark mb-3"><?php echo $b['kategori']; ?></span>
                    <?php } ?>
                    
                    <div class="mb-3">
                        <a href="detail_buku.php?id=<?php echo $b['id_buku']; ?>" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-info-circle me-2"></i>Lihat Detail
                        </a>
                    </div>
                    <div class="mt-auto">
                        <?php if($hasActiveBorrow){ ?>
                            <button type="button" class="btn btn-outline-secondary w-100 mb-2 py-3 fw-semibold disabled" disabled>
                                <i class="fas fa-ban me-2"></i>Tidak Bisa Meminjam Sekarang
                            </button>
                            <small class="text-muted d-block mt-2">Kembalikan buku aktif terlebih dahulu untuk dapat meminjam lagi.</small>
                        <?php } else {
                            $isOutOfStock = intval($b['stok']) <= 0;
                            $buttonClass = $isOutOfStock ? 'btn btn-outline-secondary' : 'btn btn-outline-primary';
                            $buttonText = $isOutOfStock ? 'Stok Habis' : 'Pinjam Buku Ini';
                        ?>
                        <form method="POST" class="d-inline w-100">
                            <input type="hidden" name="id_buku" value="<?php echo $b['id_buku']; ?>">
                            <button type="submit" name="pinjam" class="<?php echo $buttonClass; ?> w-100 mb-2 py-3 fw-semibold" <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                                <i class="fas fa-hand-holding me-2"></i><?php echo $buttonText; ?>
                            </button>
                        </form>
                        <?php if($isOutOfStock){ ?>
                            <small class="text-muted d-block mt-2">Stok buku habis, pilih buku lain.</small>
                        <?php } ?>
                        <?php } ?>
                    </div>
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
<script src="../assets/notification.js"></script>

<script>
    // Trigger notification if message exists
    <?php if($message){ ?>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
                // Parse message to extract poin and kategori
                if(preg_match('/\+(\d+)\s+poin.*Kategori:\s+(.+)\)/', $message, $matches)){
                    $poin = $matches[1];
                    $kategori = trim($matches[2]);
                    ?>
                    showNotification('success', <?php echo $poin; ?>, '<?php echo $kategori; ?>');
                    <?php if(isset($_POST['pinjam'])){ ?>
                        playNotificationSound();
                    <?php } ?>
                    <?php
                }
            ?>
        });
    <?php } ?>
</script>

</body>
</html>