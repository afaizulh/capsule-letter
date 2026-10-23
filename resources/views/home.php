
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT']
    . '/capsule-letter/config/app.php';

if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    header('Location: ' . $baseUrl
        . '/resources/views/auth/login.php');
    exit;
}

$userId = (int) ($_SESSION['user_id'] ?? 0);

if ($userId <= 0) {
    header('Location: ' . $baseUrl
        . '/resources/views/auth/login.php');
    exit;
}

$conn = require $_SERVER['DOCUMENT_ROOT']
    . '/capsule-letter/config/database.php';

mysqli_set_charset($conn, 'utf8mb4');
mysqli_query($conn, "SET time_zone = '+07:00'");

$timezone = new DateTimeZone('Asia/Jakarta');
$now = new DateTimeImmutable('now', $timezone);
$nowSql = $now->format('Y-m-d H:i:s');

$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

// Statistik surat pengguna.
$stmt = mysqli_prepare(
    $conn,
    "SELECT
        COUNT(*) AS total,
        COALESCE(SUM(unlock_date > ?), 0) AS locked,
        COALESCE(SUM(unlock_date <= ?), 0) AS unlocked
     FROM letters
     WHERE user_id = ?"
);

mysqli_stmt_bind_param($stmt, 'ssi', $nowSql, $nowSql, $userId);
mysqli_stmt_execute($stmt);

$stats = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

 // Public Letters Feed: 20 surat terbaru yang sudah terbuka.
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, content, unlock_date
     FROM letters
     WHERE is_public = 1
       AND unlock_date <= ?
     ORDER BY unlock_date DESC, id DESC
     LIMIT 20"
);

mysqli_stmt_bind_param($stmt, 's', $nowSql);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$publicLetters = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);

// Tiga surat yang paling dekat waktu pembukaannya.
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, unlock_date
     FROM letters
     WHERE user_id = ? AND unlock_date > ?
     ORDER BY unlock_date ASC
     LIMIT 3"
);

mysqli_stmt_bind_param($stmt, 'is', $userId, $nowSql);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

 // My Letters: semua surat milik user yang login.
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, title, created_at, unlock_date
     FROM letters
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$recentLetters = mysqli_fetch_all($result, MYSQLI_ASSOC);

mysqli_stmt_close($stmt);


mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>
</head>

<link rel="stylesheet" href="<?= $escape($baseUrl . '/resources/css/public-letter-feed.css') ?>">

<link rel="stylesheet" href="<?= $escape($baseUrl . '/resources/css/home-my-letters.css') ?>">

