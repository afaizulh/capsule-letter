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

            <!-- CREATE VIEW  -->
            <div id="view-create" class="row g-5">
                <!-- Left Column: Form Inputs -->
                <div class="col-lg-7 col-xl-8">
                    <div class="mb-4">
                        <h1 class="display-5 fw-bold mb-2">A Letter to My Future Self.</h1>
                        <p class="text-muted fs-6">Preserving This Strory for the Future</p>
                    </div>

                    <form id="letterForm" onsubmit="handleFormSubmit(event)">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">Letter title</label>
                            <input type="text" class="form-control fw-medium" id="letterTitle" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark mb-2">Content of Letter</label>
                            <textarea class="form-control" id="letterContent" rows="10" required></textarea>
                        </div>

                        <!-- Helper Notes -->
                        <div class="text-muted" style="font-size: 12px; line-height: 1.6;">
                            <div>Before locking the letter <i class="bi bi-info-circle"></i></div>
                            <div>Edit available for 3 days after creation.</div>
                            <div>Deletion available for 7 days after creation.</div>
                            <div>Editing the letter does not reset the deadline.</div>
                        </div>
                    </form>
                </div>

                <!-- Right Column: Settings & Submit -->
                <div class="col-lg-5 col-xl-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Opening Date -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">Opening Date</label>
                            <div class="input-group">
                                <input type="text" class="form-control fw-medium" id="openingDate" value="15 Oktober 2026">
                                <span class="input-group-text bg-white border-start-0">
                                    <i class="bi bi-calendar-event fs-5"></i>
                                </span>
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
                                        <input class="form-check-input" type="radio" name="methodRadio" id="radioPrivate" checked>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-lock fs-4 text-dark"></i>
                                        <span class="fw-bold">Private</span>
                                    </div>
                                </div>
                                <span class="badge bg-danger rounded-circle p-1" style="width: 8px; height: 8px; display: inline-block;"></span>
                            </div>

                            <!-- Public Option -->
                            <div class="method-card p-3 d-flex align-items-center justify-content-between" onclick="selectMethod('public')" id="cardPublic">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="radio" name="methodRadio" id="radioPublic">
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
                        <button type="button" onclick="submitLetter()" class="btn btn-sent w-100 shadow-sm">
                            Sent to Future
                        </button>
                    </div>
                </div>
            </div>

        </main>

        <!-- Footer -->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>
    </div>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/scripts.php'; ?>

    <script>
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

        // Form Submit Simulation
        function submitLetter() {
            const title = document.getElementById('letterTitle').value;
            const content = document.getElementById('letterContent').value;
            const openingDate = document.getElementById('openingDate').value;

            if (!title || !content) {
                alert('Silakan isi judul dan konten surat terlebih dahulu.');
                return;
            }

            alert(`Berhasil! Surat "${title}" telah disimpan dan dikunci hingga ${openingDate}.`);
        }
    </script>
</body>

</html>