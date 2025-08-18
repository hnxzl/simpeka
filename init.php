<?php
session_start();
require_once 'config/db.php';

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isUser() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'user';
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        redirect('auth/login.php');
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        redirect('public/index.php');
    }
}

function requireUser() {
    requireLogin();
    if (!isUser()) {
        redirect('public/index.php');
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    if (!$date) return 'Tidak tersedia';
    try {
        return date('d M Y, H:i', strtotime($date));
    } catch (Exception $e) {
        return 'Format tanggal tidak valid';
    }
}

function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning text-dark">⏳ Menunggu Review</span>',
        'accepted' => '<span class="badge bg-success">🎉 Diterima</span>',
        'rejected' => '<span class="badge bg-danger">💔 Ditolak</span>'
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
}
?>
