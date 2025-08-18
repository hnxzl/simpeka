<?php
require_once '../init.php';
requireUser();

// Get user's application data
try {
    $stmt = $pdo->prepare("
        SELECT a.*, p.title as position_title 
        FROM applications a 
        LEFT JOIN positions p ON a.position_id = p.id 
        WHERE a.user_id = ? 
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $applications = $stmt->fetchAll();
    
    // Get latest application for dashboard stats
    $latest_application = !empty($applications) ? $applications[0] : null;
    
} catch(PDOException $e) {
    $applications = [];
    $latest_application = null;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelamar - SimPeKa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="apply.php">
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
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">
                                    <i class="fas fa-hand-wave me-2 text-warning"></i>
                                    Selamat datang kembali, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!
                                </h2>
                                <p class="lead mb-0">
                                    <?php if ($latest_application): ?>
                                        Status lamaran terakhir Anda: <?php echo getStatusBadge($latest_application['status']); ?>
                                    <?php else: ?>
                                        Siap untuk memulai perjalanan karir baru? Mari mulai dengan melamar posisi yang sesuai dengan passion Anda!
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <a href="apply.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-rocket me-2"></i>Lamar Sekarang
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($applications); ?></div>
                    <div class="stat-label">Total Lamaran</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $pending = array_filter($applications, fn($app) => $app['status'] === 'pending');
                        echo count($pending);
                        ?>
                    </div>
                    <div class="stat-label">Menunggu Review</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-number">
                        <?php 
                        $accepted = array_filter($applications, fn($app) => $app['status'] === 'accepted');
                        echo count($accepted);
                        ?>
                    </div>
                    <div class="stat-label">Diterima</div>
                </div>
            </div>
        </div>

        <!-- Applications History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Riwayat Lamaran
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($applications)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada lamaran</h5>
                                <p class="text-muted mb-4">Anda belum pernah melamar pekerjaan. Mari mulai perjalanan karir Anda!</p>
                                <a href="apply.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Buat Lamaran Pertama
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Posisi</th>
                                            <th>Tanggal Lamar</th>
                                            <th>Status</th>
                                            <th>CV</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($applications as $app): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($app['position_title']); ?></strong>
                                            </td>
                                            <td>
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo formatDate($app['created_at']); ?>
                                            </td>
                                            <td>
                                                <?php echo getStatusBadge($app['status']); ?>
                                            </td>
                                            <td>
                                                <a href="../uploads/cv/<?php echo htmlspecialchars($app['cv_file']); ?>" 
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-pdf me-1"></i>Lihat CV
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($app['status'] === 'rejected' && !empty($app['reason'])): ?>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            onclick="showReasonModal(<?php echo htmlspecialchars(json_encode($app['reason']), ENT_QUOTES, 'UTF-8'); ?>)"
                                                            title="Klik untuk melihat alasan penolakan">
                                                        <i class="fas fa-info-circle me-1"></i>Lihat Alasan
                                                    </button>
                                                    
                                                    <!-- Fallback for users without JavaScript -->
                                                    <noscript>
                                                        <div class="mt-2 p-2 border rounded bg-light">
                                                            <small><strong>Alasan Penolakan:</strong></small><br>
                                                            <small><?php echo nl2br(htmlspecialchars($app['reason'])); ?></small>
                                                        </div>
                                                    </noscript>
                                                    
                                                <?php elseif ($app['status'] === 'accepted'): ?>
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>Selamat!
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>Sedang direview
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <?php if (!empty($applications)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-3">Aksi Cepat</h5>
                        <a href="apply.php" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-2"></i>Lamar Posisi Lain
                        </a>
                        <a href="../public/index.php" class="btn btn-secondary">
                            <i class="fas fa-eye me-2"></i>Lihat Posisi Tersedia
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log('Dashboard script loaded');
        
        // Wait for DOM and SweetAlert2 to be ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded');
            
            // Check if SweetAlert2 is available
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 not loaded!');
            } else {
                console.log('SweetAlert2 loaded successfully');
            }
            
            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.card, .stat-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 100);
            });
        });
        
        // Test if SweetAlert2 is loaded
        function testSweetAlert() {
            console.log('testSweetAlert called');
            if (typeof Swal === 'undefined') {
                alert('SweetAlert2 tidak terbaca!');
                return;
            }
            
            Swal.fire({
                title: 'Test Berhasil!',
                text: 'SweetAlert2 berfungsi dengan baik',
                icon: 'success',
                confirmButtonColor: '#734128',
                background: '#fdfce8',
                color: '#391e10'
            });
        }

        function showReasonModal(reason) {
            console.log('showReasonModal called with:', reason);
            console.log('Reason type:', typeof reason);
            
            // Check if SweetAlert2 is available
            if (typeof Swal === 'undefined') {
                console.error('SweetAlert2 not available');
                alert('SweetAlert2 belum dimuat. Alasan: ' + reason);
                return;
            }
            
            // Check if reason is valid
            if (!reason || reason === null || reason === undefined || reason === '') {
                console.error('No reason provided');
                Swal.fire({
                    title: 'Peringatan',
                    text: 'Tidak ada alasan penolakan yang tersedia.',
                    icon: 'warning',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8',
                    color: '#391e10'
                });
                return;
            }
            
            try {
                // Format reason text with line breaks
                const formattedReason = String(reason).replace(/\n/g, '<br>');
                console.log('Formatted reason:', formattedReason);
                
                Swal.fire({
                    title: '<i class="fas fa-info-circle me-2"></i>Alasan Penolakan',
                    html: `<div style="text-align: left; padding: 15px; line-height: 1.6; font-size: 14px;">${formattedReason}</div>`,
                    icon: 'info',
                    confirmButtonColor: '#734128',
                    confirmButtonText: 'Tutup',
                    background: '#fdfce8',
                    color: '#391e10',
                    width: '500px',
                    customClass: {
                        popup: 'rounded-3'
                    }
                });
            } catch (error) {
                console.error('Error in showReasonModal:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Terjadi kesalahan saat menampilkan alasan: ' + error.message,
                    icon: 'error',
                    confirmButtonColor: '#734128',
                    background: '#fdfce8',
                    color: '#391e10'
                });
            }
        }

        // Auto-refresh status every 5 minutes to check for updates
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
