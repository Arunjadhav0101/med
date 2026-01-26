<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['available' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get cart data
$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

if (empty($cart)) {
    echo json_encode(['available' => true, 'itemName' => '']);
    exit;
}

$conn = getDBConnection();

if (!$conn) {
    echo json_encode(['available' => false, 'message' => 'Database connection failed']);
    exit;
}

foreach ($cart as $item) {
    $stmt = $conn->prepare("SELECT name, quantity FROM medicines WHERE id = ?");
    $stmt->bind_param("i", $item['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $medicine = $result->fetch_assoc();
    $stmt->close();
    
    if (!$medicine || $medicine['quantity'] < $item['quantity']) {
        echo json_encode([
            'available' => false,
            'itemName' => $medicine ? $medicine['name'] : 'Unknown item',
            'availableStock' => $medicine ? $medicine['quantity'] : 0,
            'requested' => $item['quantity']
        ]);
        $conn->close();
        exit;
    }
}

$conn->close();
echo json_encode(['available' => true, 'itemName' => '']);
?>