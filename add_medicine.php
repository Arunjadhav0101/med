<?php
// Start session
session_start();

// Include database configuration
require_once 'config.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = $_POST['price'] ?? '';
    $quantity = $_POST['quantity'] ?? '0';
    $manufacturer = $_POST['manufacturer'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($name)) $errors[] = 'Medicine name is required';
    if (empty($category)) $errors[] = 'Category is required';
    if (empty($price) || !is_numeric($price) || $price <= 0) $errors[] = 'Valid price is required';
    if (!is_numeric($quantity) || $quantity < 0) $errors[] = 'Quantity must be a non-negative number';
    
    if (empty($errors)) {
        $conn = getDBConnection();
        
        if ($conn) {
            // Prepare SQL statement - FIXED VERSION
            $sql = "INSERT INTO medicines (name, description, category, price, quantity, manufacturer, expiry_date) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            
            if ($stmt) {
                // Convert to proper types
                $price = (float)$price;
                $quantity = (int)$quantity;
                
                // Handle empty expiry date
                if (empty($expiry_date)) {
                    $expiry_date = null;
                }
                
                // Debug: Check values
                error_log("Name: $name");
                error_log("Description: $description");
                error_log("Category: $category");
                error_log("Price: $price");
                error_log("Quantity: $quantity");
                error_log("Manufacturer: $manufacturer");
                error_log("Expiry: $expiry_date");
                
                // CORRECT bind_param - 7 parameters total
                // "sssdiss" means: 
                // s = string (name)
                // s = string (description)
                // s = string (category)
                // d = double (price)
                // i = integer (quantity)
                // s = string (manufacturer)
                // s = string (expiry_date) - or NULL
                
                $stmt->bind_param(
                    "sssdiss",  // 7 characters for 7 parameters
                    $name,
                    $description,
                    $category,
                    $price,
                    $quantity,
                    $manufacturer,
                    $expiry_date
                );
                
                if ($stmt->execute()) {
                    $message = 'Medicine added successfully!';
                    $message_type = 'success';
                    $_POST = []; // Clear form
                } else {
                    $message = 'Error adding medicine: ' . $stmt->error;
                    $message_type = 'error';
                }
                
                $stmt->close();
            } else {
                $message = 'Failed to prepare statement: ' . $conn->error;
                $message_type = 'error';
            }
            
            $conn->close();
        } else {
            $message = 'Database connection failed';
            $message_type = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine - MediCare</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-size: 24px;
            font-weight: 700;
            color: #2196f3;
        }
        
        .logo-icon {
            margin-right: 10px;
            font-size: 28px;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
            gap: 25px;
            align-items: center;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #2196f3;
        }
        
        .nav-links a.active {
            color: #2196f3;
            font-weight: 600;
        }
        
        .btn-login, .btn-signup {
            padding: 8px 20px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .btn-login {
            border: 2px solid #2196f3;
            color: #2196f3;
        }
        
        .btn-signup {
            background: #2196f3;
            color: white;
        }
        
        .add-medicine-page {
            padding: 40px 0;
        }
        
        .add-medicine-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .add-medicine-header h1 {
            font-size: 36px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .add-medicine-header p {
            color: #666;
            font-size: 16px;
        }
        
        .medicine-form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: #2196f3;
            outline: none;
        }
        
        .btn-submit {
            background: #4caf50;
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        
        .btn-submit:hover {
            background: #45a049;
        }
        
        .message {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #2196f3;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .required {
            color: #f44336;
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
                <li><a href="index.php">Home</a></li>
                <li><a href="catalog.php">Catalog</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="add_medicine.php" class="active">Add Medicine</a></li>
                <li><a href="login.php" class="btn-login">Login</a></li>
                <li><a href="signup.php" class="btn-signup">Sign Up</a></li>
            </ul>
        </div>
    </nav>

    <div class="add-medicine-page">
        <div class="container">
            <div class="add-medicine-header">
                <h1>Add New Medicine</h1>
                <p>Add a new medicine to the catalog</p>
            </div>

            <div class="medicine-form">
                <?php if ($message): ?>
                    <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Medicine Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Pain Relief" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Pain Relief') ? 'selected' : ''; ?>>Pain Relief</option>
                            <option value="Antibiotic" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Antibiotic') ? 'selected' : ''; ?>>Antibiotic</option>
                            <option value="Allergy" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Allergy') ? 'selected' : ''; ?>>Allergy</option>
                            <option value="Digestive" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Digestive') ? 'selected' : ''; ?>>Digestive</option>
                            <option value="Diabetes" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Diabetes') ? 'selected' : ''; ?>>Diabetes</option>
                            <option value="Blood Pressure" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Blood Pressure') ? 'selected' : ''; ?>>Blood Pressure</option>
                            <option value="Cholesterol" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Cholesterol') ? 'selected' : ''; ?>>Cholesterol</option>
                            <option value="Supplement" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Supplement') ? 'selected' : ''; ?>>Supplement</option>
                            <option value="Cold & Flu" <?php echo (isset($_POST['category']) && $_POST['category'] == 'Cold & Flu') ? 'selected' : ''; ?>>Cold & Flu</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price ($) <span class="required">*</span></label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01" 
                               value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="0" 
                               value="<?php echo htmlspecialchars($_POST['quantity'] ?? '0'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="manufacturer">Manufacturer</label>
                        <input type="text" id="manufacturer" name="manufacturer" 
                               value="<?php echo htmlspecialchars($_POST['manufacturer'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="expiry_date">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" 
                               value="<?php echo htmlspecialchars($_POST['expiry_date'] ?? ''); ?>">
                    </div>
                    
                    <button type="submit" class="btn-submit">Add Medicine</button>
                </form>
                
                <a href="catalog.php" class="back-link">← Back to Catalog</a>
            </div>
        </div>
    </div>
</body>
</html>