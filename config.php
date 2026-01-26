<?php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', 3306);   // change to 3306 if needed
define('DB_USER', 'root');
define('DB_PASS', '');
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
