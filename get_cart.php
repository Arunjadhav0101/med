<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT cart_data FROM user_carts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo $row['cart_data'];
} else {
    echo '[]';
}

$stmt->close();
$conn->close();
?>