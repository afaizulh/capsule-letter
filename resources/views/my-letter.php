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
            <span class="text-uppercase text-secondary fw-semibold" id="topBadge" style="font-size: 12px; letter-spacing: 1.5px;">MY LETTER</span>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/profile-badge.php'; ?>
        </header>

        <!-- Dynamic Content View Container -->
        <main class="container-fluid p-4 p-md-5 flex-grow-1" id="mainViewContainer">

            <!-- MY LETTER VIEW -->
            <div id="view-my-letter" class="d-none">
                <div class="py-4">
                    <h2 class="fw-bold mb-3">My Letters</h2>
                    <p class="text-muted">List of letters you have created for your future self.</p>
                    <div class="list-group mt-4">
                        <div class="list-group-item list-group-item-action p-4 border rounded-3 mb-3">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1 fw-bold">Myself</h5>
                                <small class="text-danger fw-semibold">Locked until 15 Okt 2026</small>
                            </div>
                            <p class="mb-1 text-muted text-truncate">Dear gua, Jangan lupa dunia cuman sementara yakk...</p>
                            <small class="text-secondary">Created: Today • Private</small>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/scripts.php'; ?>
</body>

</html>