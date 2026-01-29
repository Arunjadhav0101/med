<?php
// Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once 'config.php';

// Handle AJAX requests for cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'save_cart':
            if (isset($_POST['cart'])) {
                $cart_data = $_POST['cart'];
                $conn = getDBConnection();
                
                if (is_string($cart_data)) {
                    $cart_data = json_decode($cart_data, true);
                }
                
                $cart_json = json_encode($cart_data);
                
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
                    $stmt = $conn->prepare("INSERT INTO user_carts (user_id, cart_data) VALUES (?, ?)");
                }
                
                $stmt->bind_param("si", $cart_json, $user_id);
                $success = $stmt->execute();
                $stmt->close();
                $conn->close();
                
                echo json_encode(['success' => $success]);
            }
            break;
            
        case 'get_cart':
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT cart_data FROM user_carts WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                echo $row['cart_data'];
            } else {
                echo json_encode([]);
            }
            
            $stmt->close();
            $conn->close();
            break;
            
        case 'checkout':
            if (isset($_POST['cart']) && !empty($_POST['cart'])) {
                $cart = $_POST['cart'];
                
                if (is_string($cart)) {
                    $cart = json_decode($cart, true);
                }
                
                // Calculate total
                $total_amount = 0;
                foreach ($cart as $item) {
                    $total_amount += $item['price'] * $item['quantity'];
                }
                
                // Add shipping and tax
                $shipping = 5.00;
                $tax = $total_amount * 0.10;
                $grand_total = $total_amount + $shipping + $tax;
                
                // Generate order number
                $order_number = 'ORD' . date('Ymd') . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
                
                $conn = getDBConnection();
                $conn->begin_transaction();
                
                try {
                    // Save order
                    $stmt = $conn->prepare("INSERT INTO orders (order_number, user_id, total_amount, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->bind_param("sid", $order_number, $user_id, $grand_total);
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to create order: " . $stmt->error);
                    }
                    
                    $order_id = $conn->insert_id;
                    $stmt->close();
                    
                    // Save order items
                    foreach ($cart as $item) {
                        $subtotal = $item['price'] * $item['quantity'];
                        
                        $stmt = $conn->prepare("INSERT INTO order_items (order_id, medicine_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");
                        $stmt->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item['price'], $subtotal);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to save order item: " . $stmt->error);
                        }
                        $stmt->close();
                        
                        // Update medicine stock
                        $stmt = $conn->prepare("UPDATE medicines SET quantity = quantity - ? WHERE id = ?");
                        $stmt->bind_param("ii", $item['quantity'], $item['id']);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to update stock: " . $stmt->error);
                        }
                        $stmt->close();
                    }
                    
                    // Clear user cart after successful checkout
                    $stmt = $conn->prepare("DELETE FROM user_carts WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $stmt->close();
                    
                    $conn->commit();
                    
                    echo json_encode([
                        'success' => true,
                        'order_id' => $order_id,
                        'order_number' => $order_number,
                        'total' => $grand_total,
                        'message' => 'Order placed successfully!'
                    ]);
                    
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['success' => false, 'message' => 'Checkout failed: ' . $e->getMessage()]);
                }
                
                $conn->close();
            } else {
                echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - MediCare</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="chatbot.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .cart-page {
            padding: 40px 0;
            min-height: 70vh;
        }
        
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-top: 30px;
        }
        
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
        }
        
        .cart-items {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 1fr auto auto auto;
            gap: 15px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .cart-item {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 10px;
            }
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: 600;
            color: #333;
            font-size: 16px;
        }
        
        .item-category {
            font-size: 12px;
            color: #666;
            background: #f0f0f0;
            padding: 3px 10px;
            border-radius: 12px;
            display: inline-block;
            margin-top: 5px;
        }
        
        .item-price {
            font-weight: 600;
            color: #2196f3;
            font-size: 18px;
            white-space: nowrap;
        }
        
        .item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
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
        }
        
        .qty-btn:hover {
            background: #e0e0e0;
        }
        
        .qty-input {
            width: 50px;
            text-align: center;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .item-total {
            font-weight: 600;
            font-size: 18px;
            color: #333;
            white-space: nowrap;
        }
        
        .remove-btn {
            background: #ff4444;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .remove-btn:hover {
            background: #cc0000;
        }
        
        .cart-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        
        .clear-cart-btn {
            background: #666;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .clear-cart-btn:hover {
            background: #444;
        }
        
        .cart-summary {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        
        .summary-row.total {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            border-top: 2px solid #333;
            margin-top: 10px;
            padding-top: 15px;
        }
        
        .checkout-btn {
            width: 100%;
            background: #4caf50;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            margin-top: 20px;
        }
        
        .checkout-btn:hover {
            background: #45a049;
        }
        
        .checkout-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-cart h3 {
            margin-bottom: 10px;
            color: #333;
        }
        
        .btn-continue-shopping {
            display: inline-block;
            background: #2196f3;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: background 0.3s;
        }
        
        .btn-continue-shopping:hover {
            background: #1976d2;
        }
        
        .login-required {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .login-required a {
            color: #2196f3;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-required a:hover {
            text-decoration: underline;
        }
        
        .notification {
            position: fixed;
            top: 100px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 1000;
            display: none;
            animation: slideIn 0.3s ease;
        }
        
        .success-notification {
            background: #4caf50;
            color: white;
        }
        
        .error-notification {
            background: #f44336;
            color: white;
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
                <li><a href="dashboard.html">Dashboard</a></li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="orders.php">My Orders</a></li>
                    <li><a href="add_medicine.php" class="btn-login">Add Medicine</a></li>
                <?php endif; ?>
                <li class="cart-nav-item">
                    <a href="cart.php" class="active" id="cartLink">Cart 
                        <span id="cartCount">0</span>
                        <span id="cartBadge" class="cart-count-badge" style="display: none;"></span>
                    </a>
                </li>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li><a href="logout.php" class="btn-login">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.html" class="btn-login">Login</a></li>
                    <li><a href="signup.html" class="btn-signup">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div id="successNotification" class="notification success-notification"></div>
    <div id="errorNotification" class="notification error-notification"></div>

    <div class="cart-page">
        <div class="container">
            <h1 style="margin-bottom: 30px;">Shopping Cart</h1>
            
            <?php if(!isset($_SESSION['user_id'])): ?>
            <div class="login-required">
                <p>Please <a href="login.html">login</a> or <a href="signup.html">create an account</a> to proceed with checkout.</p>
            </div>
            <?php endif; ?>
            
            <div class="cart-container">
                <div class="cart-items">
                    <h2>Cart Items</h2>
                    <div id="cartItemsList">
                        Loading cart...
                    </div>
                    <div class="cart-actions" id="cartActions" style="display: none;">
                        <button onclick="clearCart()" class="clear-cart-btn">Clear Cart</button>
                        <button onclick="saveCartToServer()" class="clear-cart-btn" style="background: #2196f3;">
                            Save Cart
                        </button>
                    </div>
                </div>
                
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping:</span>
                        <span id="shipping">$5.00</span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (10%):</span>
                        <span id="tax">$0.00</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total:</span>
                        <span id="total">$0.00</span>
                    </div>
                    <button class="checkout-btn" id="checkoutBtn" 
                            <?php echo !isset($_SESSION['user_id']) ? 'disabled' : ''; ?>>
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Cart data
        let cart = JSON.parse(localStorage.getItem('medicare_cart')) || [];
        
        // Show notification
        function showNotification(message, isError = false) {
            const notification = isError 
                ? document.getElementById('errorNotification')
                : document.getElementById('successNotification');
            
            notification.textContent = message;
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 3000);
        }
        
        // Save cart to server
        async function saveCartToServer() {
            if (cart.length === 0) {
                showNotification('Cart is empty', true);
                return;
            }
            
            try {
                const response = await fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'save_cart',
                        cart: JSON.stringify(cart)
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('Cart saved successfully!');
                } else {
                    showNotification('Failed to save cart: ' + (result.message || 'Unknown error'), true);
                }
            } catch (error) {
                console.error('Error saving cart:', error);
                showNotification('Error saving cart to server', true);
            }
        }
        
        // Load cart from server
        async function loadCartFromServer() {
            try {
                const response = await fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'get_cart'
                    })
                });
                
                const serverCart = await response.json();
                
                if (serverCart && serverCart.length > 0) {
                    // Merge server cart with local cart
                    const mergedCart = mergeCarts(cart, serverCart);
                    if (JSON.stringify(mergedCart) !== JSON.stringify(cart)) {
                        cart = mergedCart;
                        localStorage.setItem('medicare_cart', JSON.stringify(cart));
                        renderCart();
                        updateCartCount();
                    }
                }
            } catch (error) {
                console.error('Error loading cart from server:', error);
                // Continue with local cart if server load fails
            }
        }
        
        // Merge carts function
        function mergeCarts(localCart, serverCart) {
            const merged = [...serverCart];
            
            localCart.forEach(localItem => {
                const existingIndex = merged.findIndex(item => item.id == localItem.id);
                if (existingIndex > -1) {
                    // Keep the larger quantity
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
        
        // Update cart count
        function updateCartCount() {
            let totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById('cartCount').textContent = totalItems;
            
            const badge = document.getElementById('cartBadge');
            if (totalItems > 0) {
                badge.textContent = totalItems;
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        }
        
        // Render cart items
        function renderCart() {
            const cartItemsList = document.getElementById('cartItemsList');
            const cartActions = document.getElementById('cartActions');
            
            if (cart.length === 0) {
                cartItemsList.innerHTML = `
                    <div class="empty-cart">
                        <h3>Your cart is empty</h3>
                        <p>Add some medicines from our catalog</p>
                        <a href="catlog.php" class="btn-continue-shopping">Continue Shopping</a>
                    </div>
                `;
                cartActions.style.display = 'none';
                updateSummary();
                return;
            }
            
            let html = '';
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                html += `
                <div class="cart-item" data-index="${index}">
                    <div>
                        <div class="item-name">${item.name}</div>
                        <div class="item-category">${item.category}</div>
                    </div>
                    <div class="item-price">$${item.price.toFixed(2)}</div>
                    <div class="item-quantity">
                        <button class="qty-btn minus" onclick="updateCartQuantity(${index}, -1)">-</button>
                        <input type="number" class="qty-input" value="${item.quantity}" 
                               min="1" onchange="updateCartQuantity(${index}, 0, this.value)">
                        <button class="qty-btn plus" onclick="updateCartQuantity(${index}, 1)">+</button>
                    </div>
                    <div class="item-total">$${itemTotal.toFixed(2)}</div>
                    <button class="remove-btn" onclick="removeFromCart(${index})">Remove</button>
                </div>
                `;
            });
            
            cartItemsList.innerHTML = html;
            cartActions.style.display = 'flex';
            updateSummary();
        }
        
        // Update cart quantity
        function updateCartQuantity(index, change, newValue = null) {
            if (newValue !== null) {
                cart[index].quantity = parseInt(newValue) || 1;
            } else {
                cart[index].quantity += change;
                if (cart[index].quantity < 1) cart[index].quantity = 1;
            }
            
            localStorage.setItem('medicare_cart', JSON.stringify(cart));
            renderCart();
            updateCartCount();
            
            // Auto-save to server if logged in
            <?php if(isset($_SESSION['user_id'])): ?>
            saveCartToServer();
            <?php endif; ?>
        }
        
        // Remove item from cart
        function removeFromCart(index) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                cart.splice(index, 1);
                localStorage.setItem('medicare_cart', JSON.stringify(cart));
                renderCart();
                updateCartCount();
                
                // Auto-save to server if logged in
                <?php if(isset($_SESSION['user_id'])): ?>
                saveCartToServer();
                <?php endif; ?>
            }
        }
        
        // Clear entire cart
        function clearCart() {
            if (confirm('Are you sure you want to clear your entire cart?')) {
                cart = [];
                localStorage.removeItem('medicare_cart');
                renderCart();
                updateCartCount();
                
                // Clear from server if logged in
                <?php if(isset($_SESSION['user_id'])): ?>
                saveCartToServer();
                <?php endif; ?>
            }
        }
        
        // Update order summary
        function updateSummary() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const shipping = 5.00;
            const tax = subtotal * 0.10;
            const total = subtotal + shipping + tax;
            
            document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('shipping').textContent = '$' + shipping.toFixed(2);
            document.getElementById('tax').textContent = '$' + tax.toFixed(2);
            document.getElementById('total').textContent = '$' + total.toFixed(2);
        }
        
        // Checkout functionality
        async function processCheckout() {
            if (cart.length === 0) {
                showNotification('Your cart is empty!', true);
                return;
            }
            
            // Validate stock before checkout
            try {
                // First check if all items are in stock
                const stockCheck = await checkStock();
                if (!stockCheck.available) {
                    showNotification(`Sorry, "${stockCheck.itemName}" is out of stock or insufficient quantity.`, true);
                    return;
                }
                
                if (confirm(`Proceed with checkout? Total: $${(cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) + 5.00 + (cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) * 0.10)).toFixed(2)}`)) {
                    
                    showNotification('Processing your order...');
                    
                    const response = await fetch('cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            action: 'checkout',
                            cart: JSON.stringify(cart)
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // Clear cart on success
                        cart = [];
                        localStorage.removeItem('medicare_cart');
                        renderCart();
                        updateCartCount();
                        
                        showNotification(`Order #${result.order_number} placed successfully! Total: $${result.total.toFixed(2)}`);
                        
                        // Redirect to order confirmation page
                        setTimeout(() => {
                            window.location.href = `order_confirmation.php?order_id=${result.order_id}`;
                        }, 2000);
                        
                    } else {
                        showNotification('Checkout failed: ' + (result.message || 'Unknown error'), true);
                    }
                }
            } catch (error) {
                console.error('Checkout error:', error);
                showNotification('Error during checkout. Please try again.', true);
            }
        }
        
        // Check stock availability
        async function checkStock() {
            try {
                // Send cart to check stock
                const response = await fetch('check_stock.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ cart: cart })
                });
                
                return await response.json();
            } catch (error) {
                console.error('Stock check error:', error);
                return { available: true }; // Assume available if check fails
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            renderCart();
            
            <?php if(isset($_SESSION['user_id'])): ?>
            // Load cart from server for logged-in users
            loadCartFromServer();
            <?php endif; ?>
            
            // Checkout button click handler
            document.getElementById('checkoutBtn').addEventListener('click', function() {
                <?php if(!isset($_SESSION['user_id'])): ?>
                    showNotification('Please login to proceed with checkout.', true);
                    window.location.href = 'login.html';
                    return;
                <?php endif; ?>
                
                processCheckout();
            });
        });
    </script>
    <script src="chatbot.js"></script>
</body>
</html>