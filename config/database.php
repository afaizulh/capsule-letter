<?php
    $servername = "localhost";
    $database = "db_capsule_letter";
    $username = "root";
    $password = "";
    $conn = mysqli_connect($servername, $username, $password, $database);

    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
    return $conn;