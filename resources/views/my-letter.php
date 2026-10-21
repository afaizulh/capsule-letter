<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/app.php';

// Cek apakah user sudah login atau belum
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /capsule-letter/resources/views/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];

header('Cache-Control: no-store');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';

    mysqli_set_charset($conn, 'utf8mb4');
    mysqli_query($conn, "SET time_zone = '+07:00'");

    $stmt = mysqli_prepare(
        $conn,
        'SELECT id, title, unlock_date, is_public, created_at
         FROM letters
         WHERE user_id = ?
         ORDER BY created_at DESC, id DESC
         LIMIT 100'
    );

    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $letters = mysqli_fetch_all($result, MYSQLI_ASSOC);

    mysqli_stmt_close($stmt);
} catch (mysqli_sql_exception $error) {
    error_log('My Letter: ' . $error->getMessage());

    http_response_code(500);
    exit('Letters could not be retrieved. Please try again later.');
}

$escapeLetter = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$letterTimezone = new DateTimeZone('Asia/Jakarta');
$letterNow = new DateTimeImmutable('now', $letterTimezone);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>
    <link rel="stylesheet"
        href="/capsule-letter/resources/css/my-letter.css?v=2">
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
        <main class="container-fluid p-4 p-md-5 flex-grow-1"
            id="mainViewContainer">

            <div id="view-my-letter" class="row mb-4">
                <div class="col-12">
                    <h2 class="fw-bold mb-2">My Letters</h2>

                    <p class="text-muted">
                        List of letters you have created for your future self.
                    </p>

                    <div id="letterList" class="list-group mt-4">

                        <?php if (empty($letters)): ?>

                            <div class="border rounded-3 p-4 text-secondary">
                                There are no letters yet. You can create a new letter by clicking the "Create Letter" button.
                            </div>

                        <?php else: ?>

                            <?php foreach ($letters as $letter): ?>
                                <?php
                                $unlockDate = new DateTimeImmutable(
                                    $letter['unlock_date'],
                                    $letterTimezone
                                );

                                $createdDate = new DateTimeImmutable(
                                    $letter['created_at'],
                                    $letterTimezone
                                );

                                $isOpen = $letterNow >= $unlockDate;
                                ?>

                                <article class="letter-card list-group-item
                                                p-4 border rounded-3 mb-3">

                                    <div class="d-flex flex-wrap
                                                justify-content-between
                                                align-items-start gap-2">

                                        <h3 class="h5 mb-1 fw-bold text-break">
                                            <?= $escapeLetter($letter['title']) ?>
                                        </h3>

                                        <small class="letter-status fw-semibold
                                            <?= $isOpen
                                                ? 'text-success'
                                                : 'text-danger' ?>">

                                            <?= $isOpen ? 'Ready to open' : 'Locked' ?>
                                        </small>
                                    </div>

                                    <p class="mb-2">
                                        <?= $isOpen
                                            ? 'This letter is now open for reading.'
                                            : 'The content of this letter will be available after the opening time.' ?>
                                    </p>

                                    <small class="text-secondary">
                                        Created:
                                        <?= $escapeLetter(
                                            $createdDate->format('d/m/Y')
                                        ) ?>

                                        &bull;

                                        <?= (int) $letter['is_public'] === 1
                                            ? 'Public'
                                            : 'Private' ?>
                                    </small>

                                    <div class="letter-actions d-flex justify-content-end gap-2 mt-3">
                                        <button type="button"
                                            class="letter-icon btn btn-dark rounded-3"
                                            data-letter-id="<?= $escapeLetter($letter['id']) ?>"
                                            data-action="update"
                                            title="Update letter"
                                            aria-label="Update letter">
                                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                        </button>

                                        <button type="button"
                                            class="letter-icon btn btn-dark rounded-3"
                                            data-letter-id="<?= $escapeLetter($letter['id']) ?>"
                                            data-action="detail"
                                            title="Detail letter"
                                            aria-label="Detail letter">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </button>

                                        <button type="button"
                                            class="letter-icon btn btn-dark rounded-3"
                                            data-letter-id="<?= $escapeLetter($letter['id']) ?>"
                                            data-action="delete"
                                            title="Delete letter"
                                            aria-label="Delete letter">
                                            <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </article>

                            <?php endforeach; ?>

                        <?php endif; ?>

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