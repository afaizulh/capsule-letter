<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/app.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/sidebar.php'; ?>

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">

        <!-- Top Navigation / Header -->
        <header class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <span class="text-uppercase text-secondary fw-semibold" id="topBadge" style="font-size: 12px; letter-spacing: 1.5px;">CREATE</span>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/profile-badge.php'; ?>
        </header>

        <!-- Dynamic Content View Container -->
        <main class="container-fluid p-4 p-md-5 flex-grow-1" id="mainViewContainer">
            Blank Page
        </main>

        <!-- Footer -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/scripts.php'; ?>
</body>

</html>