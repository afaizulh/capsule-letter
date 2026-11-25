<?php
session_start();
session_unset();
session_destroy();

require_once __DIR__ . '/../../config/app.php';

header('Location: ' . $baseUrl . '/resources/views/auth/login.php');
exit;