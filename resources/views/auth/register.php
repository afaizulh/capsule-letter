<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/app.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>
    <title>Sign Up - Capsule Letter</title>
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-logo {
            font-weight: 800;
            letter-spacing: 2px;
            font-size: 2.2rem;
        }

        .brand-dot {
            height: 12px;
            width: 12px;
            background-color: #ff3b00;
            border-radius: 50%;
            display: inline-block;
            margin-left: 6px;
        }

        .auth-card {
            border: 2px solid #000000;
            border-radius: 0;
            padding: 2.5rem;
            max-width: 440px;
            width: 100%;
            background: #ffffff;
        }

        .form-control-icon {
            position: relative;
        }

        .form-control-icon i.input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        .form-control-icon i.toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .form-control-icon input {
            padding-left: 45px;
            padding-right: 45px;
            height: 48px;
            border: 1px solid #ced4da;
            border-radius: 6px;
        }

        .btn-continue {
            background-color: #000000;
            color: #ffffff;
            height: 48px;
            font-weight: 600;
            border-radius: 6px;
            border: none;
        }

        .btn-continue:hover {
            background-color: #222222;
            color: #ffffff;
        }

        .link-pink {
            color: #ff007a;
            text-decoration: none;
            font-weight: 600;
        }

        .link-pink:hover {
            color: #d60066;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <!-- Main Header Logo -->
    <div class="text-center pt-5 mb-4">
        <h1 class="brand-logo m-0">CAPSULE LETTER<span class="brand-dot"></span></h1>
    </div>

    <!-- Sign Up Card Container -->
    <div class="d-flex justify-content-center align-items-center flex-grow-1 px-3 py-4">
        <div class="auth-card">
            <h2 class="text-center fw-bold mb-4">Sign up</h2>

            <!-- Session Alert -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger py-2 small mb-3"><?= $_SESSION['error'];
                                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form action="/capsule-letter/controller/auth/process-register.php" method="POST">
                <!-- First Name & Last Name (Side by side) -->
                <div class="row g-2 mb-3">
                    <div class="col-6 form-control-icon">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                    </div>
                    <div class="col-6 form-control-icon">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" name="last_name" class="form-control" placeholder="Last Name">
                    </div>
                </div>

                <!-- Email Input -->
                <div class="mb-3 form-control-icon">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>

                <!-- Create New Password -->
                <div class="mb-3 form-control-icon">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" id="regPassword" class="form-control" placeholder="Create New Password" required>
                    <i class="bi bi-eye-slash toggle-password" onclick="togglePass('regPassword', this)"></i>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4 form-control-icon">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="confirm_password" id="regConfirmPassword" class="form-control" placeholder="Confirm Password" required>
                    <i class="bi bi-eye-slash toggle-password" onclick="togglePass('regConfirmPassword', this)"></i>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-continue w-100 mb-4">Continue</button>
            </form>

            <!-- Bottom Link -->
            <div class="text-center small text-muted">
                Already have an account? <a href="login.php" class="link-pink">Sign in</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/script-eye.php'; ?>
</body>

</html>