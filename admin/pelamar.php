<?php
require_once '../init.php';
requireAdmin();

$message = '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $application_id = (int)$_POST['application_id'];
    $action = $_POST['action'];
    $reason = isset($_POST['reason']) ? sanitizeInput($_POST['reason']) : '';
    
    try {
        if ($action === 'accept') {
            $stmt = $pdo->prepare("UPDATE applications SET status = 'accepted', reason = NULL WHERE id = ?");
            $stmt->execute([$application_id]);
            $message = 'Lamaran berhasil diterima!';
        } elseif ($action === 'reject') {
            if (empty($reason)) {
                $message = 'Alasan penolakan harus diisi!';
            } else {
                $stmt = $pdo->prepare("UPDATE applications SET status = 'rejected', reason = ? WHERE id = ?");
                $stmt->execute([$reason, $application_id]);
                $message = 'Lamaran berhasil ditolak!';
            }
        }
    } catch(PDOException $e) {
        $message = 'Terjadi kesalahan sistem.';
    }
}

// Get applications with filters
try {
    $sql = "
        SELECT a.*, u.name as user_name, u.email as user_email, p.title as position_title 
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN positions p ON a.position_id = p.id
    ";
    
    $params = [];
    if ($filter_status) {
        $sql .= " WHERE a.status = ?";
        $params[] = $filter_status;
    }
    
    $sql .= " ORDER BY a.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $applications = [];
}

