<?php
require_once '../init.php';
requireAdmin();

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        try {
            if ($action === 'create') {
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $requirements = sanitizeInput($_POST['requirements']);
                
                if (empty($title) || empty($description) || empty($requirements)) {
                    $error = 'Semua field harus diisi!';
                } else {
                    $stmt = $pdo->prepare("INSERT INTO positions (title, description, requirements) VALUES (?, ?, ?)");
                    $stmt->execute([$title, $description, $requirements]);
                    $message = 'Posisi berhasil ditambahkan!';
                }
                
            } elseif ($action === 'update') {
                $id = (int)$_POST['id'];
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $requirements = sanitizeInput($_POST['requirements']);
                $is_active = isset($_POST['is_active']) ? 1 : 0;
                
                if (empty($title) || empty($description) || empty($requirements)) {
                    $error = 'Semua field harus diisi!';
                } else {
                    $stmt = $pdo->prepare("UPDATE positions SET title = ?, description = ?, requirements = ?, is_active = ? WHERE id = ?");
                    $stmt->execute([$title, $description, $requirements, $is_active, $id]);
                    $message = 'Posisi berhasil diupdate!';
                }
                
            } elseif ($action === 'delete') {
                $id = (int)$_POST['id'];
                
                // Check if position has applications
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE position_id = ?");
                $stmt->execute([$id]);
                $app_count = $stmt->fetch()['count'];
                
                if ($app_count > 0) {
                    $error = 'Tidak dapat menghapus posisi yang sudah memiliki lamaran!';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM positions WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Posisi berhasil dihapus!';
                }
                
            } elseif ($action === 'toggle_status') {
                $id = (int)$_POST['id'];
                $stmt = $pdo->prepare("UPDATE positions SET is_active = NOT is_active WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Status posisi berhasil diubah!';
            }
        } catch(PDOException $e) {
            $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        }
    }
}

// Get all positions
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               COUNT(a.id) as application_count 
        FROM positions p 
        LEFT JOIN applications a ON p.id = a.position_id 
        GROUP BY p.id 
        ORDER BY p.id DESC
    ");
    $stmt->execute();
    $positions = $stmt->fetchAll();
} catch(PDOException $e) {
    $positions = [];
    $error = 'Error mengambil data posisi: ' . $e->getMessage();
}

// Get single position for editing
$edit_position = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM positions WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_position = $stmt->fetch();
    } catch(PDOException $e) {
        $edit_position = null;
    }
}
?>

