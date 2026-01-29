<?php
// Auto Cart API for Chatbot Integration
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'add_medicine':
            echo json_encode(addMedicineToCart($input));
            break;
        case 'create_order':
            echo json_encode(createAutoOrder($input));
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

function addMedicineToCart($data) {
    $medicineId = $data['medicine_id'] ?? 0;
    $quantity = $data['quantity'] ?? 1;
    $userId = $_SESSION['user_id'] ?? null;
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Please login first', 'redirect' => 'login.html'];
    }
    
    // Add to session cart for now
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][$medicineId] = [
        'quantity' => $quantity,
        'added_by_chatbot' => true,
        'timestamp' => time()
    ];
    
    return ['success' => true, 'message' => 'Added to cart successfully'];
}

function createAutoOrder($data) {
    $userId = $_SESSION['user_id'] ?? null;
    $address = $data['address'] ?? '';
    $phone = $data['phone'] ?? '';
    
    if (!$userId) {
        return ['success' => false, 'message' => 'Please login first'];
    }
    
    if (empty($_SESSION['cart'])) {
        return ['success' => false, 'message' => 'Cart is empty'];
    }
    
    $conn = getDBConnection();
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Create order
    $orderTotal = calculateCartTotal();
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, phone, status, created_by) VALUES (?, ?, ?, ?, 'pending', 'chatbot')");
    $stmt->bind_param("idss", $userId, $orderTotal, $address, $phone);
    
    if ($stmt->execute()) {
        $orderId = $conn->insert_id;
        
        // Add order items
        foreach ($_SESSION['cart'] as $medicineId => $item) {
            $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, medicine_id, quantity) VALUES (?, ?, ?)");
            $stmt2->bind_param("iii", $orderId, $medicineId, $item['quantity']);
            $stmt2->execute();
        }
        
        // Clear cart
        unset($_SESSION['cart']);
        
        return ['success' => true, 'message' => 'Order created successfully', 'order_id' => $orderId];
    }
    
    return ['success' => false, 'message' => 'Failed to create order'];
}

function calculateCartTotal() {
    if (empty($_SESSION['cart'])) return 0;
    
    $conn = getDBConnection();
    $total = 0;
    
    foreach ($_SESSION['cart'] as $medicineId => $item) {
        $stmt = $conn->prepare("SELECT price FROM medicines WHERE id = ?");
        $stmt->bind_param("i", $medicineId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $total += $row['price'] * $item['quantity'];
        }
    }
    
    return $total;
}
?>
