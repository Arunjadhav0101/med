<?php
session_start();
require_once 'config.php';

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit();
}

// Get form data
$email = trim($_POST['email']);
$password = $_POST['password'];
$userType = $_POST['userType'] ?? 'patient';

// Validate inputs
if (empty($email) || empty($password)) {
    header("Location: login.html?error=empty_fields");
    exit();
}

// Get database connection
$conn = getDBConnection();

if (!$conn) {
    header("Location: login.html?error=db_error");
    exit();
}

try {
    // Handle different user types
    switch ($userType) {
        case 'admin':
            // For Admin - check in users table with admin role
            $sql = "SELECT id, email, password, full_name, role FROM users WHERE email = ? AND role = 'admin'";
            break;
            
        case 'pharmacist':
            // For Pharmacist - you might need to create a pharmacists table
            // For now, we'll check in users table with a specific condition
            $sql = "SELECT id, email, password, full_name, role FROM users WHERE email = ? AND (role = 'pharmacist' OR role = 'admin')";
            break;
            
        case 'patient':
        default:
            // For Patient - check in users table with customer role
            $sql = "SELECT id, email, password, full_name, role FROM users WHERE email = ? AND role = 'customer'";
            break;
    }
    
    // Prepare and execute query
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set common session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['user_type'] = $userType;
            
            // Set specific session variables based on user type
            switch ($userType) {
                case 'admin':
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['user_role'] = 'admin';
                    // Redirect to admin dashboard
                    header("Location: admin_dashboard.php");
                    break;
                    
                case 'pharmacist':
                    $_SESSION['pharmacist_logged_in'] = true;
                    $_SESSION['user_role'] = 'pharmacist';
                    // Redirect to pharmacist dashboard
                    header("Location: pharmacist_dashboard.php");
                    break;
                    
                case 'patient':
                default:
                    $_SESSION['patient_logged_in'] = true;
                    $_SESSION['user_role'] = 'customer';
                    // Redirect to patient dashboard
                    header("Location: home.html");
                    break;
            }
            exit();
            
        } else {
            // Invalid password
            header("Location: login.html?error=invalid_password&userType=" . $userType);
            exit();
        }
    } else {
        // User not found
        header("Location: login.html?error=user_not_found&userType=" . $userType);
        exit();
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header("Location: login.html?error=server_error&userType=" . $userType);
    exit();
}
?>