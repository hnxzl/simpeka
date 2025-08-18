<?php
require_once '../init.php';
requireUser();

$error = '';
$success = '';

// Get available positions (only active ones)
try {
    $stmt = $pdo->prepare("SELECT * FROM positions WHERE is_active = 1 ORDER BY title");
    $stmt->execute();
    $positions = $stmt->fetchAll();
} catch(PDOException $e) {
    $positions = [];
}

// Check if position is pre-selected from URL
$selected_position_id = isset($_GET['position']) ? (int)$_GET['position'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $position_id = (int)$_POST['position_id'];
    $address = sanitizeInput($_POST['address']);
    $phone_number = sanitizeInput($_POST['phone_number']);
    
    // Validation
    if (empty($position_id) || empty($address) || empty($phone_number)) {
        $error = 'Semua field harus diisi!';
    } elseif (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] !== UPLOAD_ERR_OK) {
        $error = 'File CV harus diupload!';
    } else {
        // Validate file
        $allowed_types = ['application/pdf'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        $file_type = $_FILES['cv_file']['type'];
        $file_size = $_FILES['cv_file']['size'];
        $file_name = $_FILES['cv_file']['name'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error = 'File harus berformat PDF!';
        } elseif ($file_size > $max_size) {
            $error = 'Ukuran file maksimal 2MB!';
        } else {
            // Create upload directory if not exists
            $upload_dir = '../uploads/cv/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = 'cv_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['cv_file']['tmp_name'], $upload_path)) {
                try {
                    // Check if user already applied for this position
                    $stmt = $pdo->prepare("SELECT id FROM applications WHERE user_id = ? AND position_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $position_id]);
                    
                    if ($stmt->fetch()) {
                        $error = 'Anda sudah melamar untuk posisi ini!';
                        unlink($upload_path); // Delete uploaded file
                    } else {
                        // Save application
                        $stmt = $pdo->prepare("
                            INSERT INTO applications (user_id, position_id, address, phone_number, cv_file, status) 
                            VALUES (?, ?, ?, ?, ?, 'pending')
                        ");
                        
                        if ($stmt->execute([$_SESSION['user_id'], $position_id, $address, $phone_number, $new_filename])) {
                            $success = 'Lamaran berhasil dikirim! Kami akan meninjau lamaran Anda dalam 3-5 hari kerja.';
                        } else {
                            $error = 'Terjadi kesalahan saat menyimpan lamaran.';
                            unlink($upload_path); // Delete uploaded file
                        }
                    }
                } catch(PDOException $e) {
                    $error = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                    unlink($upload_path); // Delete uploaded file
                }
            } else {
                $error = 'Gagal mengupload file CV!';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lamar Kerja - SimPeKa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-coffee me-2"></i>SimPeKa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="apply.php">
                            <i class="fas fa-paper-plane me-1"></i>Lamar Kerja
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../auth/logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h2><i class="fas fa-briefcase me-2 text-primary"></i>Lamar Pekerjaan</h2>
                        <p class="lead mb-0">Isi formulir di bawah untuk melamar posisi yang Anda inginkan</p>
                    </div>
                </div>

                <!-- Application Form -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-form me-2"></i>Formulir Lamaran</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success d-none" id="successAlert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                <div class="mt-3">
                                    <a href="dashboard.php" class="btn btn-primary me-2">
                                        <i class="fas fa-tachometer-alt me-2"></i>Kembali ke Dashboard
                                    </a>
                                    <a href="apply.php" class="btn btn-secondary">
                                        <i class="fas fa-plus me-2"></i>Lamar Posisi Lain
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>

                        <form method="POST" action="" enctype="multipart/form-data" id="applyForm">
                            <!-- Personal Information -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-2"></i>Nama Lengkap
                                    </label>
                                    <input type="text" class="form-control" id="name" 
                                           value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($_SESSION['user_email']); ?>" readonly>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="mb-3">
                                <label for="address" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Alamat Lengkap
                                </label>
                                <textarea class="form-control" id="address" name="address" rows="3" 
                                          placeholder="Masukkan alamat lengkap Anda" required><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                            </div>

                            <!-- Phone Number -->
                            <div class="mb-3">
                                <label for="phone_number" class="form-label">
                                    <i class="fas fa-phone me-2"></i>Nomor Telepon
                                </label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" 
                                       placeholder="Contoh: 08123456789" required 
                                       value="<?php echo isset($_POST['phone_number']) ? htmlspecialchars($_POST['phone_number']) : ''; ?>">
                            </div>

                            <!-- Position -->
                            <div class="mb-3">
                                <label for="position_id" class="form-label">
                                    <i class="fas fa-briefcase me-2"></i>Posisi yang Dilamar
                                </label>
                                <select class="form-control" id="position_id" name="position_id" required>
                                    <option value="">-- Pilih Posisi --</option>
                                    <?php foreach ($positions as $position): ?>
                                        <option value="<?php echo $position['id']; ?>" 
                                                <?php 
                                                $is_selected = false;
                                                if (isset($_POST['position_id']) && $_POST['position_id'] == $position['id']) {
                                                    $is_selected = true;
                                                } elseif (!isset($_POST['position_id']) && $selected_position_id == $position['id']) {
                                                    $is_selected = true;
                                                }
                                                echo $is_selected ? 'selected' : ''; 
                                                ?>>
                                            <?php echo htmlspecialchars($position['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- CV Upload -->
                            <div class="mb-4">
                                <label for="cv_file" class="form-label">
                                    <i class="fas fa-file-pdf me-2"></i>Upload CV (PDF, Maks. 2MB)
                                </label>
                                <div class="file-upload-area" onclick="document.getElementById('cv_file').click()">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    <h5>Drag & Drop file CV Anda di sini</h5>
                                    <p class="text-muted">atau klik untuk memilih file</p>
                                    <small class="text-muted">Format: PDF | Ukuran maksimal: 2MB</small>
                                    <input type="file" class="d-none" id="cv_file" name="cv_file" accept=".pdf" required>
                                </div>
                                <div id="file-info" class="mt-2 text-success" style="display: none;">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <span id="file-name"></span>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left me-2"></i>Kembali
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Kirim Lamaran
                                </button>
                            </div>
                        </form>

                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tips -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips Sukses Melamar</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Pastikan CV Anda up-to-date dan relevan
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Gunakan format PDF untuk CV
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Isi data dengan lengkap dan akurat
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Periksa kembali sebelum mengirim
                                    </li>
                                </ul>
                            </div>
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
                title: 'Oops! Ada Kesalahan',
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
                title: 'Berhasil! 🎉',
                text: '<?php echo addslashes($success); ?>',
                icon: 'success',
                confirmButtonText: 'Kembali ke Dashboard',
                confirmButtonColor: '#734128',
                background: '#fdfce8',
                showConfirmButton: true,
                showCancelButton: true,
                cancelButtonText: 'Lamar Lagi',
                cancelButtonColor: '#6c757d',
                showClass: {
                    popup: 'animate__animated animate__bounceIn'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'dashboard.php';
                } else if (result.isDismissed && result.dismiss === 'cancel') {
                    window.location.href = 'apply.php';
                }
            });
            <?php endif; ?>

            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 200);
            });
        });

        // File upload handling
        const fileInput = document.getElementById('cv_file');
        const fileUploadArea = document.querySelector('.file-upload-area');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.type !== 'application/pdf') {
                    Swal.fire({
                        title: 'Format File Salah',
                        text: 'File harus berformat PDF!',
                        icon: 'warning',
                        confirmButtonColor: '#734128',
                        background: '#fdfce8'
                    });
                    this.value = '';
                    return;
                }

                if (file.size > 2 * 1024 * 1024) {
                    Swal.fire({
                        title: 'File Terlalu Besar',
                        text: 'Ukuran file maksimal 2MB!',
                        icon: 'warning',
                        confirmButtonColor: '#734128',
                        background: '#fdfce8'
                    });
                    this.value = '';
                    return;
                }

                fileName.textContent = file.name;
                fileInfo.style.display = 'block';
                fileUploadArea.style.borderColor = 'var(--kobicha)';
                fileUploadArea.style.backgroundColor = 'var(--lion)';
            }
        });

        // Drag and drop functionality
        fileUploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        fileUploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });

        fileUploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                const event = new Event('change', { bubbles: true });
                fileInput.dispatchEvent(event);
            }
        });

        // Form validation
        document.getElementById('applyForm').addEventListener('submit', function(e) {
            const address = document.getElementById('address').value.trim();
            const phoneNumber = document.getElementById('phone_number').value.trim();
            const positionId = document.getElementById('position_id').value;
            const cvFile = document.getElementById('cv_file').files[0];

            if (!address || !phoneNumber || !positionId || !cvFile) {
                e.preventDefault();
                Swal.fire({
                    title: 'Data Belum Lengkap',
                    text: 'Harap isi semua field dan upload CV!',
                    icon: 'warning',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8'
                });
                return;
            }

            if (cvFile.type !== 'application/pdf') {
                e.preventDefault();
                Swal.fire({
                    title: 'Format File Salah',
                    text: 'File CV harus berformat PDF!',
                    icon: 'warning',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8'
                });
                return;
            }

            if (cvFile.size > 2 * 1024 * 1024) {
                e.preventDefault();
                Swal.fire({
                    title: 'File Terlalu Besar',
                    text: 'Ukuran file CV maksimal 2MB!',
                    icon: 'warning',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8'
                });
                return;
            }

            // Show loading state
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
            submitBtn.disabled = true;

            // Show loading alert
            Swal.fire({
                title: 'Mengirim Lamaran...',
                text: 'Mohon tunggu sebentar',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                background: '#fdfce8',
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });

        // Phone number validation
        document.getElementById('phone_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0 && !value.startsWith('0')) {
                value = '0' + value;
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
