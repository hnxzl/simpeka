<?php
require_once '../init.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if (isAdmin()) {
        redirect('../admin/dashboard.php');
    } else {
        redirect('../pelamar/dashboard.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    redirect('../admin/dashboard.php');
                } else {
                    redirect('../pelamar/dashboard.php');
                }
            } else {
                $error = 'Email atau password salah!';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SimPeKa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../public/index.php">
                <i class="fas fa-coffee me-2"></i>SimPeKa
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../public/index.php">Beranda</a>
                <a class="nav-link" href="register.php">Daftar</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card fade-in">
                    <div class="card-header text-center">
                        <h4><i class="fas fa-sign-in-alt me-2"></i>Masuk ke Akun Anda</h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Messages handled by SweetAlert2 -->

                        <?php if ($success): ?>
                            <div class="alert alert-success d-none">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Masukkan email Anda" required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Masukkan password Anda" required>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Masuk
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="mb-0">Belum punya akun? 
                                <a href="register.php" class="text-decoration-none fw-bold">Daftar sekarang</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show alerts with SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($error): ?>
            Swal.fire({
                title: 'Login Gagal',
                text: '<?php echo addslashes($error); ?>',
                icon: 'error',
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#734128',
                background: '#fdfce8',
                showClass: {
                    popup: 'animate__animated animate__shakeX'
                }
            });
            <?php elseif ($success): ?>
            Swal.fire({
                title: 'Berhasil!',
                text: '<?php echo addslashes($success); ?>',
                icon: 'success',
                confirmButtonColor: '#734128',
                background: '#fdfce8',
                timer: 2000,
                timerProgressBar: true
            });
            <?php endif; ?>
        });

        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('i');
            
            if (password.type === 'password') {
                password.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                password.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            if (!email || !password) {
                e.preventDefault();
                Swal.fire({
                    title: 'Data Belum Lengkap',
                    text: 'Harap isi semua field!',
                    icon: 'warning',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8'
                });
            }
        });

        // Show SweetAlert2 messages
        <?php if ($error): ?>
        Swal.fire({
            title: 'Login Gagal!',
            text: '<?php echo addslashes($error); ?>',
            icon: 'error',
            confirmButtonColor: '#734128',
            background: '#fdfce8',
            color: '#391e10'
        });
        <?php endif; ?>

        <?php if ($success): ?>
        Swal.fire({
            title: 'Berhasil!',
            text: '<?php echo addslashes($success); ?>',
            icon: 'success',
            confirmButtonColor: '#734128',
            background: '#fdfce8',
            color: '#391e10'
        });
        <?php endif; ?>
    </script>
</body>
</html>
