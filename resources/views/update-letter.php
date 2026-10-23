<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once $_SERVER['DOCUMENT_ROOT']
    . '/capsule-letter/config/app.php';

// 1. Authentication
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true
) {
    header('Location: /capsule-letter/resources/views/auth/login.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$letterId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($userId <= 0 || !$letterId || $letterId <= 0) {
    http_response_code(400);
    exit('Invalid letter request.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // 2. Database connection
    $conn = require_once $_SERVER['DOCUMENT_ROOT']
        . '/capsule-letter/config/database.php';

    mysqli_set_charset($conn, 'utf8mb4');
    mysqli_query($conn, "SET time_zone = '+07:00'");

    // 3. Retrieve letter belonging to logged-in user
    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, user_id, category_id, title, content,
                attachment, unlock_date, is_public, created_at
         FROM letters
         WHERE id = ? AND user_id = ?
         LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 'ii', $letterId, $userId);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $letter = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    if (!$letter) {
        http_response_code(404);
        exit('Letter not found.');
    }

    // 4. Check the original 3-day editing deadline
    $timezone = new DateTimeZone('Asia/Jakarta');
    $now = new DateTimeImmutable('now', $timezone);

    $createdAt = new DateTimeImmutable(
        $letter['created_at'],
        $timezone
    );

    $editDeadline = $createdAt->modify('+3 days');
    $canUpdate = $now < $editDeadline;

    if (!$canUpdate) {
        $_SESSION['error'] =
            'The editing period for this letter has expired.';

        header(
            'Location: ' . $baseUrl
                . '/resources/views/my-letter.php'
        );
        exit;
    }

    // 5. Retrieve categories
    $categoriesResult = mysqli_query(
        $conn,
        'SELECT id, name FROM categories ORDER BY name'
    );

    $categories = mysqli_fetch_all(
        $categoriesResult,
        MYSQLI_ASSOC
    );
} catch (mysqli_sql_exception $error) {
    error_log('Update Letter: ' . $error->getMessage());

    http_response_code(500);
    exit('Unable to load letter. Please try again later.');
}

// 6. HTML escaping
$escape = static function ($value): string {
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
};

$attachment = (string) ($letter['attachment'] ?? '');
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>

    <title>Update Letter | Capsule Letter</title>

    <link rel="stylesheet" href="<?= $escape($baseUrl) ?>/resources/css/update-letter.css">

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"
        referrerpolicy="origin">
    </script>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/sidebar.php'; ?>

    <div class="main-content">

        <!-- HEADER -->
        <header class="p-4 border-bottom d-flex
                       justify-content-between align-items-center bg-white">

            <span class="text-uppercase text-secondary fw-semibold"
                style="font-size: 12px; letter-spacing: 1.5px;">
                UPDATE LETTER
            </span>

            <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/profile-badge.php'; ?>

        </header>

        <main class="container-fluid p-4 p-md-5 flex-grow-1">

            <!-- PAGE INTRODUCTION -->
            <div class="update-heading mb-4">
                <h1 class="display-5 fw-bold mb-2">
                    Update Your Letter.
                </h1>

                <p class="text-muted mb-0">
                    Make changes to your letter before the editing
                    period expires.
                </p>
            </div>

            <!-- UPDATE FORM -->
            <form
                id="updateLetterForm"
                action="<?= $escape(
                            $baseUrl
                                . '/controller/update-letter/process-update.php'
                        ) ?>"
                method="POST"
                enctype="multipart/form-data">

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $letter['id'] ?>">

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= $escape($_SESSION['csrf_token']) ?>">

                <div class="row g-5">

                    <!-- LEFT COLUMN -->
                    <div class="col-lg-7 col-xl-8">

                        <!-- TITLE -->
                        <div class="mb-4">
                            <label for="title"
                                class="form-label fw-bold">
                                Letter Title
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                id="title"
                                name="title"
                                value="<?= $escape($letter['title']) ?>"
                                maxlength="255"
                                placeholder="Input Title"
                                required>
                        </div>

                        <!-- CATEGORY -->
                        <div class="mb-4">
                            <label for="category_id"
                                class="form-label fw-bold">
                                Category (Optional)
                            </label>

                            <select
                                class="form-select"
                                id="category_id"
                                name="category_id">

                                <option value="">
                                    Choose Category
                                </option>

                                <?php foreach ($categories as $category): ?>
                                    <option
                                        value="<?= (int) $category['id'] ?>"
                                        <?= (string) ($letter['category_id'] ?? '')
                                            === (string) $category['id']
                                            ? 'selected'
                                            : '' ?>>

                                        <?= $escape($category['name']) ?>

                                    </option>
                                <?php endforeach; ?>

                            </select>
                        </div>

                        <!-- CONTENT -->
                        <div class="mb-4">
                            <label for="content"
                                class="form-label fw-bold">
                                Content of Letter
                            </label>

                            <textarea
                                class="form-control"
                                id="content"
                                name="content"
                                rows="12"
                                placeholder="Input Content"
                                required><?= $escape($letter['content']) ?></textarea>
                        </div>

                        <!-- ATTACHMENT -->
                        <div class="mb-4">
                            <label for="attachment"
                                class="form-label fw-bold">
                                Attachment
                            </label>

                            <?php if ($attachment !== ''): ?>
                                <div class="current-attachment mb-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bi bi-paperclip fs-3"></i>

                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">
                                                Current Attachment
                                            </div>

                                            <div class="small text-muted
                                                        text-break">
                                                <?= $escape(basename($attachment)) ?>
                                            </div>

                                            <div class="small text-muted mt-1">
                                                Upload a new file to replace
                                                the existing attachment.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="small text-muted">
                                    No attachment has been added.
                                </p>
                            <?php endif; ?>

                            <input
                                type="file"
                                class="form-control"
                                id="attachment"
                                name="attachment"
                                accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">

                            <div class="form-text">
                                JPG, JPEG, PNG, PDF, DOC, DOCX.
                                Maximum size: 2 MB.
                            </div>
                        </div>

                        <!-- DEADLINE INFORMATION -->
                        <div class="deadline-notice">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-clock-history fs-4"></i>

                                <div>
                                    <div class="fw-bold">
                                        Editing Period
                                    </div>

                                    <div class="small">
                                        You can edit this letter for
                                        3 days after creation.
                                    </div>

                                    <div class="small mt-1">
                                        Deadline:
                                        <?= $escape(
                                            $editDeadline->format('d/m/Y H:i')
                                        ) ?> WIB
                                    </div>

                                    <div class="small mt-1">
                                        Updating does not reset the deadline.
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-lg-5 col-xl-4">

                        <div class="update-settings">

                            <!-- OPENING DATE -->
                            <div class="mb-4">
                                <label for="unlock_date"
                                    class="form-label fw-bold">
                                    Opening Date
                                </label>

                                <input
                                    type="date"
                                    class="form-control"
                                    id="unlock_date"
                                    name="unlock_date"
                                    value="<?= $escape(
                                                substr(
                                                    (string) $letter['unlock_date'],
                                                    0,
                                                    10
                                                )
                                            ) ?>"
                                    required>

                                <div class="form-text">
                                    The letter remains locked until
                                    its opening date.
                                </div>
                            </div>

                            <!-- VISIBILITY -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    Method
                                </label>

                                <!-- PRIVATE -->
                                <div
                                    class="method-card p-3 mb-3
                                    <?= (int) $letter['is_public'] === 0
                                        ? 'active'
                                        : '' ?>"
                                    id="cardPrivate"
                                    role="button"
                                    tabindex="0"
                                    aria-pressed="<?= (int) $letter['is_public'] === 0
                                                        ? 'true'
                                                        : 'false' ?>">

                                    <div class="d-flex align-items-center gap-3">
                                        <input
                                            class="form-check-input mt-0"
                                            type="radio"
                                            name="is_public"
                                            value="0"
                                            id="radioPrivate"
                                            <?= (int) $letter['is_public'] === 0
                                                ? 'checked'
                                                : '' ?>>

                                        <i class="bi bi-lock fs-4"></i>

                                        <span class="fw-bold">
                                            Private
                                        </span>
                                    </div>
                                </div>

                                <!-- PUBLIC -->
                                <div
                                    class="method-card p-3
                                    <?= (int) $letter['is_public'] === 1
                                        ? 'active'
                                        : '' ?>"
                                    id="cardPublic"
                                    role="button"
                                    tabindex="0"
                                    aria-pressed="<?= (int) $letter['is_public'] === 1
                                                        ? 'true'
                                                        : 'false' ?>">

                                    <div class="d-flex align-items-center gap-3">
                                        <input
                                            class="form-check-input mt-0"
                                            type="radio"
                                            name="is_public"
                                            value="1"
                                            id="radioPublic"
                                            <?= (int) $letter['is_public'] === 1
                                                ? 'checked'
                                                : '' ?>>

                                        <i class="bi bi-globe fs-4"></i>

                                        <div>
                                            <div class="fw-bold">
                                                Public
                                            </div>

                                            <small class="text-muted">
                                                But Anonymous
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- ACTIONS -->
                            <div class="update-actions mt-4">
                                <button
                                    type="submit"
                                    class="btn btn-sent w-100 shadow-sm">

                                    <i class="bi bi-check2-circle me-2"></i>
                                    Save Changes
                                </button>

                                <a
                                    href="<?= $escape(
                                                $baseUrl
                                                    . '/resources/views/my-letter.php'
                                            ) ?>"
                                    class="btn btn-outline-dark w-100 mt-2">

                                    Cancel
                                </a>
                            </div>

                        </div>
                    </div>

                </div>
            </form>

        </main>

        <!-- FOOTER -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>

    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/scripts.php'; ?>

    <!-- SAVE CHANGES CONFIRMATION MODAL -->
    <div class="update-confirm-overlay"
        id="saveUpdateModal"
        role="dialog"
        aria-modal="true"
        aria-labelledby="saveUpdateTitle"
        aria-describedby="saveUpdateDescription"
        hidden>

        <div class="update-confirm-box">
            <div class="update-confirm-icon">
                <i class="bi bi-save"></i>
            </div>

            <h2 id="saveUpdateTitle">Save Changes?</h2>

            <p id="saveUpdateDescription">
                Are you sure you want to save the changes to this letter?
            </p>

            <div class="update-confirm-actions">
                <button type="button"
                    class="confirm-cancel"
                    id="cancelSaveUpdate">
                    Cancel
                </button>

                <button type="button"
                    class="confirm-continue"
                    id="confirmSaveUpdate">
                    Continue
                </button>
            </div>
        </div>
    </div>

    <script>
        // Rich text editor
        tinymce.init({
            selector: '#content',
            height: 320,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
            menubar: false
        });

        // Private / Public selection
        function selectMethod(type) {
            const isPrivate = type === 'private';

            document.getElementById('radioPrivate').checked = isPrivate;
            document.getElementById('radioPublic').checked = !isPrivate;

            document.getElementById('cardPrivate').classList.toggle(
                'active',
                isPrivate
            );

            document.getElementById('cardPublic').classList.toggle(
                'active',
                !isPrivate
            );

            document.getElementById('cardPrivate').setAttribute(
                'aria-pressed',
                String(isPrivate)
            );

            document.getElementById('cardPublic').setAttribute(
                'aria-pressed',
                String(!isPrivate)
            );
        }

        document.getElementById('cardPrivate').addEventListener(
            'click',
            function() {
                selectMethod('private');
            }
        );

        document.getElementById('cardPublic').addEventListener(
            'click',
            function() {
                selectMethod('public');
            }
        );

        // Keyboard support for visibility cards
        ['cardPrivate', 'cardPublic'].forEach(function(id) {
            document.getElementById(id).addEventListener(
                'keydown',
                function(event) {
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();

                        selectMethod(
                            id === 'cardPrivate' ? 'private' : 'public'
                        );
                    }
                }
            );
        });


        const updateForm = document.getElementById('updateLetterForm');
        const saveModal = document.getElementById('saveUpdateModal');

        const cancelSaveUpdate = document.getElementById('cancelSaveUpdate');
        const confirmSaveUpdate = document.getElementById('confirmSaveUpdate');

        let saveConfirmed = false;

        // Validasi dan tampilkan modal sebelum submit.
        updateForm.addEventListener('submit', function(event) {
            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }

            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();

            if (!title || !content) {
                event.preventDefault();
                alert('Please fill in the title and content.');
                return;
            }

            if (!saveConfirmed) {
                event.preventDefault();
                saveModal.hidden = false;
                cancelSaveUpdate.focus();
            }
        });

        // Cancel hanya menutup modal.
        cancelSaveUpdate.addEventListener('click', function() {
            saveModal.hidden = true;
        });

        // Continue mengirim form ke process-update.php.
        confirmSaveUpdate.addEventListener('click', function() {
            saveConfirmed = true;

            if (typeof tinymce !== 'undefined') {
                tinymce.triggerSave();
            }

            updateForm.requestSubmit();
        });

        // Klik area gelap untuk menutup modal.
        saveModal.addEventListener('click', function(event) {
            if (event.target === saveModal) {
                saveModal.hidden = true;
            }
        });

        // Tombol Escape untuk menutup modal.
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && !saveModal.hidden) {
                saveModal.hidden = true;
            }
        });
    </script>

</body>

</html>