<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit;
}

// Get statistics
$conn = getDBConnection();
$stats = [];

if ($conn) {
    // Total users (excluding admin)
    $stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE role = 'customer'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_users'] = $result->fetch_assoc()['total_users'] ?? 0;
    $stmt->close();
    
    // Total orders
    $stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_orders'] = $result->fetch_assoc()['total_orders'] ?? 0;
    $stmt->close();
    
    // Total revenue
    $stmt = $conn->prepare("SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;
    $stmt->close();
    
    // Total medicines
    $stmt = $conn->prepare("SELECT COUNT(*) as total_medicines FROM medicines");
    $stmt->execute();
    $result = $stmt->get_result();
    $stats['total_medicines'] = $result->fetch_assoc()['total_medicines'] ?? 0;
    $stmt->close();
    
    // Recent orders (last 10)
    $stmt = $conn->prepare("
        SELECT o.*, u.full_name as customer_name 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        ORDER BY o.order_date DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $recent_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    // Low stock medicines
    $stmt = $conn->prepare("SELECT * FROM medicines WHERE quantity < 10 ORDER BY quantity ASC LIMIT 10");
    $stmt->execute();
    $low_stock = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MediCare</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-info h3 {
            margin: 0 0 5px 0;
            font-size: 24px;
            color: #2c3e50;
        }
        
        .stat-info p {
            margin: 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .card-link {
            color: #3498db;
            text-decoration: none;
            font-size: 14px;
        }
        
        .card-link:hover {
            text-decoration: underline;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #eee;
        }
        
        .admin-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .admin-table tr:hover {
            background: #f8f9fa;
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
        
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
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
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                width: 0;
                overflow: hidden;
            }
            
            .admin-main {
                margin-left: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
                <div class="admin-subtitle">Admin Panel</div>
            </div>
            
            <div class="sidebar-menu">
                <a href="admin_dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="admin_order_view.php" class="menu-item">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
                <a href="admin_medicines.php" class="menu-item">
                    <i class="fas fa-pills"></i>
                    <span>Medicines</span>
                </a>
                <a href="admin_users.php" class="menu-item">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="admin_reports.php" class="menu-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                <a href="admin_settings.php" class="menu-item">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
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
                    <div class="admin-logo">MediCare Admin</div>
                    <div class="welcome-text">Welcome, <?php echo $_SESSION['user_name']; ?>!</div>
                </div>
                <div class="admin-user">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?></div>
                    <div>
                        <div><?php echo $_SESSION['user_name']; ?></div>
                        <div style="font-size: 12px; color: #666;">Administrator</div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #3498db;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #2ecc71;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e74c3c;">
                        <i class="fas fa-pills"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_medicines']; ?></h3>
                        <p>Total Medicines</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f39c12;">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Orders</h3>
                    <a href="admin_orders.php" class="card-link">View All →</a>
                </div>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_orders)): ?>
                                <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_number']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin_order_view.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-primary btn-sm">View</a>
                                            <button class="btn btn-success btn-sm">Update</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Low Stock Medicines -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Low Stock Medicines</h3>
                    <a href="admin_medicines.php" class="card-link">View All →</a>
                </div>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Medicine</th>
                                <th>Category</th>
                                <th>Manufacturer</th>
                                <th>Stock</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($low_stock)): ?>
                                <?php foreach ($low_stock as $medicine): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($medicine['name']); ?></td>
                                    <td><?php echo htmlspecialchars($medicine['category']); ?></td>
                                    <td><?php echo htmlspecialchars($medicine['manufacturer']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $medicine['quantity'] < 5 ? '#e74c3c' : '#f39c12'; ?>;">
                                            <?php echo $medicine['quantity']; ?> units
                                        </span>
                                    </td>
                                    <td>$<?php echo number_format($medicine['price'], 2); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="admin_medicine_edit.php?id=<?php echo $medicine['id']; ?>" 
                                               class="btn btn-primary btn-sm">Edit</a>
                                            <a href="admin_medicine_stock.php?id=<?php echo $medicine['id']; ?>" 
                                               class="btn btn-success btn-sm">Restock</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">All medicines are well stocked</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.style.width = sidebar.style.width === '250px' ? '0' : '250px';
        }
    </script>
</body>
</html>