<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = strtolower(trim($input['message'] ?? ''));
    
    $response = getChatbotResponse($message);
    echo json_encode(['response' => $response]);
    exit;
}

function getChatbotResponse($message) {
    // Project information queries
    if (strpos($message, 'about') !== false || strpos($message, 'what is') !== false || strpos($message, 'medicare') !== false) {
        return "MediCare is an online pharmacy. You can buy medicines online. We deliver to your home. Safe and easy to use.";
    }
    
    // Features queries
    if (strpos($message, 'features') !== false || strpos($message, 'what can') !== false || strpos($message, 'services') !== false) {
        return "Our features: 1) Buy medicines online 2) Upload prescription 3) Fast delivery 4) Track orders 5) 24/7 chat help 6) Safe payments 7) Medicine search";
    }
    
    // How to use
    if (strpos($message, 'how to') !== false || strpos($message, 'how do') !== false || strpos($message, 'use') !== false) {
        return "How to use: 1) Sign up for account 2) Browse medicines 3) Add to cart 4) Upload prescription if needed 5) Pay online 6) Get delivery at home";
    }
    
    // Registration/signup
    if (strpos($message, 'signup') !== false || strpos($message, 'register') !== false || strpos($message, 'account') !== false) {
        return "To create account: Click 'Sign Up' button. Enter your name, email, phone number. Create password. Then you can buy medicines.";
    }
    
    // Login
    if (strpos($message, 'login') !== false || strpos($message, 'sign in') !== false) {
        return "To login: Click 'Login' button. Enter your email and password. Then you can see your orders and buy medicines.";
    }
    
    // Medicine search/catalog
    if (strpos($message, 'find medicine') !== false || strpos($message, 'search') !== false || strpos($message, 'catalog') !== false) {
        return "To find medicines: Go to 'Catalog' page. Use search box. Type medicine name. See all available medicines with prices.";
    }
    
    // Cart/shopping
    if (strpos($message, 'cart') !== false || strpos($message, 'buy') !== false || strpos($message, 'purchase') !== false) {
        return "To buy medicines: Add medicines to cart. Click cart icon. Check your items. Enter delivery address. Make payment. Order confirmed!";
    }
    
    // Emergency/urgent queries
    if (strpos($message, 'emergency') !== false || strpos($message, 'urgent') !== false) {
        return "For medical emergency: Call 911 or go to hospital. For urgent medicine: Call us +91-9876543210";
    }
    
    // Medicine queries
    if (strpos($message, 'medicine') !== false || strpos($message, 'drug') !== false || strpos($message, 'tablet') !== false) {
        return "We have many medicines. Search by name. Check prices. Read details. Ask doctor before taking new medicine.";
    }
    
    // Prescription queries
    if (strpos($message, 'prescription') !== false || strpos($message, 'rx') !== false) {
        return "For prescription medicines: Take photo of prescription. Upload when buying. Our pharmacist checks it. Then we send medicine.";
    }
    
    // Order/delivery queries
    if (strpos($message, 'order') !== false || strpos($message, 'delivery') !== false || strpos($message, 'shipping') !== false) {
        return "Orders: Check 'My Orders' to see status. Delivery takes 2-3 days. Free delivery above ₹500. Track your order online.";
    }
    
    // Payment queries
    if (strpos($message, 'payment') !== false || strpos($message, 'pay') !== false || strpos($message, 'cost') !== false) {
        return "Payment: Use credit card, debit card, UPI, or cash on delivery. All payments are safe and secure.";
    }
    
    // Contact/support
    if (strpos($message, 'contact') !== false || strpos($message, 'help') !== false || strpos($message, 'support') !== false) {
        return "Need help? Call +91-9876543210. Email: support@medicare.com. Chat with me anytime. We are here 24/7.";
    }
    
    // Admin features
    if (strpos($message, 'admin') !== false || strpos($message, 'manage') !== false) {
        return "Admin can: Add new medicines. Check all orders. Update order status. Manage inventory. View customer details.";
    }
    
    // Technology/technical
    if (strpos($message, 'technology') !== false || strpos($message, 'built') !== false || strpos($message, 'made') !== false) {
        return "MediCare is built with: PHP for backend. HTML/CSS for design. JavaScript for interactions. MySQL database. Works on all devices.";
    }
    
    // Safety/security
    if (strpos($message, 'safe') !== false || strpos($message, 'secure') !== false || strpos($message, 'security') !== false) {
        return "Your data is safe. Payments are secure. Only licensed pharmacists verify prescriptions. All medicines are genuine.";
    }
    
    // Greetings
    $greetings = ['hello', 'hi', 'hey', 'good morning', 'good evening', 'good afternoon'];
    foreach ($greetings as $greeting) {
        if (strpos($message, $greeting) !== false) {
            return "Hello! I'm MediCare chatbot. I know everything about our online pharmacy. Ask me anything!";
        }
    }
    
    // Thank you
    if (strpos($message, 'thank') !== false || strpos($message, 'thanks') !== false) {
        return "You're welcome! Ask me more questions about MediCare. I'm here to help!";
    }
    
    // Default response
    return "I can tell you about: MediCare features, how to buy medicines, create account, upload prescription, track orders, payments, and more. What do you want to know?";
}
?>
