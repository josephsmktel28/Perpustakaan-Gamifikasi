<?php
session_start();
include __DIR__ . '/../config/koneksi.php';

if(!isset($_GET['id']) && !isset($_POST['id_pinjam'])){
    header('Location: peminjaman.php');
    exit;
}

$message = '';

if(isset($_POST['id_pinjam'])){
    $id_pinjam = intval($_POST['id_pinjam']);
    $tanggal_deadline = mysqli_real_escape_string($conn, trim($_POST['tanggal_deadline']));
    $denda = intval($_POST['denda']);
    $pelanggaran = mysqli_real_escape_string($conn, trim($_POST['pelanggaran']));

    $deadline_sql = $tanggal_deadline !== '' ? "'{$tanggal_deadline}'" : 'NULL';
    $pelanggaran_sql = $pelanggaran !== '' ? "'{$pelanggaran}'" : 'NULL';

    mysqli_query($conn, "UPDATE peminjaman SET tanggal_deadline={$deadline_sql}, denda={$denda}, pelanggaran={$pelanggaran_sql} WHERE id_pinjam='{$id_pinjam}'");

    header('Location: peminjaman.php?updated=1');
    exit;
}

$id_pinjam = intval($_GET['id']);
$data_query = mysqli_query($conn, "SELECT p.*, u.username, b.judul FROM peminjaman p JOIN users u ON p.id_user = u.id JOIN buku b ON p.id_buku = b.id_buku WHERE p.id_pinjam='$id_pinjam'");
if(mysqli_num_rows($data_query) === 0){
    header('Location: peminjaman.php');
    exit;
}
$data = mysqli_fetch_assoc($data_query);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Peminjaman</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/admin.css">
</head>
<body class="admin-page">

<div class="admin-layout">

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
            <h2 class="page-title">Edit Peminjaman</h2>
            <p class="admin-panel-title">Atur deadline kembali dan poin pelanggaran untuk peminjaman ini.</p>
        </div>
    </div>

    <div class="glass-table-card">
        <div class="mb-4">
            <a href="peminjaman.php" class="btn btn-secondary">⬅ Kembali ke Data Peminjaman</a>
        </div>

        <div class="card shadow-sm p-4">
            <div class="mb-4">
                <h5 class="fw-bold">Informasi Peminjaman</h5>
                <p class="mb-1"><strong>ID Peminjaman:</strong> <?php echo $data['id_pinjam']; ?></p>
                <p class="mb-1"><strong>User:</strong> <?php echo htmlspecialchars($data['username']); ?></p>
                <p class="mb-1"><strong>Judul Buku:</strong> <?php echo htmlspecialchars($data['judul']); ?></p>
                <p class="mb-1"><strong>Status:</strong> <?php echo htmlspecialchars($data['status']); ?></p>
            </div>

            <form method="POST">
                <input type="hidden" name="id_pinjam" value="<?php echo $data['id_pinjam']; ?>">

                <div class="mb-3">
                    <label class="form-label">Tanggal Pinjam</label>
                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($data['tanggal_pinjam']); ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Deadline Kembali</label>
                    <input type="date" name="tanggal_deadline" class="form-control" value="<?php echo htmlspecialchars($data['tanggal_deadline']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Poin Pelanggaran / Denda</label>
                    <input type="number" name="denda" class="form-control" min="0" value="<?php echo intval($data['denda']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan Pelanggaran</label>
                    <textarea name="pelanggaran" class="form-control" rows="3"><?php echo htmlspecialchars($data['pelanggaran']); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

</div>

</body>
</html>
