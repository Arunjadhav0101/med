<?php
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$order_id = $_GET['order_id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch order details
$order = null;
$order_items = [];

if ($order_id > 0) {
    $conn = getDBConnection();
    
    if ($conn) {
        // Get order details
        $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
        
        if ($order) {
            // Get order items
            $stmt = $conn->prepare("
                SELECT oi.*, m.name, m.category, m.manufacturer 
                FROM order_items oi 
                JOIN medicines m ON oi.medicine_id = m.id 
                WHERE oi.order_id = ?
            ");
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($item = $result->fetch_assoc()) {
                $order_items[] = $item;
            }
            $stmt->close();
        }
        
        $conn->close();
    }
}

if (!$order) {
    // Try to get the latest order if no specific order ID
    $conn = getDBConnection();
    if ($conn) {
        $stmt = $conn->prepare("SELECT id FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 1");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $latest_order = $result->fetch_assoc();
        $stmt->close();
        $conn->close();
        
        if ($latest_order) {
            header("Location: order_confirmation.php?order_id=" . $latest_order['id']);
            exit;
        }
    }
    
    // If still no order, redirect to cart
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - MediCare</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .confirmation-page {
            padding: 40px 0;
            min-height: 70vh;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .success-icon {
            font-size: 60px;
            text-align: center;
            margin-bottom: 20px;
            color: #4caf50;
        }
        
        .order-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .order-header h1 {
            color: #4caf50;
            margin-bottom: 10px;
        }
        
        .order-number {
            font-size: 20px;
            color: #666;
            font-weight: 600;
        }
        
        .order-date {
            color: #999;
            margin-top: 5px;
        }
        
        .order-details {
            margin: 30px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 18px;
            color: #333;
        }
        
        .order-items {
            margin: 30px 0;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        
        .item-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .item-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .item-quantity {
            font-weight: 600;
            color: #333;
        }
        
        .item-price {
            font-weight: 600;
            color: #2196f3;
            font-size: 18px;
        }
        
        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #4caf50;
            color: white;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
        
        .btn-secondary {
            background: #2196f3;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #1976d2;
        }
        
        .btn-outline {
            background: white;
            color: #333;
            border: 2px solid #ddd;
        }
        
        .btn-outline:hover {
            border-color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-left: 10px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background: #d4edda;
            color: #155724;
        }
        
        .status-delivered {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .thank-you {
            text-align: center;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #eee;
            color: #666;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <div class="logo">
                <span class="logo-icon">💊</span>
                <span class="logo-text">MediCare</span>
            </div>
            <ul class="nav-links">
                <li><a href="home.html">Home</a></li>
                <li><a href="catalog.php">Catalog</a></li>
                <li><a href="dashboard.html">Dashboard</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="orders.php">My Orders</a></li>
                <?php endif; ?>
                <li><a href="cart.php">Cart</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php" class="btn-login">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.html" class="btn-login">Login</a></li>
                    <li><a href="signup.html" class="btn-signup">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="confirmation-page">
        <div class="container">
            <div class="confirmation-card">
                <div class="success-icon">✓</div>
                
                <div class="order-header">
                    <h1>Order Confirmed!</h1>
                    <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                    <div class="order-date">Placed on <?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></div>
                    <div>
                        Status: 
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
                
                <?php if (!empty($order_items)): ?>
                <div class="order-items">
                    <h3>Order Items</h3>
                    <?php foreach ($order_items as $item): ?>
                    <div class="order-item">
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <p><?php echo htmlspecialchars($item['category']); ?> • <?php echo htmlspecialchars($item['manufacturer']); ?></p>
                        </div>
                        <div class="item-quantity">Qty: <?php echo $item['quantity']; ?></div>
                        <div class="item-price">$<?php echo number_format($item['subtotal'], 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="order-details">
                    <h3>Order Summary</h3>
                    <div class="detail-row">
                        <span>Subtotal:</span>
                        <span>$<?php echo number_format($order['total_amount'] - 5.00 - ($order['total_amount'] * 0.10), 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Shipping:</span>
                        <span>$5.00</span>
                    </div>
                    <div class="detail-row">
                        <span>Tax (10%):</span>
                        <span>$<?php echo number_format(($order['total_amount'] - 5.00) * 0.10, 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span>Total Amount:</span>
                        <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                    </div>
                </div>
                
                <div class="actions">
                    <a href="catalog.php" class="btn btn-primary">Continue Shopping</a>
                    <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                    <a href="home.html" class="btn btn-outline">Go to Dashboard</a>
                </div>
                
                <div class="thank-you">
                    <p>Thank you for your order! You will receive an email confirmation shortly.</p>
                    <p>Need help? Contact our support at support@medicare.com</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>