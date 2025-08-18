<?php
require_once '../init.php';
requireAdmin();

// Get statistics
try {
    // Total applications
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications");
    $stmt->execute();
    $total_applications = $stmt->fetch()['total'];
    
    // Pending applications
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications WHERE status = 'pending'");
    $stmt->execute();
    $pending_applications = $stmt->fetch()['total'];
    
    // Accepted applications
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM applications WHERE status = 'accepted'");
    $stmt->execute();
    $accepted_applications = $stmt->fetch()['total'];
    
    // Recent applications
    $stmt = $pdo->prepare("
        SELECT a.*, u.name as user_name, p.title as position_title 
        FROM applications a 
        JOIN users u ON a.user_id = u.id 
        JOIN positions p ON a.position_id = p.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recent_applications = $stmt->fetchAll();
    
    // Applications by position
    $stmt = $pdo->prepare("
        SELECT p.title, COUNT(a.id) as count
        FROM positions p 
        LEFT JOIN applications a ON p.id = a.position_id 
        GROUP BY p.id, p.title
        ORDER BY count DESC
    ");
    $stmt->execute();
    $position_stats = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $total_applications = 0;
    $pending_applications = 0;
    $accepted_applications = 0;
    $recent_applications = [];
    $position_stats = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SimPeKa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pelamar.php">
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
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">
                                    <i class="fas fa-chart-line me-2 text-primary"></i>
                                    Dashboard Admin SimPeKa
                                </h2>
                                <p class="lead mb-0">Kelola proses rekrutmen dengan efisien dan transparan</p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php 
                                    date_default_timezone_set('Asia/Jakarta');
                                    echo date('d M Y, H:i'); 
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_applications; ?></div>
                    <div class="stat-label">Total Lamaran</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending_applications; ?></div>
                    <div class="stat-label">Menunggu Review</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $accepted_applications; ?></div>
                    <div class="stat-label">Diterima</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_applications - $accepted_applications; ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Applications -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2"></i>Lamaran Terbaru
                        </h5>
                        <a href="pelamar.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye me-1"></i>Lihat Semua
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_applications)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">Belum ada lamaran</h6>
                                <p class="text-muted mb-0">Lamaran yang masuk akan muncul di sini</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Pelamar</th>
                                            <th>Posisi</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_applications as $app): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($app['user_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($app['position_title']); ?></td>
                                            <td>
                                                <small><?php echo formatDate($app['created_at']); ?></small>
                                            </td>
                                            <td>
                                                <?php echo getStatusBadge($app['status']); ?>
                                            </td>
                                            <td>
                                                <a href="pelamar.php#app-<?php echo $app['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>Detail
                                                </a>
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

            <!-- Position Statistics -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Statistik per Posisi
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($position_stats)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-pie fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">Belum ada data</h6>
                            </div>
                        <?php else: ?>
                            <div style="height: 200px; position: relative;">
                                <canvas id="positionChart"></canvas>
                            </div>
                            <div class="mt-3" style="max-height: 200px; overflow-y: auto;">
                                <?php 
                                $displayedStats = array_slice($position_stats, 0, 8); // Limit to 8 positions
                                foreach ($displayedStats as $stat): 
                                ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-truncate me-2" style="max-width: 150px;" title="<?php echo htmlspecialchars($stat['title']); ?>">
                                        <?php echo htmlspecialchars($stat['title']); ?>
                                    </span>
                                    <span class="badge bg-primary"><?php echo $stat['count']; ?></span>
                                </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($position_stats) > 8): ?>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        +<?php echo count($position_stats) - 8; ?> posisi lainnya
                                    </small>
                                    <br>
                                    <a href="positions.php" class="btn btn-sm btn-outline-primary mt-1">
                                        <i class="fas fa-eye me-1"></i>Lihat Semua
                                    </a>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="mb-3">Aksi Cepat</h5>
                        <a href="pelamar.php" class="btn btn-primary me-2">
                            <i class="fas fa-users me-2"></i>Kelola Pelamar
                        </a>
                        <a href="positions.php" class="btn btn-success me-2">
                            <i class="fas fa-briefcase me-2"></i>Kelola Posisi
                        </a>
                        <a href="pelamar.php?status=pending" class="btn btn-warning me-2">
                            <i class="fas fa-clock me-2"></i>Review Pending
                        </a>
                        <a href="../public/index.php" class="btn btn-secondary">
                            <i class="fas fa-eye me-2"></i>Lihat Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Position Statistics Chart
        <?php if (!empty($position_stats)): ?>
        const ctx = document.getElementById('positionChart').getContext('2d');
        const positionChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php foreach ($position_stats as $stat): ?>
                    '<?php echo addslashes($stat['title']); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($position_stats as $stat): ?>
                        <?php echo $stat['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        '#734128', '#C7A07A', '#E2CEB1', '#391E10', '#8B4513',
                        '#A0522D', '#CD853F', '#DEB887'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                layout: {
                    padding: 10
                }
            }
        });
        <?php endif; ?>

        // Add fade-in animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card, .stat-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.classList.add('fade-in');
                }, index * 100);
            });
        });

        // Auto-refresh every 2 minutes
        setInterval(function() {
            location.reload();
        }, 120000);
    </script>
</body>
</html>
