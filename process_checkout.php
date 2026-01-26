<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];

if (empty($data['cart'])) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$conn = getDBConnection();

// Start transaction
$conn->begin_transaction();

try {
    // Check stock availability
    foreach ($data['cart'] as $item) {
        $stmt = $conn->prepare("SELECT quantity FROM medicines WHERE id = ?");
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $medicine = $result->fetch_assoc();
        $stmt->close();
        
        if (!$medicine || $medicine['quantity'] < $item['quantity']) {
            throw new Exception("Insufficient stock for " . $item['name']);
        }
    }
    
    // Calculate total
    $total = 0;
    foreach ($data['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Add shipping and tax
    $shipping = 5.00;
    $tax = $total * 0.10;
    $grand_total = $total + $shipping + $tax;
    
    // Generate order number
    $order_number = 'ORD' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
    // Create order
    $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sidds", $order_number, $user_id, $grand_total, $data['shipping_address'], $data['payment_method']);
    $stmt->execute();
    $order_id = $conn->insert_id;
    $stmt->close();
    
    // Add order items and update stock
    foreach ($data['cart'] as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        
        // Add to order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item['price'], $subtotal);
        $stmt->execute();
        $stmt->close();
        
        // Update medicine stock
        $stmt = $conn->prepare("UPDATE medicines SET quantity = quantity - ? WHERE id = ?");
        $stmt->bind_param("ii", $item['quantity'], $item['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'order_number' => $order_number,
        'total' => $grand_total
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>