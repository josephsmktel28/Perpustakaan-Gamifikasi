<?php
session_start();
include '../config/koneksi.php';

if($_SESSION['users']['role'] != 'admin'){
    header('Location: ../auth/login.php');
    exit;
}

$message = "";
$edit_mode = false;
$reward_data = [
    'nama_hadiah' => '',
    'deskripsi' => '',
    'reward_points' => 0,
    'chance' => 10
];

if(isset($_POST['add_reward'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama_hadiah']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $points = intval($_POST['reward_points']);
    $chance = intval($_POST['chance']);

    mysqli_query($conn, "INSERT INTO gacha_rewards (nama_hadiah, deskripsi, reward_points, chance) VALUES ('$nama', '$deskripsi', $points, $chance)");
    $message = "Hadiah gacha berhasil dibuat.";
}

if(isset($_POST['update_reward'])){
    $id = intval($_POST['id_reward']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_hadiah']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $points = intval($_POST['reward_points']);
    $chance = intval($_POST['chance']);

    mysqli_query($conn, "UPDATE gacha_rewards SET nama_hadiah = '$nama', deskripsi = '$deskripsi', reward_points = $points, chance = $chance WHERE id = $id");
    $message = "Hadiah gacha berhasil diperbarui.";
}

if(isset($_GET['delete'])){
    $delete_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM gacha_rewards WHERE id = $delete_id");
    header('Location: gacha.php?message=deleted');
    exit;
}

if(isset($_GET['edit'])){
    $edit_id = intval($_GET['edit']);
    $edit_mode = true;
    $result = mysqli_query($conn, "SELECT * FROM gacha_rewards WHERE id = $edit_id");
    if($result && mysqli_num_rows($result) > 0){
        $reward_data = mysqli_fetch_assoc($result);
    } else {
        $edit_mode = false;
        $message = "Hadiah tidak ditemukan.";
    }
}

if(isset($_GET['message']) && $_GET['message'] === 'deleted'){
    $message = "Hadiah gacha berhasil dihapus.";
}

$rewards = mysqli_query($conn, "SELECT * FROM gacha_rewards ORDER BY chance DESC, reward_points DESC, nama_hadiah ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Gacha Roulette</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/admin.css">
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
<a href="review.php">📝 Review User</a>
<a href="challenges.php">🎯 Challenges</a>
<a href="gacha.php">🎡 Gacha Rewards</a>
<a href="../auth/logout.php">🚪 Logout</a>

</div>
<div class="main-panel">
    <div class="admin-header">
        <div>
            <h2 class="page-title">Gacha Roulette</h2>
            <p class="admin-panel-title">Kelola hadiah gacha yang bisa ditarik oleh pengguna menggunakan poin.</p>
        </div>
    </div>

    <?php if($message){ ?>
    <div class="alert alert-custom"><?php echo htmlspecialchars($message); ?></div>
    <?php } ?>

    <div class="form-card">
        <form method="POST" class="mb-0">
            <input type="hidden" name="id_reward" value="<?php echo htmlspecialchars($reward_data['id'] ?? ''); ?>">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="nama_hadiah" class="form-control mb-2" placeholder="Nama Hadiah" value="<?php echo htmlspecialchars($reward_data['nama_hadiah']); ?>" required>
                    <textarea name="deskripsi" class="form-control mb-2" placeholder="Deskripsi Hadiah" rows="3" required><?php echo htmlspecialchars($reward_data['deskripsi']); ?></textarea>
                </div>
                <div class="col-md-3">
                    <input type="number" name="reward_points" class="form-control mb-2" placeholder="Poin Hadiah" value="<?php echo htmlspecialchars($reward_data['reward_points']); ?>" min="0" required>
                    <input type="number" name="chance" class="form-control mb-2" placeholder="Peluang (chance)" value="<?php echo htmlspecialchars($reward_data['chance']); ?>" min="1" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <?php if($edit_mode){ ?>
                        <button type="submit" name="update_reward" class="btn btn-success w-100">Update Hadiah</button>
                    <?php } else { ?>
                        <button type="submit" name="add_reward" class="btn btn-primary w-100">Tambah Hadiah</button>
                    <?php } ?>
                </div>
            </div>
        </form>
    </div>

    <div class="card shadow mt-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Daftar Hadiah Gacha</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Hadiah</th>
                            <th>Deskripsi</th>
                            <th>Poin Hadiah</th>
                            <th>Peluang</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($rewards) > 0){ ?>
                            <?php while($r = mysqli_fetch_assoc($rewards)){ ?>
                                <tr>
                                    <td><?php echo $r['id']; ?></td>
                                    <td><?php echo htmlspecialchars($r['nama_hadiah']); ?></td>
                                    <td><?php echo htmlspecialchars($r['deskripsi']); ?></td>
                                    <td><?php echo $r['reward_points']; ?></td>
                                    <td><?php echo $r['chance']; ?></td>
                                    <td>
                                        <a href="gacha.php?edit=<?php echo $r['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="gacha.php?delete=<?php echo $r['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus hadiah ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">Belum ada hadiah gacha. Tambahkan hadiah untuk mulai.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
