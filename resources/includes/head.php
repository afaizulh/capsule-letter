<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Capsule Letter</title>
<!-- Bootstrap v5.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
    :root {
        --sidebar-width: 280px;
        --brand-orange: #FF4500;
    }

    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background-color: #ffffff;
        color: #000000;
        overflow-x: hidden;
    }

    /* Sidebar Styling */
    .sidebar {
        width: var(--sidebar-width);
        min-height: 100vh;
        border-right: 1px solid #dee2e6;
        background-color: #ffffff;
        position: fixed;
        top: 0;
        left: 0;
        z-index: 1000;
        transition: transform 0.3s ease-in-out;
    }

    .main-content {
        margin-left: var(--sidebar-width);
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    @media (max-width: 991.98px) {
        .sidebar {
            transform: translateX(-100%);
        }

        .sidebar.show {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
        }
    }

    /* Navigation Links */
    .nav-link-custom {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #000000;
        font-weight: 500;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 5px;
    }

    .nav-link-custom:hover {
        background-color: #f8f9fa;
    }

    .nav-link-custom.active {
        background-color: #00ffd5;
        color: #000000;
        font-weight: 600;
    }

    .nav-link-custom.active i {
        color: #000000 !important;
    }

    /* Custom UI Elements matching screenshot */
    .brand-dot {
        height: 8px;
        width: 8px;
        background-color: #FF4500;
        border-radius: 50%;
        display: inline-block;
        margin-left: 2px;
    }

    .method-card {
        border: 2px solid #000000;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #ffffff;
    }

    .method-card.active {
        border-color: #FF4500;
        background-color: #FFF5F2;
    }

    .btn-sent {
        background-color: #000000;
        color: #ffffff;
        border-radius: 8px;
        padding: 14px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: background-color 0.2s;
    }

    .btn-sent:hover {
        background-color: #222222;
        color: #ffffff;
    }

    .form-control,
    .form-select {
        border: 1px solid #000000;
        border-radius: 6px;
        padding: 12px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #FF4500;
        box-shadow: 0 0 0 0.25rem rgba(255, 69, 0, 0.15);
    }

    /* Profile badge */
    .profile-badge {
        background-color: #198754;
        color: #ffffff;
        border-radius: 50px;
        padding: 6px 16px;
        font-size: 14px;
        font-weight: 500;
    }

    /* Footer pill button in sidebar */
    .sidebar-pill {
        border: 1px solid #000000;
        border-radius: 50px;
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 600;
        text-align: center;
        display: inline-block;
        text-decoration: none;
        color: #000;
    }
</style>