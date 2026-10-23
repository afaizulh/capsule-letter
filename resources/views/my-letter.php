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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = $_SESSION['user_id'];

$updateSuccess = $_SESSION['update_success'] ?? null;
$updateError = $_SESSION['update_error'] ?? null;

$deleteSuccess = $_SESSION['delete_success'] ?? null;
$deleteError = $_SESSION['delete_error'] ?? null;

unset(
    $_SESSION['update_success'],
    $_SESSION['update_error'],
    $_SESSION['delete_success'],
    $_SESSION['delete_error']
);

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
        href="/capsule-letter/resources/css/my-letter.css?v=4">
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
                    
                    <!-- UPDATE ALERT -->
                    <?php if ($updateSuccess): ?>
                        <div class="alert alert-success alert-dismissible fade show"
                            role="alert">
                            <?= $escapeLetter($updateSuccess) ?>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($updateError): ?>
                        <div class="alert alert-danger alert-dismissible fade show"
                            role="alert">
                            <?= $escapeLetter($updateError) ?>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <!-- END UPDATE ALERT -->
                                        
                    <!-- DELETE ALERT -->
                    <?php if ($deleteSuccess): ?>
                        <div class="alert alert-success alert-dismissible fade show"
                            role="alert">
                            <?= $escapeLetter($deleteSuccess) ?>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($deleteError): ?>
                        <div class="alert alert-danger alert-dismissible fade show"
                            role="alert">
                            <?= $escapeLetter($deleteError) ?>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <!-- END DELETE ALERT -->

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

                                $isOpen =   $letterNow >= $unlockDate;

                                // Batas Update: 3 hari
                                $editDeadline = $createdDate->modify('+3 days');
                                $canUpdate = $letterNow < $editDeadline;

                                // Batas Delete: 7 hari
                                $deleteDeadline = $createdDate->modify('+7 days');
                                $canDelete = $letterNow < $deleteDeadline;

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

                                            <?php if ($canUpdate): ?>
                                                <button type="button"
                                                    class="letter-icon btn btn-dark rounded-3"
                                                    data-letter-id="<?= (int) $letter['id'] ?>"
                                                    data-action="update"
                                                    onclick="openUpdateModal(<?= (int) $letter['id'] ?>)"
                                                    title="Update letter"
                                                    aria-label="Update letter">
                                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                                </button>
                                            <?php endif; ?>

                                            
                                            <button type="button"
                                                class="letter-icon btn btn-dark rounded-3"
                                                data-letter-id="<?= (int) $letter['id'] ?>"
                                                data-action="detail"
                                                onclick="openDetailLetter(<?= (int) $letter['id'] ?>)"
                                                title="Detail letter"
                                                aria-label="Detail letter">
                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                            </button>


                                            <?php if ($canDelete): ?>
                                                <button type="button"
                                                    class="letter-icon btn btn-dark rounded-3"
                                                    data-letter-id="<?= (int) $letter['id'] ?>"
                                                    data-action="delete"
                                                    onclick="openDeleteModal(<?= (int) $letter['id'] ?>)"
                                                    title="Delete letter"
                                                    aria-label="Delete letter">
                                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                                </button>
                                            <?php endif; ?>

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
    
    <!-- Update Confirmation Modal -->
    <div class="update-confirm-overlay" id="updateConfirmModal"
        role="dialog" aria-modal="true"
        aria-labelledby="updateConfirmTitle"
        aria-describedby="updateConfirmDescription"
        hidden>

        <div class="update-confirm-box">
            <div class="update-confirm-icon">
                <i class="bi bi-pencil-square"></i>
            </div>

            <h2 id="updateConfirmTitle">Update Letter?</h2>

            <p id="updateConfirmDescription">
                Do you want to continue to the letter editor?
            </p>

            <div class="update-confirm-actions">
                <button type="button" class="confirm-cancel"
                        id="cancelUpdate">
                    Cancel
                </button>

                <button type="button" class="confirm-continue"
                        id="continueUpdate">
                    Continue
                </button>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="update-confirm-overlay"
        id="deleteConfirmModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="deleteConfirmTitle"
        aria-describedby="deleteConfirmDescription"
        hidden>

        <div class="update-confirm-box">
            <div class="update-confirm-icon">
                <i class="bi bi-trash"></i>
            </div>

            <h2 id="deleteConfirmTitle">Delete Letter?</h2>

            <p id="deleteConfirmDescription">
                Are you sure you want to delete this letter?
                This action cannot be undone.
            </p>

            <div class="update-confirm-actions">
                <button type="button"
                        class="confirm-cancel"
                        id="cancelDelete">
                    Cancel
                </button>

                <button type="button"
                        class="confirm-continue"
                        id="continueDelete">
                    Continue
                </button>
            </div>
        </div>
    </div>
    <!-- End Delete Confirmation Modal -->
    
    <form
        id="deleteLetterForm"
        action="<?= $escapeLetter(
            $baseUrl . '/controller/delete-letter/process-delete.php'
        ) ?>"
        method="POST"
        hidden>

        <input
            type="hidden"
            name="id"
            id="deleteLetterId">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= $escapeLetter($_SESSION['csrf_token']) ?>">
    </form>

    <script>
        let selectedLetterId = null;

        const updateModal = document.getElementById('updateConfirmModal');
        const cancelUpdate = document.getElementById('cancelUpdate');
        const deleteModal = document.getElementById('deleteConfirmModal');
        const cancelDelete = document.getElementById('cancelDelete');
        const continueDelete = document.getElementById('continueDelete');

        let selectedDeleteId = null;

        function openDeleteModal(letterId) {
            selectedDeleteId = letterId;
            deleteModal.hidden = false;
            document.body.style.overflow = 'hidden';
            cancelDelete.focus();
        }

        function closeDeleteModal() {
            deleteModal.hidden = true;
            document.body.style.overflow = '';
            selectedDeleteId = null;
        }

        cancelDelete.addEventListener('click', closeDeleteModal);
        continueDelete.addEventListener('click', function () {
            if (!selectedDeleteId) return;

            document.getElementById('deleteLetterId').value =
                selectedDeleteId;

            document.getElementById('deleteLetterForm').submit();
        });

        function openUpdateModal(letterId) {
            selectedLetterId = letterId;
            updateModal.hidden = false;
            document.body.style.overflow = 'hidden';
            cancelUpdate.focus();
        }

        function closeUpdateModal() {
            updateModal.hidden = true;
            document.body.style.overflow = '';
            selectedLetterId = null;
        }

        cancelUpdate.addEventListener('click', closeUpdateModal);

        continueUpdate.addEventListener('click', function () {
            if (!selectedLetterId) return;

            window.location.href =
                '<?= $baseUrl ?>/resources/views/update-letter.php?id='
                + encodeURIComponent(selectedLetterId);
        });

        function openDetailLetter(letterId) {
            window.location.href =
                '<?= $baseUrl ?>/resources/views/detail-letter.php?id='
                + encodeURIComponent(letterId);
        }
    </script>

</body>

</html>