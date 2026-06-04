<?php
session_start();
include '../config/koneksi.php';

$user = $_SESSION['users'];
$id_user = $user['id'];

$message = "";

// PROSES RETURN + REVIEW
if(isset($_POST['return'])){

    $id_pinjam = $_POST['id_pinjam'];
    $review = mysqli_real_escape_string($conn, $_POST['review']);
    $rating = isset($_POST['rating']) ? mysqli_real_escape_string($conn, $_POST['rating']) : null;

    // Validasi: Harus mengisi rating atau review
    if (empty($rating) && empty($review)) {
        $message = "❌ Harus mengisi rating atau review sebelum return buku!";
    } else {
            $peminjaman_query = mysqli_query($conn, "SELECT tanggal_deadline, denda, pelanggaran, id_buku, status FROM peminjaman WHERE id_pinjam='$id_pinjam' AND id_user='$id_user'");
            $peminjaman_data = mysqli_fetch_assoc($peminjaman_query);
        $deadline = $peminjaman_data ? $peminjaman_data['tanggal_deadline'] : null;
        $configured_denda = $peminjaman_data && intval($peminjaman_data['denda']) > 0 ? intval($peminjaman_data['denda']) : 10;
        $configured_pelanggaran = $peminjaman_data && !empty($peminjaman_data['pelanggaran']) ? mysqli_real_escape_string($conn, $peminjaman_data['pelanggaran']) : 'Keterlambatan pengembalian';
        $isLate = $deadline && strtotime(date('Y-m-d')) > strtotime($deadline);

        // hanya proses return jika sebelumnya status adalah 'dipinjam'
        $previous_status = $peminjaman_data ? $peminjaman_data['status'] : null;
        $id_buku = $peminjaman_data ? $peminjaman_data['id_buku'] : null;

        if($previous_status !== 'dipinjam'){
            $message = "❌ Peminjaman ini sudah dikembalikan sebelumnya.";
        } else if ($isLate) {
            mysqli_query($conn,"UPDATE peminjaman 
            SET status='kembali terlambat', 
                tanggal_kembali=NOW(),
                review='$review',
                rating='$rating',
                denda={$configured_denda},
                pelanggaran='{$configured_pelanggaran}'
            WHERE id_pinjam='$id_pinjam'");

            mysqli_query($conn,"UPDATE users 
            SET points = GREATEST(points - {$configured_denda}, 0) 
            WHERE id='$id_user'");

            $message = "✅ Return berhasil! Buku terlambat, denda {$configured_denda} poin diterapkan.";
        } else {
            mysqli_query($conn,"UPDATE peminjaman 
            SET status='kembali', 
                tanggal_kembali=NOW(),
                review='$review',
                rating='$rating',
                denda=0,
                pelanggaran=NULL
            WHERE id_pinjam='$id_pinjam'");

            mysqli_query($conn,"UPDATE users 
            SET points = points + 20 
            WHERE id='$id_user'");

            $message = "✅ Return berhasil! +20 poin & review tersimpan";
        }

        // Jika return berhasil (sebelumnya status 'dipinjam'), tambahkan stok buku
        if(isset($id_buku) && $previous_status === 'dipinjam'){
            mysqli_query($conn, "UPDATE buku SET stok = stok + 1 WHERE id_buku='{$id_buku}'");
        }

        # Update Challenges Progress for return
        $challenges_return = mysqli_query($conn,"SELECT * FROM challenges WHERE syarat_type='return_buku'");
        while($ch = mysqli_fetch_assoc($challenges_return)){
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
    }
}

// DATA PINJAMAN
$data = mysqli_query($conn,"
SELECT p.*, b.judul 
FROM peminjaman p
JOIN buku b ON p.id_buku = b.id_buku
WHERE p.id_user='$id_user' AND p.status='dipinjam'
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kembalikan Buku - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/notification.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    .star-rating input {
        display: none;
    }
    .star-rating label {
        font-size: 25px;
        color: #ccc;
        cursor: pointer;
        transition: color 0.3s;
    }
    .star-rating input:checked ~ label {
        color: #ffc107;
    }
    .star-rating label:hover,
    .star-rating label:hover ~ label {
        color: #ffc107;
    }
    </style>
</head>
<body>

<!-- Celebration Notification Modal for Return -->
<div id="notificationModal" class="notification-modal">
    <div class="notification-content return">
        <button class="close-notification" onclick="closeNotification()">&times;</button>
        <div class="notification-icon">✨</div>
        <div class="notification-title">Return Berhasil!</div>
        <div class="notification-subtitle">Terima kasih telah mengembalikan buku</div>
        <div class="points-display">
            <div class="category-badge">Bonus +20 Poin</div>
            <div class="points-label">Poin Diterima</div>
            <div class="points-number shimmer">+20 🎯</div>
            <div class="book-title-notification">Review Tersimpan</div>
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
                    <h1 class="display-5 fw-bold text-success mb-2">
                        <i class="fas fa-undo me-3"></i>Kembalikan Buku & Beri Review
                    </h1>
                    <p class="lead text-muted">Bagikan pengalaman membaca Anda dan kembalikan buku</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Message - Notification via JavaScript -->
    <?php if($message){ ?>
    <!-- Hidden trigger for notification modal -->
    <?php } ?>

    <!-- Return Form -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-book-return me-2"></i>Return Your Book</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <!-- Book Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-book me-2"></i>Pilih Buku yang Akan Dikembalikan
                            </label>
                            <select name="id_pinjam" class="form-select form-select-lg" required>
                                <option value="">-- Pilih buku --</option>
                                <?php while($d = mysqli_fetch_assoc($data)){ ?>
                                <option value="<?php echo $d['id_pinjam']; ?>">
                                    ID <?php echo $d['id_pinjam']; ?> - <?php echo $d['judul']; ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Rating -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-star me-2"></i>Beri Rating Buku
                            </label>
                            <small class="form-text text-muted d-block mb-2">Anda harus memberikan rating atau review</small>
                            <div class="star-rating text-center">
                                <input type="radio" name="rating" value="5" id="5"><label for="5">★</label>
                                <input type="radio" name="rating" value="4" id="4"><label for="4">★</label>
                                <input type="radio" name="rating" value="3" id="3"><label for="3">★</label>
                                <input type="radio" name="rating" value="2" id="2"><label for="2">★</label>
                                <input type="radio" name="rating" value="1" id="1"><label for="1">★</label>
                            </div>
                        </div>

                        <!-- Review -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-comment me-2"></i>Tulis Review
                            </label>
                            <small class="form-text text-muted d-block mb-2">Bagikan pengalaman membaca Anda</small>
                            <textarea name="review" class="form-control" rows="4" 
                                placeholder="Ceritakan pengalaman membaca buku ini... Apa yang Anda sukai? Apa yang Anda pelajari?"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" name="return" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane me-2"></i>Kembalikan Buku & Kirim Review
                            </button>
                        </div>
                    </form>
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
<script src="../assets/notification.js"></script>

<script>
    // Trigger notification if message exists
    <?php if($message){ ?>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('return', 20, '');
            <?php if(isset($_POST['return'])){ ?>
                playNotificationSound();
            <?php } ?>
        });
    <?php } ?>
</script>

</body>
</html>