<?php
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Posisi - SimPeKa</title>
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
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pelamar.php">
                            <i class="fas fa-users me-1"></i>Kelola Pelamar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="positions.php">
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
                                    <i class="fas fa-briefcase me-2 text-primary"></i>
                                    Kelola Posisi Pekerjaan
                                </h2>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                                    <i class="fas fa-plus me-2"></i>Tambah Posisi Baru
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Messages - Handled by SweetAlert2 -->

        <!-- Positions Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Daftar Posisi
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($positions)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-briefcase fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada posisi</h5>
                                <p class="text-muted mb-4">Tambahkan posisi pekerjaan pertama untuk memulai proses rekrutmen</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPositionModal">
                                    <i class="fas fa-plus me-2"></i>Tambah Posisi Pertama
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Posisi</th>
                                            <th>Deskripsi</th>
                                            <th>Status</th>
                                            <th>Lamaran</th>
                                            <th>Tanggal Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($positions as $position): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($position['title']); ?></strong>
                                            </td>
                                            <td>
                                                <div style="max-width: 300px;">
                                                    <?php echo htmlspecialchars(substr($position['description'], 0, 100)); ?>
                                                    <?php if (strlen($position['description']) > 100): ?>...<?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($position['is_active']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Aktif
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-pause me-1"></i>Nonaktif
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo $position['application_count']; ?> lamaran
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <?php 
                                                    $date_str = isset($position['created_at']) ? $position['created_at'] : null;
                                                    echo formatDate($date_str);
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-info" 
                                                            onclick="viewPosition(<?php echo $position['id']; ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <a href="positions.php?edit=<?php echo $position['id']; ?>" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm <?php echo $position['is_active'] ? 'btn-secondary' : 'btn-success'; ?>" 
                                                            onclick="toggleStatus(<?php echo $position['id']; ?>)">
                                                        <i class="fas fa-<?php echo $position['is_active'] ? 'pause' : 'play'; ?>"></i>
                                                    </button>
                                                    <?php if ($position['application_count'] == 0): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="deletePosition(<?php echo $position['id']; ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
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

    <!-- Add Position Modal -->
    <div class="modal fade" id="addPositionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus me-2"></i>Tambah Posisi Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">
                                <i class="fas fa-briefcase me-2"></i>Judul Posisi
                            </label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   placeholder="Contoh: Senior Frontend Developer" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-file-text me-2"></i>Deskripsi Pekerjaan
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Jelaskan tanggung jawab, tugas, dan lingkup pekerjaan..." required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="requirements" class="form-label">
                                <i class="fas fa-list-check me-2"></i>Kualifikasi & Requirements
                            </label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="4" 
                                      placeholder="Tuliskan kualifikasi yang dibutuhkan, satu per baris..." required></textarea>
                            <div class="form-text">Tip: Gunakan enter untuk membuat baris baru</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Simpan Posisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Position Modal -->
    <?php if ($edit_position): ?>
    <div class="modal fade show" id="editPositionModal" tabindex="-1" style="display: block;">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Edit Posisi
                    </h5>
                    <a href="positions.php" class="btn-close"></a>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?php echo $edit_position['id']; ?>">
                        
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">
                                <i class="fas fa-briefcase me-2"></i>Judul Posisi
                            </label>
                            <input type="text" class="form-control" id="edit_title" name="title" 
                                   value="<?php echo htmlspecialchars($edit_position['title']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">
                                <i class="fas fa-file-text me-2"></i>Deskripsi Pekerjaan
                            </label>
                            <textarea class="form-control" id="edit_description" name="description" rows="4" required><?php echo htmlspecialchars($edit_position['description']); ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_requirements" class="form-label">
                                <i class="fas fa-list-check me-2"></i>Kualifikasi & Requirements
                            </label>
                            <textarea class="form-control" id="edit_requirements" name="requirements" rows="4" required><?php echo htmlspecialchars($edit_position['requirements']); ?></textarea>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   <?php echo $edit_position['is_active'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_active">
                                <i class="fas fa-toggle-on me-2"></i>Posisi Aktif
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="positions.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Posisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <!-- Hidden Forms -->
    <form id="toggleForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="toggle_status">
        <input type="hidden" id="toggle_id" name="id">
    </form>

    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" id="delete_id" name="id">
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

        <?php if ($error): ?>
        Swal.fire({
            title: 'Error!',
            text: '<?php echo addslashes($error); ?>',
            icon: 'error',
            confirmButtonColor: '#734128',
            background: '#fdfce8',
            color: '#391e10'
        });
        <?php endif; ?>

        function toggleStatus(id) {
            Swal.fire({
                title: 'Ubah Status Posisi?',
                text: 'Apakah Anda yakin ingin mengubah status posisi ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#734128',
                cancelButtonColor: '#c7a07a',
                confirmButtonText: 'Ya, Ubah!',
                cancelButtonText: 'Batal',
                background: '#fdfce8',
                color: '#391e10'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('toggle_id').value = id;
                    document.getElementById('toggleForm').submit();
                }
            });
        }

        function deletePosition(id) {
            Swal.fire({
                title: 'Hapus Posisi?',
                text: 'Apakah Anda yakin ingin menghapus posisi ini? Aksi ini tidak dapat dibatalkan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#c7a07a',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                background: '#fdfce8',
                color: '#391e10'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete_id').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }

        function viewPosition(id) {
            // Redirect to view position details (will be implemented later)
            window.open('../public/index.php#positions', '_blank');
        }

        // Auto-resize textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });
        });
    </script>
</body>
</html>
