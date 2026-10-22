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

<div class="d-flex align-items-center gap-3">
    <div class="profile-badge d-flex align-items-center gap-2 shadow-sm bg-black text-white">
        <i class="bi bi-person-fill"></i> <?php echo $username ?>
    </div>
</div>