<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<title>Register - Perpustakaan Gamifikasi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="../assets/style.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.register-card {
    background: rgba(255, 255, 255, 0.92);
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}
.register-header {
    text-align: center;
    margin-bottom: 30px;
}
.register-header i {
    font-size: 3rem;
    color: #667eea;
    margin-bottom: 10px;
}
.register-header h3 {
    color: #333;
    font-weight: 600;
}
.form-control {
    border-radius: 10px;
    border: 1px solid #ddd;
    padding: 12px 15px;
    margin-bottom: 15px;
}
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}
.btn-register {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 12px;
    font-weight: 600;
    transition: transform 0.2s;
}
.btn-register:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}
</style>
</head>
<body>

<div class="container">
<div class="row justify-content-center">
<div class="col-md-6 col-lg-4">
<div class="card register-card p-4">
<div class="register-header">
<i class="fas fa-user-plus"></i>
<h3>Daftar Akun Baru</h3>
<p class="text-muted">Buat akun untuk mulai meminjam buku dan kumpulkan poin.</p>
</div>

<form method="POST" action="proses_register.php">
<div class="mb-3">
<input type="text" name="username" class="form-control" placeholder="Username" required>
</div>
<div class="mb-3">
<input type="password" name="password" class="form-control" placeholder="Password" required>
</div>
<div class="mb-3">
<input type="password" name="confirm_password" class="form-control" placeholder="Konfirmasi Password" required>
</div>
<button type="submit" class="btn btn-primary w-100 btn-register">Register</button>
</form>

<?php if(isset($_GET['success'])) { ?>
<div class="alert alert-success mt-3" role="alert">
Registrasi berhasil! Silakan login.
</div>
<?php } ?>
<?php if(isset($_GET['error'])) { ?>
<div class="alert alert-danger mt-3" role="alert">
<?php echo htmlspecialchars($_GET['error']); ?>
</div>
<?php } ?>

<div class="text-center mt-3">
<p class="mb-0">Sudah punya akun? <a href="login.php">Login di sini</a></p>
</div>

</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
