<?php
require_once '../init.php';

// Get active positions from database
try {
    $stmt = $pdo->prepare("SELECT id, title, description, requirements, is_active, created_at 
                          FROM positions WHERE is_active = 1 ORDER BY created_at DESC");
    $stmt->execute();
    $positions = $stmt->fetchAll();
    
    // Debug: Check if positions exist
    if (empty($positions)) {
        // Try to get all positions to see if there are any
        $debug_stmt = $pdo->prepare("SELECT id, title, is_active FROM positions");
        $debug_stmt->execute();
        $all_positions = $debug_stmt->fetchAll();
        
        // If no positions exist at all, we'll show a message
        $no_positions_message = empty($all_positions) ? 
            "Belum ada posisi yang tersedia. Silakan hubungi admin." : 
            "Tidak ada posisi yang aktif saat ini. Total posisi: " . count($all_positions);
    }
} catch(PDOException $e) {
    $positions = [];
    $error_message = "Error mengambil data posisi: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimPeKa - Sistem Informasi Penerimaan Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coffee me-2"></i>SimPeKa
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#positions">Posisi</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- Logged in user navigation -->
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../admin/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard Admin
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="../pelamar/dashboard.php">
                                    <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="../pelamar/apply.php">
                                    <i class="fas fa-paper-plane me-1"></i>Lamar Kerja
                                </a>
                            </li>
                        <?php endif; ?>
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
                    <?php else: ?>
                        <!-- Guest navigation -->
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/login.php">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../auth/register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="fade-in">Wujudkan Karir Impianmu</h1>
                    <p class="fade-in">Platform rekrutmen modern yang menghubungkan talenta terbaik dengan perusahaan impian. Bergabunglah dengan ribuan profesional yang telah memulai perjalanan karir mereka bersama kami.</p>
                    <div class="fade-in">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Logged in user buttons -->
                            <?php if (isAdmin()): ?>
                                <a href="../admin/dashboard.php" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin
                                </a>
                                <a href="../admin/pelamar.php" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-users me-2"></i>Kelola Pelamar
                                </a>
                            <?php else: ?>
                                <a href="../pelamar/apply.php" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-paper-plane me-2"></i>Lamar Sekarang
                                </a>
                                <a href="../pelamar/dashboard.php" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard Saya
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- Guest buttons -->
                            <a href="../auth/register.php" class="btn btn-primary btn-lg me-3">
                                <i class="fas fa-rocket me-2"></i>Mulai Sekarang
                            </a>
                            <a href="#about" class="btn btn-outline-light btn-lg">
                                <i class="fas fa-info-circle me-2"></i>Pelajari Lebih Lanjut
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="mb-4">Mengapa Memilih SimPeKa?</h2>
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-friends fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>Proses yang Transparan</h5>
                                    <p>Pantau status lamaran Anda secara real-time dengan update yang jelas dan transparan.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>Respons Cepat</h5>
                                    <p>Tim HRD kami berkomitmen memberikan feedback dalam waktu 3-5 hari kerja.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-mobile-alt fa-2x text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5>Mobile-Friendly</h5>
                                    <p>Akses platform dari mana saja, kapan saja dengan tampilan yang responsif.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body text-center p-5">
                            <i class="fas fa-chart-line fa-4x text-primary mb-4"></i>
                            <h4>Bergabung dengan 1000+ Profesional</h4>
                            <p>Yang telah memulai perjalanan karir mereka bersama perusahaan-perusahaan terpercaya melalui platform kami.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Available Positions -->
    <section id="positions" class="py-5" style="background-color: var(--dun);">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2>Posisi yang Tersedia</h2>
                    <p class="lead">Temukan peluang karir yang sesuai dengan passion dan keahlian Anda</p>
                </div>
            </div>
            <div class="row g-4">
                <?php if (!empty($error_message)): ?>
                    <div class="col-12">
                        <div class="alert alert-warning" style="background-color: var(--lion); border-color: var(--kobicha); color: var(--bistre);">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                        </div>
                    </div>
                <?php elseif (empty($positions)): ?>
                    <div class="col-12 text-center">
                        <div class="card">
                            <div class="card-body p-5">
                                <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted"><?php echo $no_positions_message ?? 'Belum ada posisi tersedia'; ?></h5>
                                <p class="text-muted">Posisi pekerjaan akan segera tersedia. Pantau terus halaman ini!</p>
                                <?php if (isset($_SESSION['user_id']) && isAdmin()): ?>
                                    <a href="../admin/positions.php" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus me-2"></i>Tambah Posisi Baru
                                    </a>
                                <?php elseif (!isset($_SESSION['user_id'])): ?>
                                    <a href="../auth/register.php" class="btn btn-outline-primary mt-3">
                                        <i class="fas fa-user-plus me-2"></i>Daftar untuk Update
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php 
                    $icons = [
                        'Frontend Developer' => 'fas fa-code',
                        'Backend Developer' => 'fas fa-server', 
                        'UI/UX Designer' => 'fas fa-paint-brush',
                        'Digital Marketing' => 'fas fa-bullhorn',
                        'Content Creator' => 'fas fa-pen-fancy',
                        'Data Analyst' => 'fas fa-chart-bar',
                        'Project Manager' => 'fas fa-tasks',
                        'Quality Assurance' => 'fas fa-check-circle'
                    ];
                    ?>
                    <?php foreach ($positions as $position): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 position-card" data-position-id="<?php echo $position['id']; ?>">
                            <div class="card-body d-flex flex-column">
                                <div class="text-center mb-3">
                                    <i class="<?php echo $icons[$position['title']] ?? 'fas fa-briefcase'; ?> fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title"><?php echo htmlspecialchars($position['title']); ?></h5>
                                </div>
                                <p class="card-text flex-grow-1">
                                    <?php echo htmlspecialchars(substr($position['description'], 0, 120)); ?>
                                    <?php if (strlen($position['description']) > 120): ?>...<?php endif; ?>
                                </p>
                                <div class="mt-auto">
                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 mb-2" 
                                            onclick="showPositionDetails(<?php echo $position['id']; ?>)"
                                            style="border-color: var(--kobicha); color: var(--kobicha);">
                                        <i class="fas fa-info-circle me-1"></i>Lihat Detail
                                    </button>
                                    <?php if (isset($_SESSION['user_id']) && !isAdmin()): ?>
                                        <a href="../pelamar/apply.php?position=<?php echo $position['id']; ?>" 
                                           class="btn btn-primary btn-sm w-100">
                                            <i class="fas fa-paper-plane me-1"></i>Lamar Posisi Ini
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="text-center mt-5">
                <?php if (isset($_SESSION['user_id']) && !isAdmin()): ?>
                    <!-- For logged in users (non-admin) -->
                    <a href="../pelamar/apply.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Lamar Sekarang
                    </a>
                <?php elseif (isset($_SESSION['user_id']) && isAdmin()): ?>
                    <!-- For admin users -->
                    <a href="../admin/pelamar.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-users me-2"></i>Kelola Pelamar
                    </a>
                <?php else: ?>
                    <!-- For guests -->
                    <a href="../auth/register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-right me-2"></i>Mulai Melamar Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4" style="background-color: var(--bistre); color: var(--ivory);">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-coffee me-2"></i>SimPeKa</h5>
                    <p>Platform rekrutmen modern untuk generasi masa depan.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 SimPeKa. All rights reserved.</p>
                    <p>Made with <i class="fas fa-heart text-danger"></i> for better recruitment experience.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Position Details Modal -->
    <div class="modal fade" id="positionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="positionModalTitle">
                        <i class="fas fa-briefcase me-2"></i>Detail Posisi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="positionModalContent">
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p>Memuat detail posisi...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <div id="positionModalActions"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Position data for modal
        const positions = <?php echo json_encode($positions); ?>;

        function showPositionDetails(positionId) {
            const position = positions.find(p => p.id == positionId);
            if (!position) return;

            // Update modal title
            document.getElementById('positionModalTitle').innerHTML = 
                `<i class="fas fa-briefcase me-2"></i>${position.title}`;

            // Update modal content
            const content = `
                <div class="position-detail">
                    <div class="mb-4">
                        <h6><i class="fas fa-info-circle me-2 text-primary"></i>Deskripsi Pekerjaan</h6>
                        <p class="text-muted">${position.description}</p>
                    </div>
                    
                    <div class="mb-4">
                        <h6><i class="fas fa-list-check me-2 text-primary"></i>Kualifikasi & Requirements</h6>
                        <div class="requirements-list">
                            ${position.requirements.split('\\n').map(req => 
                                req.trim() ? `<div class="requirement-item mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <span>${req.trim()}</span>
                                </div>` : ''
                            ).join('')}
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Posting Date:</strong> ${position.created_at ? formatDate(position.created_at) : 'Tidak tersedia'}
                    </div>
                </div>
            `;
            
            document.getElementById('positionModalContent').innerHTML = content;

            // Update modal actions
            const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
            const isAdmin = <?php echo isset($_SESSION['user_id']) && isAdmin() ? 'true' : 'false'; ?>;
            
            let actions = '';
            if (isLoggedIn && !isAdmin) {
                actions = `<a href="../pelamar/apply.php?position=${positionId}" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Lamar Posisi Ini
                </a>`;
            } else if (!isLoggedIn) {
                actions = `<a href="../auth/register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-2"></i>Daftar untuk Melamar
                </a>`;
            }
            
            document.getElementById('positionModalActions').innerHTML = actions;

            // Show modal
            new bootstrap.Modal(document.getElementById('positionModal')).show();
        }

        function formatDate(dateString) {
            if (!dateString) return 'Tidak tersedia';
            try {
                const options = { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                };
                return new Date(dateString).toLocaleDateString('id-ID', options);
            } catch (e) {
                return 'Format tanggal tidak valid';
            }
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('slide-up');
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.card').forEach(card => {
            observer.observe(card);
        });

        // Add hover effect to position cards
        document.querySelectorAll('.position-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'transform 0.3s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>