<body>

    <!-- SIDEBAR -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/sidebar.php'; ?>

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">

        <!-- Top Navigation / Header -->
        <header class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <span class="text-uppercase text-secondary fw-semibold" id="topBadge" style="font-size: 12px; letter-spacing: 1.5px;">HOME</span>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/profile-badge.php'; ?>
        </header>

        <!-- Dynamic Content View Container -->
        <main class="container-fluid p-4 p-md-5 flex-grow-1"
            id="mainViewContainer">

            <!-- Dashboard Heading -->
            <section class="mb-5">
                <p class="text-uppercase text-secondary small mb-2"
                style="letter-spacing: 1.5px;">
                    YOUR PERSONAL SPACE
                </p>

                <!-- <div class="d-none d-lg-block mb-5"> -->
                    <h1 class="fw-bold m-0 tracking-wide">CAPSULE LETTER<span class="brand-dot"></span></h1>
                <!-- </div> -->
                <!-- <h1 class="fw-bold mb-2">Home Dashboard</h1> -->

                <p class="text-secondary mb-4">
                    Every letter holds a moment worth remembering.
                </p>

                <a href="<?= $escape($baseUrl . '/resources/views/create.php') ?>"
                class="btn btn-dark px-4 py-2">
                    <i class="bi bi-plus-lg me-2"></i>
                    Create New Letter
                </a>
            </section>
            
            <!-- Public Letter Feed -->
            <section class="public-feed-section mb-5">

                <div class="mb-3">
                    <h2 class="h5 fw-bold mb-1">Public Letters</h2>
                    <p class="text-secondary small mb-0">
                        Little stories shared with the world.
                    </p>
                </div>

                <?php if (empty($publicLetters)): ?>

                    <p class="text-secondary py-3">
                        No public letters are available yet.
                    </p>

                <?php else: ?>

                    <div class="public-feed-viewport">
                        <div class="public-feed-track">

                            <?php for ($copy = 0; $copy < 2; $copy++): ?>
   
                            <div
                                class="public-feed-group"
                                <?= $copy === 1 ? 'aria-hidden="true"' : '' ?>
                            >

                                    <?php foreach ($publicLetters as $letter): ?>

                                        <?php
                                            $plainContent = html_entity_decode(
                                                strip_tags($letter['content']),
                                                ENT_QUOTES | ENT_HTML5,
                                                'UTF-8'
                                            );

                                            $plainContent = trim(
                                                preg_replace(
                                                    '/\s+/u',
                                                    ' ',
                                                    $plainContent
                                                )
                                            );

                                            $preview = mb_substr(
                                                $plainContent,
                                                0,
                                                280,
                                                'UTF-8'
                                            );

                                            if (
                                                mb_strlen($plainContent, 'UTF-8') > 280
                                            ) {
                                                $preview .= '...';
                                            }
                                        ?>

                                        <article class="public-letter-card">

                                            <p class="public-letter-author">
                                                by Anonymous
                                            </p>

                                            <h3 class="public-letter-title">
                                                <?= $escape($letter['title']) ?>
                                            </h3>

                                            <p class="public-letter-preview">
                                                <?= $escape($preview) ?>
                                            </p>

                                            <a
                                                class="public-letter-link"
                                                href="<?= $escape(
                                                    $baseUrl
                                                    . '/resources/views/detail-letter.php?id='
                                                    . (int) $letter['id']
                                                ) ?>"
                                                tabindex="<?= $copy === 1 ? '-1' : '0' ?>"
                                            >
                                                Read Letter
                                                <i class="bi bi-arrow-up-right ms-1"></i>
                                            </a>

                                        </article>

                                    <?php endforeach; ?>

                                </div>

                            <?php endfor; ?>

                        </div>
                    </div>

                <?php endif; ?>

            </section>
            <!-- End Public Letter Feed -->

            <!-- My Letters -->
            <section class="my-letters-section mb-5">

                <div class="d-flex justify-content-between align-items-center
                            mb-3 pb-3 border-bottom">
                    <h2 class="h5 fw-bold mb-0">My Letters</h2>

                    <a href="<?= $escape(
                        $baseUrl . '/resources/views/my-letter.php'
                    ) ?>"
                    class="text-decoration-none small text-dark">
                        View all <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>

                <?php if (empty($recentLetters)): ?>

                    <p class="text-secondary py-3">
                        You haven't created any letters yet.
                        Start by writing your first letter.
                    </p>

                <?php else: ?>

                    <div class="my-letters-viewport">
                        <div class="my-letters-list">

                            <?php foreach ($recentLetters as $letter): ?>

                                <?php
                                    $letterUnlockDate = new DateTimeImmutable(
                                        $letter['unlock_date'],
                                        $timezone
                                    );

                                    $letterCreatedDate = new DateTimeImmutable(
                                        $letter['created_at'],
                                        $timezone
                                    );

                                    $letterIsOpen = $now >= $letterUnlockDate;
                                ?>

                                <a href="<?= $escape(
                                    $baseUrl
                                    . '/resources/views/detail-letter.php?id='
                                    . (int) $letter['id']
                                ) ?>"
                                class="my-letter-item text-decoration-none text-dark">

                                    <div class="row align-items-center g-2">

                                        <div class="col-auto">
                                            <i class="bi bi-envelope fs-4"
                                            style="color: #FF4500;"></i>
                                        </div>

                                        <div class="col">
                                            <h3 class="h6 fw-semibold mb-1 text-break">
                                                <?= $escape($letter['title']) ?>
                                            </h3>

                                            <p class="small text-secondary mb-0">
                                                Created
                                                <?= $escape(
                                                    $letterCreatedDate->format('d M Y')
                                                ) ?>
                                            </p>
                                        </div>

                                        <div class="col-auto text-end">
                                            <span class="small <?= $letterIsOpen
                                                ? 'text-success'
                                                : 'text-secondary' ?>">

                                                <i class="bi <?= $letterIsOpen
                                                    ? 'bi-unlock'
                                                    : 'bi-lock' ?> me-1"></i>

                                                <?= $letterIsOpen
                                                    ? 'Unlocked'
                                                    : 'Locked' ?>
                                            </span>

                                            <p class="small text-secondary mb-0 mt-1">
                                                <?= $escape(
                                                    $letterUnlockDate->format('d M Y')
                                                ) ?>
                                            </p>
                                        </div>

                                    </div>

                                </a>

                            <?php endforeach; ?>

                        </div>
                    </div>

                <?php endif; ?>

            </section>
            <!-- End My Letters -->

        </main>

        <!-- Footer -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/scripts.php'; ?>
</body>

</html>