<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit;
}

$order_id = $_GET['id'] ?? 0;

if (!$order_id) {
    header('Location: admin_orders.php');
    exit;
}

// Get order details
$conn = getDBConnection();
$order = null;
$order_items = [];

if ($conn) {
    // Get order with customer details
    $stmt = $conn->prepare("
        SELECT o.*, u.full_name, u.email, u.phone, u.created_at as user_since
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $order_id);
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

if (!$order) {
    header('Location: admin_orders.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - MediCare Admin</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Admin Dashboard Styles */
        .admin-dashboard {
            min-height: 100vh;
            background: #f5f7fa;
        }
        
        .admin-sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
        }
        
        .admin-main {
            margin-left: 250px;
            padding: 20px;
        }
        
        .admin-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .admin-logo {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .welcome-text {
            color: #666;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .sidebar-header {
            padding: 30px 20px;
            background: #1a252f;
        }
        
        .admin-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .admin-subtitle {
            font-size: 12px;
            opacity: 0.7;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            color: #bdc3c7;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
            position: relative;
        }
        
        .menu-item:hover, .menu-item.active {
            background: #34495e;
            color: white;
            border-left-color: #3498db;
        }
        
        .menu-item i {
            width: 20px;
            text-align: center;
        }
        
        .badge {
            margin-left: auto;
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
        }
        
        /* Order Details Styles */
        .order-details-page {
            display: grid;
            gap: 30px;
        }
        
        .order-header-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-info h2 {
            margin: 0 0 10px 0;
            color: #2c3e50;
        }
        
        .order-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .details-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 20px 0;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .info-grid {
            display: grid;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #eee;
        }
        
        .info-label {
            font-weight: 500;
            color: #666;
        }
        
        .info-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .items-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
        }
        
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .item-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .item-category {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .status-select {
            padding: 8px 15px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-family: 'Poppins', sans-serif;
            background: white;
            cursor: pointer;
        }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #cce5ff; color: #004085; }
        .status-shipped { background: #d4edda; color: #155724; }
        .status-delivered { background: #d1ecf1; color: #0c5460; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .order-header-card {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .order-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .order-actions {
                flex-direction: column;
                width: 100%;
            }
            
            .status-select {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="sidebar-header">
                <div class="admin-title">MediCare Admin</div>
                <div class="admin-subtitle">
                    <?php 
                        if (isset($_SESSION['user_name'])) {
                            echo "Welcome, " . $_SESSION['user_name'];
                        } else {
                            echo "Admin Panel";
                        }
                    ?>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="admin_dashboard.php" class="menu-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin_orders.php" class="menu-item active">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                    <?php
                        // Get pending orders count
                        require_once 'config.php';
                        $conn = getDBConnection();
                        if ($conn) {
                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $pending = $result->fetch_assoc()['count'] ?? 0;
                            $stmt->close();
                            $conn->close();
                            
                            if ($pending > 0) {
                                echo '<span class="badge">' . $pending . '</span>';
                            }
                        }
                    ?>
                </a>
                <a href="admin_medicines.php" class="menu-item">
                    <i class="fas fa-pills"></i>
                    <span>Medicines</span>
                </a>
                <a href="admin_users.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="admin-main">
            <div class="admin-header">
                <div class="logo-area">
                    <a href="admin_orders.php" style="color: #3498db; text-decoration: none; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-arrow-left"></i> Back to Orders
                    </a>
                </div>
                <div class="admin-user">
                    <div class="user-avatar">
                        <?php echo isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'A'; ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></div>
                        <div style="font-size: 12px; color: #666;">Administrator</div>
                    </div>
                </div>
            </div>
            
            <div class="order-details-page">
                <!-- Order Header -->
                <div class="order-header-card">
                    <div class="order-info">
                        <h2>Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
                        <div class="order-meta">
                            <span>Placed: <?php echo date('F j, Y g:i A', strtotime($order['order_date'])); ?></span>
                            <span>Status: 
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </span>
                            <span>Total: $<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>
                    <div class="order-actions">
                        <select class="status-select" onchange="updateOrderStatus(<?php echo $order['id']; ?>, this.value)">
                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancel</option>
                        </select>
                        <a href="#" class="btn btn-primary" onclick="printInvoice()">
                            <i class="fas fa-print"></i> Print Invoice
                        </a>
                    </div>
                </div>
                
                <div class="details-grid">
                    <!-- Order Items -->
                    <div class="details-card">
                        <h3 class="card-title">Order Items (<?php echo count($order_items); ?>)</h3>
                        <table class="items-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="item-category">
                                            <?php echo htmlspecialchars($item['category']); ?> • 
                                            <?php echo htmlspecialchars($item['manufacturer']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Order Summary & Customer Info -->
                    <div>
                        <!-- Order Summary -->
                        <div class="details-card" style="margin-bottom: 20px;">
                            <h3 class="card-title">Order Summary</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Subtotal</span>
                                    <span class="info-value">$<?php echo number_format($order['total_amount'] - 5.00 - ($order['total_amount'] * 0.10), 2); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Shipping</span>
                                    <span class="info-value">$5.00</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Tax (10%)</span>
                                    <span class="info-value">$<?php echo number_format(($order['total_amount'] - 5.00) * 0.10, 2); ?></span>
                                </div>
                                <div class="info-item" style="font-size: 18px; border-top: 2px solid #eee; padding-top: 15px;">
                                    <span class="info-label">Total</span>
                                    <span class="info-value">$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Customer Information -->
                        <div class="details-card">
                            <h3 class="card-title">Customer Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <span class="info-label">Name</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Email</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Phone</span>
                                    <span class="info-value"><?php echo htmlspecialchars($order['phone'] ?: 'N/A'); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Member Since</span>
                                    <span class="info-value"><?php echo date('M Y', strtotime($order['user_since'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Shipping Address -->
                        <?php if ($order['shipping_address']): ?>
                        <div class="details-card" style="margin-top: 20px;">
                            <h3 class="card-title">Shipping Address</h3>
                            <p style="color: #666; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Update order status
        async function updateOrderStatus(orderId, newStatus) {
            if (!confirm('Are you sure you want to update this order status?')) {
                location.reload();
                return;
            }
            
            try {
                const response = await fetch('admin_update_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: orderId,
                        status: newStatus
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Order status updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating status: ' + result.message);
                    location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error updating status. Please try again.');
                location.reload();
            }
        }
        
        // Print invoice
        function printInvoice() {
            window.open('admin_order_invoice.php?id=<?php echo $order_id; ?>', '_blank');
        }
        
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.style.width = sidebar.style.width === '250px' ? '0' : '250px';
        }
        
        // Add toggle button for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.admin-header');
            const toggleBtn = document.createElement('button');
            toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
            toggleBtn.style.cssText = 'display: none; background: #3498db; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;';
            toggleBtn.onclick = toggleSidebar;
            
            header.insertBefore(toggleBtn, header.firstChild);
            
            // Show toggle button on mobile
            if (window.innerWidth <= 768) {
                toggleBtn.style.display = 'block';
                document.querySelector('.admin-sidebar').style.width = '0';
                document.querySelector('.admin-main').style.marginLeft = '0';
            }
            
            window.addEventListener('resize', function() {
                if (window.innerWidth <= 768) {
                    toggleBtn.style.display = 'block';
                } else {
                    toggleBtn.style.display = 'none';
                    document.querySelector('.admin-sidebar').style.width = '250px';
                    document.querySelector('.admin-main').style.marginLeft = '250px';
                }
            });
        });
    </script>
</body>
</html>