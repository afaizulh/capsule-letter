<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/config/app.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: /capsule-letter/resources/views/home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/head.php'; ?>
    <title>Sign In - Capsule Letter</title>
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

    <!-- Sign In Card Container -->
    <div class="d-flex justify-content-center align-items-center flex-grow-1 px-3">
        <div class="auth-card">
            <h2 class="text-center fw-bold mb-4">Sign in</h2>

            <!-- Session Alert -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger py-2 small mb-3"><?= $_SESSION['error'];
                                                                unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success py-2 small mb-3"><?= $_SESSION['success'];
                                                                    unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <form action="<?php echo $baseUrl; ?>/controller/auth/process-login.php" method="POST">
                <!-- Email Input -->
                <div class="mb-3 form-control-icon">
                    <i class="bi bi-envelope input-icon"></i>
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>

                <!-- Password Input -->
                <div class="mb-4 form-control-icon">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Password" required>
                    <i class="bi bi-eye-slash toggle-password" onclick="togglePass('loginPassword', this)"></i>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-continue w-100 mb-4">Continue</button>
            </form>

            <!-- Bottom Link -->
            <div class="text-center small text-muted">
                Don't have an account yet? <a href="register.php" class="link-pink">Sign up</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/footer.php'; ?>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/capsule-letter/resources/includes/script-eye.php'; ?>
</body>

</html>