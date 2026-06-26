<?php
session_start();
include '../config/koneksi.php';

if(!isset($_SESSION['users'])){
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['users'];
$id_user = $user['id'];

$SPIN_COST = 20;
$message = "";
$reward_result = null;

// Refresh poin pengguna dari database agar halaman selalu menampilkan nilai terbaru
$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE id = '$id_user'");
$userData = mysqli_fetch_assoc($userQuery);
if($userData){
    $current_points = intval($userData['points']);
    $_SESSION['users'] = array_merge($_SESSION['users'], $userData);
    $user = $_SESSION['users'];
} else {
    $current_points = intval($user['points']);
}

if(isset($_POST['spin'])){
    $userQuery = mysqli_query($conn, "SELECT points FROM users WHERE id = '$id_user'");
    $userData = mysqli_fetch_assoc($userQuery);
    $current_points = intval($userData['points']);

    if($current_points < $SPIN_COST){
        $message = "Maaf, poin Anda tidak cukup untuk memutar gacha. Butuh $SPIN_COST poin.";
    } else {
        $rewards = mysqli_query($conn, "SELECT * FROM gacha_rewards WHERE chance > 0");
        $rewardList = [];
        $totalChance = 0;

        while($row = mysqli_fetch_assoc($rewards)){
            $rewardList[] = $row;
            $totalChance += intval($row['chance']);
        }

        if(empty($rewardList)){
            $message = "Belum ada hadiah gacha tersedia. Silakan hubungi admin.";
        } else {
            if($totalChance <= 0){
                foreach($rewardList as $row){
                    $totalChance += 1;
                    $row['chance'] = 1;
                }
            }

            $rand = random_int(1, $totalChance);
            $current = 0;
            $selected = null;
            foreach($rewardList as $item){
                $current += intval($item['chance']);
                if($rand <= $current){
                    $selected = $item;
                    break;
                }
            }

            if(!$selected){
                $selected = $rewardList[array_rand($rewardList)];
            }

            $awardedPoints = intval($selected['reward_points']);
            $newPoints = $current_points - $SPIN_COST + $awardedPoints;

            mysqli_query($conn, "UPDATE users SET points = $newPoints WHERE id = '$id_user'");
            mysqli_query($conn, "INSERT INTO gacha_history (id_user, id_reward, awarded_points, cost_points) VALUES ($id_user, {$selected['id']}, $awardedPoints, $SPIN_COST)");

            $message = "Selamat! Anda mendapatkan <strong>\"" . htmlspecialchars($selected['nama_hadiah']) . "\"</strong>. " . htmlspecialchars($selected['deskripsi']) . "<br>Biaya putaran: $SPIN_COST poin, hadiah: +$awardedPoints poin.";
            $_SESSION['users']['points'] = $newPoints;
            $current_points = $newPoints;
            $reward_result = $selected;
        }
    }
}

$reward_list = mysqli_query($conn, "SELECT * FROM gacha_rewards ORDER BY chance DESC, reward_points DESC");
$history = mysqli_query($conn, "SELECT h.*, r.nama_hadiah, r.deskripsi, r.reward_points FROM gacha_history h JOIN gacha_rewards r ON h.id_reward = r.id WHERE h.id_user = '$id_user' ORDER BY created_at DESC LIMIT 8");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gacha Roulette - Perpustakaan Digital</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/notification.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div id="notificationModal" class="notification-modal">
    <div class="notification-content gacha">
        <button class="close-notification" onclick="closeNotification()">&times;</button>
        <div class="notification-icon">🎉</div>
        <div class="notification-title">Hadiah Gacha!</div>
        <div class="notification-subtitle">Selamat! Hadiah Anda berhasil diklaim.</div>
        <div class="points-display">
            <div class="category-badge">Hadiah Gacha</div>
            <div class="points-label">Poin Diterima</div>
            <div class="points-number shimmer">+0 🎯</div>
            <div class="book-title-notification">"Hadiah"</div>
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
                    <a class="nav-link" href="pinjam.php"><i class="fas fa-plus me-1"></i>Pinjam Buku</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="peminjaman.php"><i class="fas fa-history me-1"></i>Riwayat</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="badge.php"><i class="fas fa-trophy me-1"></i>Badge</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="gacha.php"><i class="fas fa-dice me-1"></i>Gacha</a>
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
                    <h1 class="display-5 fw-bold text-primary mb-2"><i class="fas fa-dice me-3"></i>Gacha Roulette</h1>
                    <p class="lead text-muted">Gunakan poin Anda untuk memutar roulette dan dapatkan hadiah menarik!</p>
                </div>
            </div>
        </div>
    </div>

    <?php if($message){ ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-<?php echo strpos($message, 'Maaf') !== false ? 'danger' : 'success'; ?>">
                <?php echo $message; ?>
            </div>
        </div>
    </div>
    <?php } ?>

    <div class="row gy-4">
        <div class="col-lg-5">
            <div class="card shadow h-100">
                <div class="card-body text-center">
                    <h3 class="card-title">Poin Anda</h3>
                    <div class="display-1 text-warning mb-3"><?php echo $current_points; ?></div>
                    <p class="text-muted">Biaya satu putaran gacha: <strong><?php echo $SPIN_COST; ?> poin</strong>.</p>
                    <form method="POST">
                        <button type="submit" name="spin" class="btn btn-primary btn-lg w-100"<?php echo $current_points < $SPIN_COST ? ' disabled' : ''; ?>>
                            <i class="fas fa-dice fa-lg me-2"></i>Spin Gacha
                        </button>
                    </form>
                    <?php if($reward_result){ ?>
                        <div class="mt-4 p-3 bg-light rounded shadow-sm text-start">
                            <h5>Hadiah Terakhir</h5>
                            <strong><?php echo htmlspecialchars($reward_result['nama_hadiah']); ?></strong><br>
                            <?php echo htmlspecialchars($reward_result['deskripsi']); ?><br>
                            <span class="badge bg-success">+<?php echo intval($reward_result['reward_points']); ?> poin</span>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow h-100">
                <div class="card-body">
                    <h4 class="card-title">Daftar Hadiah Gacha</h4>
                    <p class="text-muted">Hadiah akan dipilih secara acak berdasarkan peluang masing-masing.</p>
                    <div class="row gy-3">
                        <?php if(mysqli_num_rows($reward_list) > 0){ ?>
                            <?php while($row = mysqli_fetch_assoc($reward_list)){ ?>
                                <div class="col-md-6">
                                    <div class="card h-100 border-primary">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($row['nama_hadiah']); ?></h5>
                                            <p class="card-text text-muted"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="badge bg-success">+<?php echo intval($row['reward_points']); ?> poin</span>
                                                <span class="badge bg-secondary">Chance: <?php echo intval($row['chance']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="col-12">
                                <div class="alert alert-warning mb-0">Belum ada hadiah gacha tersedia. Silakan hubungi admin.</div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Riwayat Gacha Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Hadiah</th>
                                    <th>Point</th>
                                    <th>Biaya</th>
                                    <th>Waktu</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($history) > 0){ ?>
                                    <?php $idx = 1; while($h = mysqli_fetch_assoc($history)){ ?>
                                        <tr>
                                            <td><?php echo $idx++; ?></td>
                                            <td><?php echo htmlspecialchars($h['nama_hadiah']); ?></td>
                                            <td>+<?php echo intval($h['awarded_points']); ?></td>
                                            <td>-<?php echo intval($h['cost_points']); ?></td>
                                            <td><?php echo htmlspecialchars($h['created_at']); ?></td>
                                            <td>
                                                <button type='button' class='btn btn-sm btn-warning btn-gacha-claim' data-hadiah="<?php echo htmlspecialchars($h['nama_hadiah'], ENT_QUOTES, 'UTF-8'); ?>" data-deskripsi="<?php echo htmlspecialchars($h['deskripsi'], ENT_QUOTES, 'UTF-8'); ?>" data-points="<?php echo intval($h['awarded_points']); ?>">
                                                    Klaim
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-3">Belum ada riwayat gacha.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/notification.js"></script>
</body>
</html>
