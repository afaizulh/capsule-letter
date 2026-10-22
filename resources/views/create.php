<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/app.php';

// Cek apakah user sudah login atau belum
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: /capsule-letter/resources/views/auth/login.php');
    exit;
}

$conn = require_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/database.php';
$categoriesResult = mysqli_query($conn, "SELECT id, name FROM categories");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>

    <!-- CDN TinyMCE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/sidebar.php'; ?>

    <!-- MAIN CONTENT AREA -->
    <div class="main-content">

        <!-- Top Navigation / Header -->
        <header class="p-4 border-bottom d-flex justify-content-between align-items-center bg-white">
            <span class="text-uppercase text-secondary fw-semibold" id="topBadge" style="font-size: 12px; letter-spacing: 1.5px;">CREATE LETTER</span>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/profile-badge.php'; ?>
        </header>

        <!-- Dynamic Content View Container -->
        <main class="container-fluid p-4 p-md-5 flex-grow-1" id="mainViewContainer">

            <!-- ALERT -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'];
                    unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <!-- END ALERT -->

            <!-- CREATE VIEW  -->
            <form id="letterForm" action="<?php echo $baseUrl; ?>/controller/create-letter/process-create.php" method="POST" enctype="multipart/form-data" onsubmit="tinymce.triggerSave();">
                <div id="view-create" class="row g-5">
                    <div class="col-lg-7 col-xl-8">
                        <div class="mb-4">
                            <h1 class="display-5 fw-bold mb-2">A Letter to My Future Self.</h1>
                            <p class="text-muted fs-6">Preserving This Strory for the Future</p>
                        </div>

                        <!-- Letter Title -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">Letter Title</label>
                            <input type="text" class="form-control fw-medium" id="title" name="title" placeholder="Input Title" required>
                        </div>

                        <!-- Category Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">Category (Optional)</label>
                            <select class="form-select fw-medium" name="category_id">
                                <option value="" disabled selected>Choose Category</option>
                                <?php while ($cat = mysqli_fetch_assoc($categoriesResult)): ?>
                                    <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Content with TinyMCE -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark mb-2">Content of Letter</label>
                            <textarea class="form-control" id="content" name="content" rows="10" placeholder="Input Content"></textarea>
                        </div>

                        <!-- File / Image Attachment -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">Attachment (Image / Document (Optional))</label>
                            <input type="file" class="form-control" name="attachment" accept="image/*,.pdf,.doc,.docx">
                            <div class="form-text text-muted" style="font-size: 11px;">Supported formats: JPG, PNG, PDF, DOC (Max. 2MB)</div>
                        </div>

                        <!-- Helper Notes -->
                        <div class="text-muted" style="font-size: 12px; line-height: 1.6;">
                            <div>Before locking the letter <i class="bi bi-info-circle"></i></div>
                            <div>Edit available for 3 days after creation.</div>
                            <div>Deletion available for 7 days after creation.</div>
                            <div>Editing the letter does not reset the deadline.</div>
                        </div>

                    </div>

                    <!-- Right Column: Settings & Submit -->
                    <div class="col-lg-5 col-xl-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Opening Date -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">Opening Date</label>
                                <div class="input-group">
                                    <input type="date" class="form-control fw-medium" id="unlock_date" name="unlock_date" placeholder="Select Date" required>
                                </div>
                                <div class="form-text text-muted mt-1" style="font-size: 12px;">The letter is locked until this date.</div>
                            </div>

                            <!-- Method Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-bold text-dark mb-2">Method</label>

                                <!-- Private Option -->
                                <div class="method-card active p-3 mb-3 d-flex align-items-center justify-content-between" onclick="selectMethod('private')" id="cardPrivate">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="radio" name="is_public" value="0" id="radioPrivate" checked>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-lock fs-4 text-dark"></i>
                                            <span class="fw-bold">Private</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Public Option -->
                                <div class="method-card p-3 d-flex align-items-center justify-content-between" onclick="selectMethod('public')" id="cardPublic">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="radio" name="is_public" value="1" id="radioPublic">
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-globe fs-4 text-dark"></i>
                                            <div>
                                                <div class="fw-bold lh-1">Public</div>
                                                <small class="text-muted" style="font-size: 10px;">But Anonymous</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="mt-4">
                            <button type="submit" class="btn btn-sent w-100 shadow-sm">
                                Sent to Future
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </main>

        <!-- Footer -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/scripts.php'; ?>

    <script>
        tinymce.init({
            selector: '#content',
            height: 300,
            plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat',
            menubar: false
        });

        document.addEventListener('DOMContentLoaded', function() {
            const unlockInput = document.getElementById('unlock_date');
            const unlockBtn = document.getElementById('unlockDateButton');

            // Dapatkan tanggal hari ini dalam format YYYY-MM-DD
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            const minDate = `${year}-${month}-${day}`;

            // 2Set atribut min pada input tanggal (Mencegah pilih tanggal sebelum hari ini)
            if (unlockInput) {
                unlockInput.min = minDate;
                unlockInput.value = minDate; // Set default nilai ke hari ini
            }

            // Fungsionalitas agar tombol ikon kalender ikut memicu popup date picker
            if (unlockBtn && unlockInput) {
                unlockBtn.addEventListener('click', function() {
                    if (typeof unlockInput.showPicker === 'function') {
                        unlockInput.showPicker(); // Standar modern browser untuk memunculkan kalender
                    } else {
                        unlockInput.focus();
                    }
                });
            }
        });

        // Method Selector Handler (Private / Public)
        function selectMethod(type) {
            const cardPrivate = document.getElementById('cardPrivate');
            const cardPublic = document.getElementById('cardPublic');
            const radioPrivate = document.getElementById('radioPrivate');
            const radioPublic = document.getElementById('radioPublic');

            if (type === 'private') {
                cardPrivate.classList.add('active');
                cardPublic.classList.remove('active');
                radioPrivate.checked = true;
                radioPublic.checked = false;
            } else {
                cardPublic.classList.add('active');
                cardPrivate.classList.remove('active');
                radioPublic.checked = true;
                radioPrivate.checked = false;
            }
        }
    </script>
</body>

</html>