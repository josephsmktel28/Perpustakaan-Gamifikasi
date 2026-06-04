<?php
session_start();
include '../config/koneksi.php';

if(!isset($_GET['id']) || empty($_GET['id'])){
    header('Location: pinjam.php');
    exit;
}

$id = mysqli_real_escape_string($conn, trim($_GET['id']));
$bukuQuery = mysqli_query($conn, "SELECT * FROM buku WHERE id_buku='$id'");
if(mysqli_num_rows($bukuQuery) === 0){
    header('Location: pinjam.php');
    exit;
}
$b = mysqli_fetch_assoc($bukuQuery);

$message = "";
$id_user = $_SESSION['users']['id'];
$active_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM peminjaman WHERE id_user='$id_user' AND status='dipinjam'");
$active_row = mysqli_fetch_assoc($active_check);
$hasActiveBorrow = $active_row ? intval($active_row['cnt']) > 0 : false;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pinjam'])){
    $id_buku_pinjam = mysqli_real_escape_string($conn, $_POST['id_buku']);

    // Cek apakah user sudah memiliki peminjaman aktif
    $active_check = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM peminjaman WHERE id_user='$id_user' AND status='dipinjam'");
    $active_row = mysqli_fetch_assoc($active_check);
    $active_count = $active_row ? intval($active_row['cnt']) : 0;

    if($active_count > 0){
        $message = "❌ Anda sudah memiliki buku yang sedang dipinjam. Kembalikan dulu sebelum meminjam lagi.";
    } else {
        mysqli_query($conn,"INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, status) VALUES ('$id_user','$id_buku_pinjam', NOW(),'dipinjam')");
        $hasActiveBorrow = true;
        
        // Hitung poin berdasarkan kategori buku
        $buku_result = mysqli_query($conn,"SELECT kategori FROM buku WHERE id_buku='$id_buku_pinjam'");
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

        $challenges_pinjam = mysqli_query($conn,"SELECT * FROM challenges WHERE syarat_type='pinjam_buku'");
        while($ch = mysqli_fetch_assoc($challenges_pinjam)){
            $id_ch = $ch['id_challenge'];
            $check = mysqli_query($conn,"SELECT * FROM user_challenges WHERE id_user='$id_user' AND id_challenge='$id_ch'");
            if(mysqli_num_rows($check) == 0){
                mysqli_query($conn,"INSERT INTO user_challenges (id_user, id_challenge, progress) VALUES ('$id_user', '$id_ch', 1)");
            } else {
                mysqli_query($conn,"UPDATE user_challenges SET progress = progress + 1 WHERE id_user='$id_user' AND id_challenge='$id_ch'");
            }
            $user_ch = mysqli_query($conn,"SELECT progress, completed FROM user_challenges WHERE id_user='$id_user' AND id_challenge='$id_ch'");
            $uc = mysqli_fetch_assoc($user_ch);
            if($uc && $uc['progress'] >= $ch['syarat_value'] && !$uc['completed']){
                mysqli_query($conn,"UPDATE user_challenges SET completed=1, completed_date=NOW() WHERE id_user='$id_user' AND id_challenge='$id_ch'");
                mysqli_query($conn,"UPDATE users SET points = points + {$ch['reward_points']} WHERE id='$id_user'");
            }
        }

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

function resolveCoverUrl($coverPath) {
    if(empty($coverPath)){
        return 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=800&q=80';
    }
    if(preg_match('#^https?://#i', $coverPath) || preg_match('#^/#', $coverPath)){
        return $coverPath;
    }
    return '../' . ltrim($coverPath, '/');
}

$coverUrl = resolveCoverUrl($b['cover_url']);
$kategori = !empty($b['kategori']) ? $b['kategori'] : 'Fiksi';
$deskripsi = !empty($b['deskripsi']) ? $b['deskripsi'] : 'Deskripsi buku belum tersedia. Admin dapat menambahkan informasi detail buku dari panel admin.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku - <?php echo htmlspecialchars($b['judul']); ?> - Perpustakaan Digital</title>
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
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body text-center py-4">
                    <h1 class="display-5 fw-bold text-primary mb-2">
                        <i class="fas fa-book-reader me-3"></i>Detail Buku
                    </h1>
                    <p class="lead text-muted">Informasi lengkap buku ini dan kategorinya</p>
                </div>
            </div>
        </div>
    </div>

    <?php if(!empty($message)){ ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info shadow-sm" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        </div>
    </div>
    <?php } ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card detail-book-card h-100">
                <div class="detail-book-cover-container">
                    <img src="<?php echo htmlspecialchars($coverUrl); ?>" alt="Cover <?php echo htmlspecialchars($b['judul']); ?>" class="detail-book-cover">
                </div>
                <div class="card-body text-center">
                    <h2 class="book-title mb-3"><?php echo htmlspecialchars($b['judul']); ?></h2>
                    <p class="book-author mb-2"><i class="fas fa-user-edit me-2"></i><?php echo htmlspecialchars($b['penulis']); ?></p>
                    <span class="badge bg-primary px-4 py-2"><?php echo htmlspecialchars($kategori); ?></span>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card detail-book-card h-100">
                <div class="card-body">
                    <h2 class="mb-3"><?php echo htmlspecialchars($b['judul']); ?> <small class="text-muted">~ <?php echo htmlspecialchars($b['penulis']); ?></small></h2>
                    <hr>
                    <div class="mb-4">
                        <h5 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Deskripsi</h5>
                        <p class="text-muted mb-0"><?php echo nl2br(htmlspecialchars($deskripsi)); ?></p>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="info-card p-3 h-100">
                                <h6 class="fw-bold">Kategori</h6>
                                <p><?php echo htmlspecialchars($kategori); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="info-card p-3 h-100">
                                <h6 class="fw-bold">Stok</h6>
                                <p><?php echo intval($b['stok']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="info-card p-3 h-100">
                                <h6 class="fw-bold">Cover</h6>
                                <p><?php echo !empty($b['cover_url']) ? 'Tersedia' : 'Default'; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="pinjam.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <!-- <?php if($hasActiveBorrow){ ?>
                            <button type="button" class="btn btn-outline-secondary btn-lg w-100 disabled" disabled>
                                <i class="fas fa-ban me-2"></i>Tidak Bisa Meminjam Sekarang
                            </button>
                        <?php } else { ?>
                            <form method="POST" class="m-0 w-100">
                                <input type="hidden" name="id_buku" value="<?php echo htmlspecialchars($b['id_buku']); ?>">
                                <button type="submit" name="pinjam" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-hand-holding me-2"></i>Pinjam Buku Ini
                                </button>
                            </form> -->
                        <?php } ?>
                    </div>
                    <?php if($hasActiveBorrow){ ?>
                        <div class="alert alert-warning mt-3">
                            Anda sudah memiliki buku yang sedang dipinjam. Kembalikan buku tersebut terlebih dahulu untuk dapat meminjam ulang.
                        </div>
                    <?php } ?>
                </div>
            </div>
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
                    showNotification('success', <?php echo $poin; ?>, '<?php echo $kategori; ?>', '<?php echo htmlspecialchars($b['judul']); ?>');
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
