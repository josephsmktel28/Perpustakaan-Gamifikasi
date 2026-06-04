<?php
session_start();
include '../config/koneksi.php';

if($_SESSION['users']['role'] != 'admin'){
    header('Location: ../auth/login.php');
    exit;
}

$message = "";
$edit_mode = false;
$challenge_data = [
    'nama_challenge' => '',
    'deskripsi' => '',
    'syarat_type' => 'pinjam_buku',
    'syarat_value' => '',
    'reward_points' => ''
];

if(isset($_POST['add_challenge'])){
    $nama = mysqli_real_escape_string($conn, $_POST['nama_challenge']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $type = mysqli_real_escape_string($conn, $_POST['syarat_type']);
    $value = intval($_POST['syarat_value']);
    $points = intval($_POST['reward_points']);

    mysqli_query($conn, "INSERT INTO challenges (nama_challenge, deskripsi, syarat_type, syarat_value, reward_points) VALUES ('$nama', '$deskripsi', '$type', $value, $points)");
    $message = "Challenge added successfully!";
}

if(isset($_POST['update_challenge'])){
    $id = intval($_POST['id_challenge']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_challenge']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $type = mysqli_real_escape_string($conn, $_POST['syarat_type']);
    $value = intval($_POST['syarat_value']);
    $points = intval($_POST['reward_points']);

    mysqli_query($conn, "UPDATE challenges SET nama_challenge = '$nama', deskripsi = '$deskripsi', syarat_type = '$type', syarat_value = $value, reward_points = $points WHERE id_challenge = $id");
    $message = "Challenge updated successfully!";
}

if(isset($_GET['delete'])){
    $delete_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM challenges WHERE id_challenge = $delete_id");
    header('Location: challenges.php?message=deleted');
    exit;
}

if(isset($_GET['edit'])){
    $edit_id = intval($_GET['edit']);
    $edit_mode = true;
    $result = mysqli_query($conn, "SELECT * FROM challenges WHERE id_challenge = $edit_id");
    if($result && mysqli_num_rows($result) > 0){
        $challenge_data = mysqli_fetch_assoc($result);
    } else {
        $edit_mode = false;
        $message = "Challenge not found.";
    }
}

if(isset($_GET['message']) && $_GET['message'] === 'deleted'){
    $message = "Challenge deleted successfully!";
}

$challenges = mysqli_query($conn, "SELECT * FROM challenges");
?>

<!DOCTYPE html>
<html>
<head>
<title>Manage Challenges</title>
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
            <h2 class="page-title">Challenges</h2>
            <p class="admin-panel-title">Tambah dan kelola tantangan gamifikasi.</p>
        </div>
    </div>

    <?php if($message){ ?>
    <div class="alert alert-custom"><?php echo $message; ?></div>
    <?php } ?>

    <div class="form-card">
    <form method="POST" class="mb-0">
<input type="hidden" name="id_challenge" value="<?php echo htmlspecialchars($challenge_data['id_challenge'] ?? ''); ?>">
<div class="row">
<div class="col-md-6">
<input type="text" name="nama_challenge" class="form-control mb-2" placeholder="Challenge Name" value="<?php echo htmlspecialchars($challenge_data['nama_challenge']); ?>" required>
<textarea name="deskripsi" class="form-control mb-2" placeholder="Description" required><?php echo htmlspecialchars($challenge_data['deskripsi']); ?></textarea>
</div>
<div class="col-md-6">
<select name="syarat_type" class="form-control mb-2" required>
<option value="pinjam_buku" <?php echo ($challenge_data['syarat_type'] === 'pinjam_buku') ? 'selected' : ''; ?>>Pinjam Buku</option>
<option value="return_buku" <?php echo ($challenge_data['syarat_type'] === 'return_buku') ? 'selected' : ''; ?>>Return Buku</option>
<option value="daily_pinjam" <?php echo ($challenge_data['syarat_type'] === 'daily_pinjam') ? 'selected' : ''; ?>>Daily Pinjam</option>
</select>
<input type="number" name="syarat_value" class="form-control mb-2" placeholder="Syarat Value" value="<?php echo htmlspecialchars($challenge_data['syarat_value']); ?>" required>
<input type="number" name="reward_points" class="form-control mb-2" placeholder="Reward Points" value="<?php echo htmlspecialchars($challenge_data['reward_points']); ?>" required>
<?php if($edit_mode){ ?>
<button name="update_challenge" class="btn btn-success">Update Challenge</button>
<a href="challenges.php" class="btn btn-secondary ms-2">Cancel</a>
<?php } else { ?>
<button name="add_challenge" class="btn btn-primary">Add Challenge</button>
<?php } ?>
</div>
</div>
</form>
    </div>

    <div class="glass-table-card">
    <table class="table table-admin">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Description</th>
<th>Type</th>
<th>Value</th>
<th>Reward</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php while($c = mysqli_fetch_assoc($challenges)){ ?>
<tr>
<td><?php echo $c['id_challenge']; ?></td>
<td><?php echo $c['nama_challenge']; ?></td>
<td><?php echo $c['deskripsi']; ?></td>
<td><?php echo $c['syarat_type']; ?></td>
<td><?php echo $c['syarat_value']; ?></td>
<td><?php echo $c['reward_points']; ?></td>
<td>
<a href="challenges.php?edit=<?php echo $c['id_challenge']; ?>" class="btn btn-sm btn-warning">Edit</a>
<a href="challenges.php?delete=<?php echo $c['id_challenge']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this challenge?');">Delete</a>
</td>
</tr>
<?php } ?>
</tbody>
</table>

<a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

</div>

</div>
</body>
</html>