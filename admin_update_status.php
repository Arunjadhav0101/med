<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? 0;
$status = $input['status'] ?? '';

// Validate input
if (!$order_id || !in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Update order status
$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $order_id);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Order status updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update order status']);
}

$stmt->close();
$conn->close();
?>