<?php
$userName = $_SESSION['user_name'] ?? 'Pengguna';
?>

<div class="d-flex align-items-center gap-3">
    <div class="profile-badge d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-person-fill"></i> <?php echo $userName; ?>
    </div>
</div>