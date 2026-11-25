<?php

$readEnv = static function (string $key, string $default): string {
    $value = getenv($key);

    return is_string($value) && $value !== '' ? $value : $default;
};

$servername = $readEnv('DB_HOST', '127.0.0.1');
$database   = $readEnv('DB_DATABASE', 'db_capsule_letter');
$username   = $readEnv('DB_USERNAME', 'root');
$port       = (int) $readEnv('DB_PORT', '3306');

$password = getenv('DB_PASSWORD');

if ($password === false) {
    $password = '';
}

$conn = mysqli_connect($servername, $username, $password, $database, $port);

if (!$conn) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}

return $conn;
