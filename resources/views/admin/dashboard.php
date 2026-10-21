<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/app.php';
$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

// Proteksi Hak Akses: Hanya Admin
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = 'Access denied. This page is only for administrators.';
    header('Location: /capsule-letter/resources/views/home.php');
    exit;
}

// Ambil Data Categories & Forbidden Words
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
$words = mysqli_query($conn, "SELECT * FROM forbidden_words ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>
    <title>Admin Dashboard - Capsule Letter</title>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/sidebar.php'; ?>

    <div class="main-content">

        <!-- Top Header -->
        <header class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <span class="badge bg-danger text-uppercase px-3 py-2" style="letter-spacing: 1.5px;">ADMIN PANEL</span>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/profile-badge.php'; ?>
        </header>

        <main class="container-fluid p-4 p-md-5">

            <!-- Alert Notifikasi -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="mb-4">
                <h1 class="display-6 fw-bold">Admin Management</h1>
                <p class="text-muted">Manage Letter Categories and Forbidden Word Filters (Content Moderation)</p>
            </div>

            <div class="row g-4">
                <!-- PANEL 1: KELOLA KATEGORI -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <h4 class="fw-bold mb-3"><i class="bi bi-tags me-2"></i> Letter Categories</h4>

                        <!-- Form Tambah Kategori -->
                        <form action="/capsule-letter/controller/admin/process-category.php?action=add" method="POST" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="name" class="form-control" placeholder="New Category Name" required>
                                <button type="submit" class="btn btn-dark">+ Add</button>
                            </div>
                        </form>

                        <!-- Tabel Kategori -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Category Name</th>
                                        <th>Slug</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    while ($c = mysqli_fetch_assoc($categories)): ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td class="fw-bold"><?= htmlspecialchars($c['name']); ?></td>
                                            <td><code><?= htmlspecialchars($c['slug']); ?></code></td>
                                            <td class="text-center">
                                                <a href="/capsule-letter/controller/admin/process-category.php?action=delete&id=<?= $c['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PANEL 2: KELOLA KATA TERLARANG -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm p-4 h-100">
                        <h4 class="fw-bold mb-3"><i class="bi bi-shield-x me-2"></i> Forbidden Words</h4>

                        <!-- Form Tambah Kata Terlarang -->
                        <form action="/capsule-letter/controller/admin/process-word.php?action=add" method="POST" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="word" class="form-control" placeholder="New forbidden word" required>
                                <button type="submit" class="btn btn-danger">+ Add Word</button>
                            </div>
                        </form>

                        <!-- List Badge Kata Terlarang -->
                        <div class="d-flex flex-wrap gap-2">
                            <?php while ($w = mysqli_fetch_assoc($words)): ?>
                                <span class="badge bg-light text-dark border p-2 d-flex align-items-center gap-2">
                                    <span><?= htmlspecialchars($w['word']); ?></span>
                                    <a href="/capsule-letter/controller/admin/process-word.php?action=delete&id=<?= $w['id']; ?>" class="text-danger text-decoration-none fw-bold" onclick="return confirm('Are you sure you want to delete this forbidden word?')">&times;</a>
                                </span>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/scripts.php'; ?>
</body>

</html>