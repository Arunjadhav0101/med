<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

if (!$data || !isset($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$conn = getDBConnection();

// Check if user already has a cart
$stmt = $conn->prepare("SELECT id FROM user_carts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result->num_rows > 0;
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare("UPDATE user_carts SET cart_data = ? WHERE user_id = ?");
} else {
    $stmt = $conn->prepare("INSERT INTO user_carts (cart_data, user_id) VALUES (?, ?)");
}

$cart_json = json_encode($data['cart']);
$stmt->bind_param("si", $cart_json, $user_id);
$success = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $success]);
?>