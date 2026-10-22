<link rel="stylesheet" href="../css/sidebar.css">

<!-- Mobile Header Toggle -->
<div class="d-lg-none p-3 border-bottom d-flex justify-content-between align-items-center bg-white sticky-top">
    <h5 class="m-0 fw-bold">CAPSULE LETTER<span class="brand-dot"></span></h5>
    <button class="btn btn-outline-dark btn-sm" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
</div>

<!-- SIDEBAR -->
<nav id="sidebar" class="sidebar d-flex flex-column justify-content-between p-4">
    <div>
        <!-- Logo Desktop -->
        <div class="d-none d-lg-block mb-5">
            <h5 class="fw-bold m-0 tracking-wide">CAPSULE LETTER<span class="brand-dot"></span></h5>
        </div>

        <div class="text-uppercase text-muted fw-bold my-4" style="font-size: 11px; letter-spacing: 1px;">Workspace</div>

        <div class="nav flex-column">
            <a href="<?= $baseUrl; ?>/resources/views/home.php" class="nav-link-custom <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">
                <i class="bi bi-house fs-5 me-3"></i> Home
            </a>
            <a href="<?= $baseUrl; ?>/resources/views/create.php" class="nav-link-custom <?= basename($_SERVER['PHP_SELF']) == 'create.php' ? 'active' : '' ?>">
                <i class="bi bi-pencil fs-5 me-3"></i> Create
            </a>
            <a href="<?= $baseUrl; ?>/resources/views/my-letter.php" class="nav-link-custom <?= basename($_SERVER['PHP_SELF']) == 'my-letter.php' ? 'active' : '' ?>">
                <i class="bi bi-envelope-paper fs-5 me-3"></i> My Letter
            </a>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="text-uppercase text-danger fw-bold my-2" style="font-size: 10px; letter-spacing: 1px;">Administrator</div>
                <a href="<?= $baseUrl; ?>/resources/views/admin/dashboard.php" class="nav-link-custom <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="bi bi-speedometer2 fs-5 me-3 text-danger"></i> Admin Panel
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sidebar Footer Action -->
    <div class="pt-4 border-top">
        <a href="#" class="sidebar-pill w-100 d-flex align-items-center justify-content-center gap-2 text-decoration-none">
            <span>Today</span> <i class="bi bi-arrow-right"></i> <span>Someday</span>
        </a>
    </div>
</nav>