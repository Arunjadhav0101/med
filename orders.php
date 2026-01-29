<?php
session_start();
require_once 'config.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's orders
$orders = [];
$conn = getDBConnection();

if ($conn) {
    $stmt = $conn->prepare("
        SELECT o.*, 
               COUNT(oi.id) as item_count,
               SUM(oi.quantity) as total_items
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($order = $result->fetch_assoc()) {
        $orders[] = $order;
    }
    
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - MediCare</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="chatbot.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .orders-page {
            padding: 40px 0;
            min-height: 70vh;
        }
        
        .orders-header {
            margin-bottom: 40px;
        }
        
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .order-number {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .order-date {
            color: #666;
            font-size: 14px;
        }
        
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .detail-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .detail-value {
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
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
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .btn-order {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-view {
            background: #2196f3;
            color: white;
        }
        
        .btn-view:hover {
            background: #1976d2;
        }
        
        .btn-reorder {
            background: #4caf50;
            color: white;
        }
        
        .btn-reorder:hover {
            background: #45a049;
        }
        
        .btn-invoice {
            background: #ff9800;
            color: white;
        }
        
        .btn-invoice:hover {
            background: #f57c00;
        }
        
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-orders h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .order-details {
                grid-template-columns: 1fr;
            }
            
            .order-actions {
                flex-direction: column;
            }
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
                <li><a href="catlog.php">Catalog</a></li>
                <li><a href="home.html">Dashboard</a></li>
                <li><a href="orders.php" class="active">My Orders</a></li>
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

    <div class="orders-page">
        <div class="container">
            <div class="orders-header">
                <h1>My Orders</h1>
                <p>View your order history and track your shipments</p>
            </div>
            
            <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <h3>No orders yet</h3>
                <p>Start shopping to see your orders here</p>
                <a href="catlog.php" class="btn btn-primary" style="display: inline-block; margin-top: 20px;">Browse Medicines</a>
            </div>
            <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                            <div class="order-date">Placed on <?php echo date('F j, Y', strtotime($order['order_date'])); ?></div>
                        </div>
                        <div class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </div>
                    </div>
                    
                    <div class="order-details">
                        <div class="detail-item">
                            <span class="detail-label">Total Amount</span>
                            <span class="detail-value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Items</span>
                            <span class="detail-value"><?php echo $order['item_count']; ?> items (<?php echo $order['total_items']; ?> units)</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Payment Method</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['payment_method'] ?? 'Cash on Delivery'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Shipping Address</span>
                            <span class="detail-value"><?php echo htmlspecialchars($order['shipping_address'] ?? 'Not specified'); ?></span>
                        </div>
                    </div>
                    
                    <div class="order-actions">
                        <a href="order_confirmation.php?order_id=<?php echo $order['id']; ?>" class="btn-order btn-view">
                            View Details
                        </a>
                        <a href="#" class="btn-order btn-invoice">
                            Download Invoice
                        </a>
                        <a href="#" class="btn-order btn-reorder">
                            Reorder
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="chatbot.js"></script>
</body>
</html>