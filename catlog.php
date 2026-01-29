<?php
// Session must be at the VERY TOP, before ANY output
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Catalog - MediCare</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="chatbot.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* General Styles */
        .catalog-page {
            padding: 40px 0;
            min-height: 70vh;
        }
        
        .catalog-header {
            margin-bottom: 40px;
        }
        
        .catalog-header h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .catalog-header p {
            color: #666;
            font-size: 16px;
        }
        
        /* Medicine Grid */
        .medicine-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .medicine-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 1px solid #e8f5e9;
        }
        
        .medicine-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .medicine-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .medicine-category {
            display: inline-block;
            background: #e8f5e9;
            color: #2e7d32;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 12px;
        }
        
        .medicine-price {
            font-size: 22px;
            font-weight: 700;
            color: #2196f3;
            margin: 15px 0;
        }
        
        .medicine-quantity {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        
        .in-stock {
            color: #4caf50;
        }
        
        .low-stock {
            color: #ff9800;
        }
        
        .out-of-stock {
            color: #f44336;
        }
        
        /* Search & Filter */
        .search-filter {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-group input:focus, .form-group select:focus {
            border-color: #2196f3;
            outline: none;
        }
        
        .btn-search {
            background: #4caf50;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            min-width: 100px;
            align-self: flex-start;
            margin-top: 0;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-search:hover {
            background: #45a049;
        }
        
        /* No Results */
        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .no-results h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        /* Medicine Details */
        .medicine-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
            min-height: 60px;
            flex-grow: 1;
        }
        
        .medicine-manufacturer {
            color: #777;
            font-size: 13px;
            margin: 10px 0 15px;
        }
        
        .medicine-expiry {
            color: #999;
            font-size: 12px;
            font-style: italic;
        }
        
        /* Cart Controls */
        .cart-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-grow: 1;
        }
        
        .qty-btn {
            width: 30px;
            height: 30px;
            border: none;
            background: #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .qty-btn:hover {
            background: #e0e0e0;
        }
        
        .qty-btn:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
            color: #999;
        }
        
        .qty-input {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
        }
        
        .qty-input:disabled {
            background: #f9f9f9;
            color: #999;
        }
        
        .btn-add-to-cart {
            background: #2196f3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            flex-shrink: 0;
            font-family: 'Poppins', sans-serif;
        }
        
        .btn-add-to-cart:hover {
            background: #1976d2;
        }
        
        .btn-add-to-cart:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        
        /* Cart Notification */
        .cart-notification {
            position: fixed;
            top: 100px;
            right: 20px;
            background: #4caf50;
            color: white;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Cart Nav Item */
        .cart-nav-item {
            position: relative;
        }
        
        .cart-count-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4444;
            color: white;
            font-size: 12px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-quick {
            padding: 8px 16px;
            border: 2px solid #2196f3;
            background: white;
            color: #2196f3;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-quick:hover {
            background: #2196f3;
            color: white;
        }
        
        /* Expired Warning */
        .expired-warning {
            background: #ffebee;
            color: #c62828;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            margin-top: 10px;
            display: inline-block;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .medicine-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }
            
            .search-filter {
                flex-direction: column;
            }
            
            .form-group {
                min-width: 100%;
            }
            
            .btn-search {
                width: 100%;
            }
            
            .cart-controls {
                flex-direction: column;
                gap: 15px;
            }
            
            .quantity-controls {
                width: 100%;
                justify-content: center;
            }
            
            .btn-add-to-cart {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .medicine-grid {
                grid-template-columns: 1fr;
            }
            
            .catalog-header h1 {
                font-size: 28px;
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
                <li><a href="catlog.php" class="active">Catalog</a></li>
                <li><a href="orders.php">My Orders</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="orders.php">My Orders</a></li>
                    <li><a href="add_medicine.php" class="btn-login">Add Medicine</a></li>
                <?php else: ?>
                    <li><a href="dashboard.html">Dashboard</a></li>
                <?php endif; ?>
                <li class="cart-nav-item">
                    <a href="cart.php" id="cartLink">Cart 
                        <span id="cartCount">0</span>
                        <span id="cartBadge" class="cart-count-badge" style="display: none;"></span>
                    </a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php" class="btn-login">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="btn-login">Login</a></li>
                    <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div id="cartNotification" class="cart-notification">
        Item added to cart!
    </div>

    <div class="catalog-page">
        <div class="container">
            <div class="catalog-header">
                <h1>Medicine Catalog</h1>
                <p>Browse our wide selection of medicines - All prescriptions verified by licensed pharmacists</p>
                
                <form method="GET" action="catalog.php" class="search-filter">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Search medicines, symptoms, or health concerns..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="Pain Relief" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Pain Relief') ? 'selected' : ''; ?>>Pain Relief</option>
                            <option value="Antibiotic" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Antibiotic') ? 'selected' : ''; ?>>Antibiotic</option>
                            <option value="Allergy" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Allergy') ? 'selected' : ''; ?>>Allergy</option>
                            <option value="Digestive" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Digestive') ? 'selected' : ''; ?>>Digestive</option>
                            <option value="Diabetes" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Diabetes') ? 'selected' : ''; ?>>Diabetes</option>
                            <option value="Blood Pressure" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Blood Pressure') ? 'selected' : ''; ?>>Blood Pressure</option>
                            <option value="Cholesterol" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Cholesterol') ? 'selected' : ''; ?>>Cholesterol</option>
                            <option value="Supplement" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Supplement') ? 'selected' : ''; ?>>Supplement</option>
                            <option value="Cold & Flu" <?php echo (isset($_GET['category']) && $_GET['category'] == 'Cold & Flu') ? 'selected' : ''; ?>>Cold & Flu</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select name="sort">
                            <option value="" disabled <?php echo !isset($_GET['sort']) ? 'selected' : ''; ?>>- Sort -</option>
                            <option value="price-low" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price-low') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price-high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price-high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'name') ? 'selected' : ''; ?>>Name A-Z</option>
                            <option value="newest" <?php echo (!isset($_GET['sort']) || $_GET['sort'] == 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-search">Search</button>
                    <?php if(isset($_GET['search']) || isset($_GET['category']) || isset($_GET['sort'])): ?>
                        <a href="catalog.php" class="btn-search" style="background: #666;">Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="medicine-grid">
                <?php
                // Include database configuration
                require_once 'config.php';
                
                // Get database connection
                $conn = getDBConnection();
                
                if (!$conn) {
                    echo '<div class="no-results">
                            <h3>Database Connection Error</h3>
                            <p>Unable to connect to the database. Please try again later.</p>
                          </div>';
                } else {
                    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
                    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';
                    
                    // Build SQL query
                    $sql = "SELECT * FROM medicines WHERE 1=1";
                    $conditions = [];
                    $params = [];
                    $types = "";
                    
                    if (!empty($search)) {
                        $conditions[] = "(name LIKE ? OR description LIKE ? OR manufacturer LIKE ?)";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                        $params[] = "%$search%";
                        $types .= "sss";
                    }
                    
                    if (!empty($category)) {
                        $conditions[] = "category = ?";
                        $params[] = $category;
                        $types .= "s";
                    }
                    
                    // Exclude expired medicines
                    $conditions[] = "expiry_date > CURDATE() OR expiry_date IS NULL";
                    
                    if (!empty($conditions)) {
                        $sql .= " AND " . implode(" AND ", $conditions);
                    }
                    
                    // Add sorting
                    switch ($sort) {
                        case 'price-low':
                            $sql .= " ORDER BY price ASC";
                            break;
                        case 'price-high':
                            $sql .= " ORDER BY price DESC";
                            break;
                        case 'name':
                            $sql .= " ORDER BY name ASC";
                            break;
                        case 'newest':
                        default:
                            $sql .= " ORDER BY created_at DESC";
                    }
                    
                    // Prepare and execute statement
                    $stmt = $conn->prepare($sql);
                    
                    if ($stmt) {
                        if (!empty($params)) {
                            $stmt->bind_param($types, ...$params);
                        }
                        
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            while ($medicine = $result->fetch_assoc()) {
                                $stock_class = '';
                                $is_out_of_stock = false;
                                $is_low_stock = false;
                                $is_expired = false;
                                
                                // Check stock status
                                if ($medicine['quantity'] > 20) {
                                    $stock_class = 'in-stock';
                                } elseif ($medicine['quantity'] > 0) {
                                    $stock_class = 'low-stock';
                                    $is_low_stock = true;
                                } else {
                                    $stock_class = 'out-of-stock';
                                    $is_out_of_stock = true;
                                }
                                
                                // Check expiry date
                                if ($medicine['expiry_date'] && strtotime($medicine['expiry_date']) < strtotime('+30 days')) {
                                    $is_expired = true;
                                }
                                
                                echo '
                                <div class="medicine-card" data-id="' . $medicine['id'] . '">
                                    <div class="medicine-name">' . htmlspecialchars($medicine['name']) . '</div>
                                    <div class="medicine-category">' . htmlspecialchars($medicine['category']) . '</div>
                                    <div class="medicine-description">' . 
                                        substr(htmlspecialchars($medicine['description']), 0, 80) . 
                                        (strlen($medicine['description']) > 80 ? '...' : '') . 
                                    '</div>
                                    <div class="medicine-price">$' . number_format($medicine['price'], 2) . '</div>
                                    <div class="medicine-quantity ' . $stock_class . '">
                                        ' . ($is_low_stock ? '⚠️ ' : '') . 'Stock: ' . $medicine['quantity'] . ' units
                                    </div>
                                    <div class="medicine-manufacturer">
                                        Manufacturer: ' . htmlspecialchars($medicine['manufacturer']) . '
                                    </div>';
                                
                                if ($is_expired && $medicine['expiry_date']) {
                                    $expiry_date = date('M d, Y', strtotime($medicine['expiry_date']));
                                    echo '<div class="expired-warning">⚠️ Expires soon: ' . $expiry_date . '</div>';
                                } elseif ($medicine['expiry_date']) {
                                    $expiry_date = date('M d, Y', strtotime($medicine['expiry_date']));
                                    echo '<div class="medicine-expiry">Expires: ' . $expiry_date . '</div>';
                                }
                                
                                echo '
                                    <div class="cart-controls">
                                        <div class="quantity-controls">
                                            <button class="qty-btn minus" onclick="updateQuantity(' . $medicine['id'] . ', -1)" 
                                                    ' . ($is_out_of_stock ? 'disabled' : '') . '>-</button>
                                            <input type="number" class="qty-input" id="qty_' . $medicine['id'] . '" 
                                                   value="1" min="1" max="' . ($medicine['quantity']) . '" 
                                                   onchange="validateQuantity(' . $medicine['id'] . ', ' . $medicine['quantity'] . ')"
                                                   ' . ($is_out_of_stock ? 'disabled' : '') . '>
                                            <button class="qty-btn plus" onclick="updateQuantity(' . $medicine['id'] . ', 1)" 
                                                    ' . ($is_out_of_stock ? 'disabled' : '') . '>+</button>
                                        </div>
                                        <button class="btn-add-to-cart" onclick="addToCart(' . $medicine['id'] . ')" 
                                                ' . ($is_out_of_stock ? 'disabled' : '') . '>
                                            ' . ($is_out_of_stock ? 'Out of Stock' : ($is_low_stock ? '🛒 Low Stock' : '🛒 Add to Cart')) . '
                                        </button>
                                    </div>
                                </div>';
                            }
                        } else {
                            echo '<div class="no-results">
                                    <h3>No medicines found</h3>
                                    <p>Try adjusting your search criteria or browse all medicines</p>
                                    <div class="quick-actions">
                                        <a href="catalog.php" class="btn-quick">View All Medicines</a>
                                        <a href="add_medicine.php" class="btn-quick">Add New Medicine</a>
                                    </div>
                                  </div>';
                        }
                        
                        $stmt->close();
                    } else {
                        echo '<div class="no-results">
                                <h3>Database Error</h3>
                                <p>There was an error with the database query. Please try again.</p>
                              </div>';
                    }
                    
                    $conn->close();
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        // Cart functionality
        let cart = JSON.parse(localStorage.getItem('medicare_cart')) || [];
        
        // Initialize cart from server if user is logged in
        function initializeCart() {
            <?php if(isset($_SESSION['user_id'])): ?>
            // Try to load cart from server
            fetch('get_cart.php')
                .then(response => response.json())
                .then(serverCart => {
                    if (serverCart && serverCart.length > 0) {
                        // Merge with local cart
                        cart = mergeCarts(cart, serverCart);
                        localStorage.setItem('medicare_cart', JSON.stringify(cart));
                        updateCartCount();
                    }
                })
                .catch(error => console.error('Error loading cart:', error));
            <?php endif; ?>
        }
        
        // Merge local and server carts
        function mergeCarts(localCart, serverCart) {
            const merged = [...serverCart];
            
            localCart.forEach(localItem => {
                const existingIndex = merged.findIndex(item => item.id == localItem.id);
                if (existingIndex > -1) {
                    merged[existingIndex].quantity = Math.max(
                        merged[existingIndex].quantity, 
                        localItem.quantity
                    );
                } else {
                    merged.push(localItem);
                }
            });
            
            return merged;
        }
        
        // Save cart to server
        function saveCartToServer() {
            <?php if(isset($_SESSION['user_id'])): ?>
            fetch('save_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ cart: cart })
            }).catch(error => console.error('Error saving cart:', error));
            <?php endif; ?>
        }
        
        // Update cart count on page load
        function updateCartCount() {
            let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = totalItems;
            
            // Update badge
            const badge = document.getElementById('cartBadge');
            if (totalItems > 0) {
                badge.textContent = totalItems;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Update quantity input
        function updateQuantity(medicineId, change) {
            const input = document.getElementById('qty_' + medicineId);
            let currentValue = parseInt(input.value) || 1;
            let newValue = currentValue + change;
            let maxValue = parseInt(input.max);
            
            if (newValue < 1) newValue = 1;
            if (newValue > maxValue) newValue = maxValue;
            
            input.value = newValue;
        }
        
        // Validate quantity input
        function validateQuantity(medicineId, maxQuantity) {
            const input = document.getElementById('qty_' + medicineId);
            let value = parseInt(input.value) || 1;
            
            if (value < 1) value = 1;
            if (value > maxQuantity) value = maxQuantity;
            
            input.value = value;
        }
        
        // Add item to cart with stock validation
        function addToCart(medicineId) {
            const input = document.getElementById('qty_' + medicineId);
            const quantity = parseInt(input.value) || 1;
            const maxQuantity = parseInt(input.max);
            
            // Validate quantity
            if (quantity < 1) {
                alert('Please enter a valid quantity.');
                input.value = 1;
                return;
            }
            
            if (quantity > maxQuantity) {
                alert(`Only ${maxQuantity} units available in stock.`);
                input.value = maxQuantity;
                return;
            }
            
            const medicineCard = document.querySelector(`.medicine-card[data-id="${medicineId}"]`);
            const medicineName = medicineCard.querySelector('.medicine-name').textContent;
            const medicinePrice = parseFloat(medicineCard.querySelector('.medicine-price').textContent.replace('$', ''));
            const medicineCategory = medicineCard.querySelector('.medicine-category').textContent;
            
            // Check if item already in cart
            const existingItemIndex = cart.findIndex(item => item.id == medicineId);
            
            if (existingItemIndex > -1) {
                // Check if adding more exceeds stock
                const newTotal = cart[existingItemIndex].quantity + quantity;
                if (newTotal > maxQuantity) {
                    alert(`Cannot add more than ${maxQuantity} units. You already have ${cart[existingItemIndex].quantity} in cart.`);
                    return;
                }
                // Update existing item
                cart[existingItemIndex].quantity += quantity;
            } else {
                // Add new item
                cart.push({
                    id: medicineId,
                    name: medicineName,
                    price: medicinePrice,
                    category: medicineCategory,
                    quantity: quantity
                });
            }
            
            // Save to localStorage
            localStorage.setItem('medicare_cart', JSON.stringify(cart));
            
            // Save to server if logged in
            saveCartToServer();
            
            // Update cart count
            updateCartCount();
            
            // Show notification
            showNotification('Item added to cart!');
            
            // Reset quantity to 1
            input.value = 1;
        }
        
        // Show notification
        function showNotification(message) {
            const notification = document.getElementById('cartNotification');
            notification.textContent = message;
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
        
        // Sync cart when page loads
        function syncCart() {
            const currentCart = JSON.parse(localStorage.getItem('medicare_cart')) || [];
            
            // Check for any expired items in cart
            const updatedCart = currentCart.filter(item => {
                // In a real app, you would check stock from database here
                return true;
            });
            
            if (updatedCart.length !== currentCart.length) {
                localStorage.setItem('medicare_cart', JSON.stringify(updatedCart));
                cart = updatedCart;
                showNotification('Some items were removed from cart due to stock changes.');
            }
        }
        
        // Quick add function (for testing)
        function quickAdd(medicineId, quantity = 1) {
            const input = document.getElementById('qty_' + medicineId);
            input.value = quantity;
            addToCart(medicineId);
        }
        
        // Initialize everything on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCart();
            updateCartCount();
            syncCart();
            
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + S to save cart
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    saveCartToServer();
                    showNotification('Cart saved!');
                }
            });
        });
        
        // Before page unload, save cart to server
        window.addEventListener('beforeunload', function() {
            <?php if(isset($_SESSION['user_id'])): ?>
            if (cart.length > 0) {
                // Use synchronous request for reliability
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'save_cart.php', false);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.send(JSON.stringify({ cart: cart }));
            }
            <?php endif; ?>
        });
    </script>
    <script src="chatbot.js"></script>
</body>
</html>