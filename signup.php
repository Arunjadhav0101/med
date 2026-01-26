<?php
session_start();
require_once 'config.php';

// Function to generate toast notification page
function showToastPage($message, $type, $redirectUrl) {
    return <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration - Processing</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .loader {
                width: 50px;
                height: 50px;
                border: 5px solid rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                border-top-color: white;
                animation: spin 1s ease-in-out infinite;
                margin-bottom: 20px;
            }
            
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
            
            .processing-text {
                color: white;
                font-size: 18px;
                opacity: 0.9;
            }
        </style>
    </head>
    <body>
        <div style="text-align: center;">
            <div class="loader"></div>
            <div class="processing-text">Processing registration...</div>
        </div>

        <script>
            // Store toast message in localStorage
            localStorage.setItem('toastMessage', "$message");
            localStorage.setItem('toastType', "$type");
            
            // Redirect after a short delay
            setTimeout(() => {
                window.location.href = "$redirectUrl";
            }, 1000);
        </script>
    </body>
    </html>
HTML;
}

// Main processing logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $fullName = trim($_POST['fullName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    
    // Validation
    if (empty($fullName) || empty($email) || empty($password) || empty($phone)) {
        echo showToastPage('All fields are required!', 'error', 'signup.html');
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo showToastPage('Invalid email format!', 'error', 'signup.html');
        exit();
    }
    
    // Get database connection
    $conn = getDBConnection();
    
    if (!$conn) {
        echo showToastPage('Database connection failed!', 'error', 'signup.html');
        exit();
    }
    
    // Check if email exists
    $checkSql = "SELECT id FROM users WHERE email = ?";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        echo showToastPage('Database error!', 'error', 'signup.html');
        exit();
    }
    
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        $checkStmt->close();
        $conn->close();
        echo showToastPage('Email already exists! Please use a different email.', 'error', 'signup.html');
        exit();
    }
    $checkStmt->close();
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $insertSql = "INSERT INTO users (full_name, email, password, phone, created_at) 
                  VALUES (?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);
    
    if (!$insertStmt) {
        $conn->close();
        echo showToastPage('Database error!', 'error', 'signup.html');
        exit();
    }
    
    $insertStmt->bind_param("ssss", $fullName, $email, $hashedPassword, $phone);
    
    if ($insertStmt->execute()) {
        // Set session variables
        $_SESSION['user_id'] = $insertStmt->insert_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_name'] = $fullName;
        $_SESSION['logged_in'] = true;
        
        $insertStmt->close();
        $conn->close();
        
        echo showToastPage("Registration successful! Welcome, $fullName", 'success', 'home.html');
        exit();
    } else {
        $insertStmt->close();
        $conn->close();
        
        echo showToastPage('Registration failed. Please try again.', 'error', 'signup.html');
        exit();
    }
} else {
    header("Location: signup.html");
    exit();
}
?>