<?php
$username = $_SESSION['user_name'] ?? 'User';
?>

<style>
    .profile-badge {
    transition: background-color 0.2s ease;
}

.profile-badge:hover {
    background-color: #FF00FF !important;
}
</style>

<div class="dropdown">
    <div class="profile-badge d-flex align-items-center gap-2 shadow-sm bg-black text-white px-3 py-2 rounded-2 dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" role="button">
        <i class="bi bi-person-fill"></i> 
        <span><?php echo htmlspecialchars($username); ?></span>
    </div>

    <!-- Menu Dropdown -->
    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
        <li>
            <a class="dropdown-item text-danger d-flex align-items-center gap-2 fw-semibold py-2" href="/capsule-letter/controller/auth/process-logout.php">
                <i class="bi bi-box-arrow-right fs-5"></i> Logout
            </a>
        </li>
    </ul>
</div>