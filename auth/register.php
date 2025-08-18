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
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Semua field harus diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $confirm_password) {
        $error = 'Konfirmasi password tidak cocok!';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Email sudah terdaftar!';
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'user')");
                
                if ($stmt->execute([$name, $email, $hashed_password])) {
                    $success = 'Pendaftaran berhasil! Silakan login untuk melanjutkan.';
                } else {
                    $error = 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.';
                }
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
    <title>Daftar - SimPeKa</title>
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
                <a class="nav-link" href="login.php">Masuk</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card fade-in">
                    <div class="card-header text-center">
                        <h4><i class="fas fa-user-plus me-2"></i>Daftar Akun Baru</h4>
                    </div>
                    <div class="card-body p-4">
                        <!-- Messages handled by SweetAlert2 -->

                        <?php if (!$success): ?>

                        <form method="POST" action="" id="registerForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-2"></i>Nama Lengkap
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       placeholder="Masukkan nama lengkap Anda" required 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="Masukkan email Anda" required 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Minimal 6 karakter" required>
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Password minimal 6 karakter</div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Konfirmasi Password
                                </label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           placeholder="Ulangi password Anda" required>
                                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                            </button>
                        </form>

                        <?php endif; ?>

                        <div class="text-center">
                            <p class="mb-0">Sudah punya akun? 
                                <a href="login.php" class="text-decoration-none fw-bold">Masuk sekarang</a>
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
                title: 'Pendaftaran Gagal',
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
                title: 'Pendaftaran Berhasil! 🎉',
                text: '<?php echo addslashes($success); ?>',
                icon: 'success',
                confirmButtonText: 'Login Sekarang',
                confirmButtonColor: '#734128',
                background: '#fdfce8',
                showClass: {
                    popup: 'animate__animated animate__bounceIn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'login.php';
                }
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

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            const confirmPassword = document.getElementById('confirm_password');
            const icon = this.querySelector('i');
            
            if (confirmPassword.type === 'password') {
                confirmPassword.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                confirmPassword.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (!name || !email || !password || !confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    title: 'Data Belum Lengkap',
                    text: 'Harap isi semua field!',
                    icon: 'warning',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8'
                });
                return;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                Swal.fire({
                    title: 'Password Terlalu Pendek',
                    text: 'Password minimal 6 karakter!',
                    icon: 'warning',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8'
                });
                return;
            }
            
            if (password !== confirmPassword) {
                e.preventDefault();
                Swal.fire({
                    title: 'Password Tidak Cocok',
                    text: 'Konfirmasi password tidak cocok!',
                    icon: 'warning',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8'
                });
                return;
            }
        });

        // Real-time password confirmation check
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Password tidak cocok');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
            }
        });

        // Show SweetAlert2 messages
        <?php if ($error): ?>
        Swal.fire({
            title: 'Registrasi Gagal!',
            text: '<?php echo addslashes($error); ?>',
            icon: 'error',
            confirmButtonColor: '#734128',
            background: '#fdfce8',
            color: '#391e10'
        });
        <?php endif; ?>

        <?php if ($success): ?>
        Swal.fire({
            title: 'Registrasi Berhasil!',
            text: '<?php echo addslashes($success); ?>',
            icon: 'success',
            confirmButtonColor: '#734128',
            background: '#fdfce8',
            color: '#391e10',
            showCancelButton: true,
            confirmButtonText: 'Login Sekarang',
            cancelButtonText: 'Tetap di Sini'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'login.php';
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
