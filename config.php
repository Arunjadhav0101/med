<?php
define('DB_HOST', 'mysql');  // Docker service name
define('DB_PORT', 3306);
define('DB_USER', 'medicare_user');
define('DB_PASS', 'medicare_pass');
define('DB_NAME', 'medicare');

function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        error_log("DB Connection Failed: " . $conn->connect_error);
        return null;
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}
?>
