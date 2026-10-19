<?php
session_start();
session_unset();
session_destroy();

header('Location: /capsule-letter/resources/views/login.php');
exit;