// Get status counts for filter buttons
try {
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM applications GROUP BY status");
    $stmt->execute();
    $status_counts = [];
    while ($row = $stmt->fetch()) {
        $status_counts[$row['status']] = $row['count'];
    }
} catch(PDOException $e) {
    $status_counts = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pelamar - SimPeKa</title>
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
                <i class="fas fa-coffee me-2"></i>SimPeKa Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="pelamar.php">
                            <i class="fas fa-users me-1"></i>Kelola Pelamar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="positions.php">
                            <i class="fas fa-briefcase me-1"></i>Kelola Posisi
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($_SESSION['user_name']); ?>
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
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h2 class="mb-0">
                                    <i class="fas fa-users me-2 text-primary"></i>
                                    Kelola Pelamar
                                </h2>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <span class="text-muted">
                                    Total: <?php echo count($applications); ?> lamaran
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message - Handled by SweetAlert2 -->

        <!-- Filter Buttons -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <a href="pelamar.php" class="btn <?php echo $filter_status === '' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                <i class="fas fa-list me-1"></i>Semua
                                <span class="badge bg-light text-dark ms-1"><?php echo count($applications); ?></span>
                            </a>
                            <a href="pelamar.php?status=pending" class="btn <?php echo $filter_status === 'pending' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                                <i class="fas fa-clock me-1"></i>Pending
                                <span class="badge bg-light text-dark ms-1"><?php echo $status_counts['pending'] ?? 0; ?></span>
                            </a>
                            <a href="pelamar.php?status=accepted" class="btn <?php echo $filter_status === 'accepted' ? 'btn-success' : 'btn-outline-success'; ?>">
                                <i class="fas fa-check me-1"></i>Diterima
                                <span class="badge bg-light text-dark ms-1"><?php echo $status_counts['accepted'] ?? 0; ?></span>
                            </a>
                            <a href="pelamar.php?status=rejected" class="btn <?php echo $filter_status === 'rejected' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                                <i class="fas fa-times me-1"></i>Ditolak
                                <span class="badge bg-light text-dark ms-1"><?php echo $status_counts['rejected'] ?? 0; ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>Daftar Lamaran
                            <?php if ($filter_status): ?>
                                - Status: <?php echo ucfirst($filter_status); ?>
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($applications)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ada lamaran</h5>
                                <p class="text-muted mb-0">
                                    <?php if ($filter_status): ?>
                                        Tidak ada lamaran dengan status "<?php echo $filter_status; ?>"
                                    <?php else: ?>
                                        Belum ada lamaran yang masuk
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Pelamar</th>
                                            <th>Posisi</th>
                                            <th>Kontak</th>
                                            <th>Tanggal Lamar</th>
                                            <th>Status</th>
                                            <th>CV</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($applications as $app): ?>
                                        <tr id="app-<?php echo $app['id']; ?>">
                                            <td>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($app['user_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($app['user_email']); ?></small>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($app['position_title']); ?></strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <small>
                                                        <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($app['phone_number']); ?><br>
                                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars(substr($app['address'], 0, 30)); ?>...
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?php echo formatDate($app['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo getStatusBadge($app['status']); ?>
                                            </td>
                                            <td>
                                                <a href="../uploads/cv/<?php echo htmlspecialchars($app['cv_file']); ?>" 
                                                   target="_blank" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-file-pdf me-1"></i>Lihat
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($app['status'] === 'pending'): ?>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-success" 
                                                                onclick="acceptApplication(<?php echo $app['id']; ?>)">
                                                            <i class="fas fa-check me-1"></i>Terima
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="showRejectModal(<?php echo $app['id']; ?>)">
                                                            <i class="fas fa-times me-1"></i>Tolak
                                                        </button>
                                                    </div>
                                                <?php elseif ($app['status'] === 'rejected' && $app['reason']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="showReasonModal(<?php echo json_encode($app['reason']); ?>)">
                                                        <i class="fas fa-info-circle me-1"></i>Alasan
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted">
                                                        <i class="fas fa-check-circle me-1"></i>Selesai
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
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Lamaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" id="reject_application_id" name="application_id">
                        <input type="hidden" name="action" value="reject">
                        
                        <div class="mb-3">
                            <label for="reason" class="form-label">
                                <i class="fas fa-comment-alt me-2"></i>Alasan Penolakan
                            </label>
                            <textarea class="form-control" id="reason" name="reason" rows="4" 
                                      placeholder="Berikan alasan konstruktif untuk penolakan..." required></textarea>
                            <div class="form-text">Berikan feedback yang membantu untuk pengembangan kandidat</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Tolak Lamaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Accept Form (Hidden) -->
    <form id="acceptForm" method="POST" action="" style="display: none;">
        <input type="hidden" id="accept_application_id" name="application_id">
        <input type="hidden" name="action" value="accept">
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show SweetAlert2 messages
        <?php if ($message): ?>
        Swal.fire({
            title: 'Berhasil!',
            text: '<?php echo addslashes($message); ?>',
            icon: 'success',
            confirmButtonColor: '#734128',
            background: '#fdfce8',
            color: '#391e10'
        });
        <?php endif; ?>

        function acceptApplication(applicationId) {
            Swal.fire({
                title: 'Terima Lamaran?',
                text: 'Apakah Anda yakin ingin menerima lamaran ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#c7a07a',
                confirmButtonText: 'Ya, Terima!',
                cancelButtonText: 'Batal',
                background: '#fdfce8',
                color: '#391e10'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('accept_application_id').value = applicationId;
                    document.getElementById('acceptForm').submit();
                }
            });
        }

        function showRejectModal(applicationId) {
            document.getElementById('reject_application_id').value = applicationId;
            document.getElementById('reason').value = '';
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }

        function showReasonModal(reason) {
            // Format reason text with line breaks
            const formattedReason = reason.replace(/\n/g, '<br>');
            
            Swal.fire({
                title: '<i class="fas fa-info-circle me-2"></i>Alasan Penolakan',
                html: `<div style="text-align: left; padding: 10px;">${formattedReason}</div>`,
                icon: 'info',
                confirmButtonColor: '#734128',
                confirmButtonText: 'Tutup',
                background: '#fdfce8',
                color: '#391e10',
                customClass: {
                    title: 'swal-title-left'
                }
            });
        }

        // Add fade-in animation to table rows
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach((row, index) => {
                setTimeout(() => {
                    row.classList.add('fade-in');
                }, index * 50);
            });
        });

        // Auto-refresh every 2 minutes for pending applications
        <?php if ($filter_status === 'pending' || $filter_status === ''): ?>
        setInterval(function() {
            location.reload();
        }, 120000);
        <?php endif; ?>
    </script>
</body>
</html>
