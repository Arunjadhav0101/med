<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = strtolower(trim($input['message'] ?? ''));
    
    $response = getChatbotResponse($message);
    echo json_encode($response);
    exit;
}

function getChatbotResponse($message) {
    // Check if user wants to buy/order medicine
    if (strpos($message, 'buy') !== false || strpos($message, 'order') !== false || strpos($message, 'need') !== false || strpos($message, 'want') !== false) {
        return handleMedicineRequest($message);
    }
    
    // Project information queries
    if (strpos($message, 'about') !== false || strpos($message, 'what is') !== false || strpos($message, 'medicare') !== false) {
        return ['response' => "MediCare is an online pharmacy. You can buy medicines online. We deliver to your home. Safe and easy to use."];
    }
    
    // Features queries
    if (strpos($message, 'features') !== false || strpos($message, 'what can') !== false || strpos($message, 'services') !== false) {
        return ['response' => "Our features: 1) Buy medicines online 2) Upload prescription 3) Fast delivery 4) Track orders 5) 24/7 chat help 6) Safe payments 7) Medicine search"];
    }
    
    // How to use
    if (strpos($message, 'how to') !== false || strpos($message, 'how do') !== false || strpos($message, 'use') !== false) {
        return ['response' => "How to use: 1) Sign up for account 2) Browse medicines 3) Add to cart 4) Upload prescription if needed 5) Pay online 6) Get delivery at home"];
    }
    
    // Registration/signup
    if (strpos($message, 'signup') !== false || strpos($message, 'register') !== false || strpos($message, 'account') !== false) {
        return ['response' => "To create account: Click 'Sign Up' button. Enter your name, email, phone number. Create password. Then you can buy medicines."];
    }
    
    // Login
    if (strpos($message, 'login') !== false || strpos($message, 'sign in') !== false) {
        return ['response' => "To login: Click 'Login' button. Enter your email and password. Then you can see your orders and buy medicines."];
    }
    
    // Medicine search/catalog
    if (strpos($message, 'find medicine') !== false || strpos($message, 'search') !== false || strpos($message, 'catalog') !== false) {
        return ['response' => "To find medicines: Go to 'Catalog' page. Use search box. Type medicine name. See all available medicines with prices."];
    }
    
    // Cart/shopping
    if (strpos($message, 'cart') !== false) {
        return ['response' => "To buy medicines: Add medicines to cart. Click cart icon. Check your items. Enter delivery address. Make payment. Order confirmed!"];
    }
    
    // Emergency/urgent queries
    if (strpos($message, 'emergency') !== false || strpos($message, 'urgent') !== false) {
        return ['response' => "For medical emergency: Call 911 or go to hospital. For urgent medicine: Call us +91-9876543210"];
    }
    
    // Medicine queries
    if (strpos($message, 'medicine') !== false || strpos($message, 'drug') !== false || strpos($message, 'tablet') !== false) {
        return ['response' => "We have many medicines. Search by name. Check prices. Read details. Ask doctor before taking new medicine."];
    }
    
    // Prescription queries
    if (strpos($message, 'prescription') !== false || strpos($message, 'rx') !== false) {
        return ['response' => "For prescription medicines: Take photo of prescription. Upload when buying. Our pharmacist checks it. Then we send medicine."];
    }
    
    // Order/delivery queries
    if (strpos($message, 'delivery') !== false || strpos($message, 'shipping') !== false) {
        return ['response' => "Orders: Check 'My Orders' to see status. Delivery takes 2-3 days. Free delivery above ₹500. Track your order online."];
    }
    
    // Payment queries
    if (strpos($message, 'payment') !== false || strpos($message, 'pay') !== false || strpos($message, 'cost') !== false) {
        return ['response' => "Payment: Use credit card, debit card, UPI, or cash on delivery. All payments are safe and secure."];
    }
    
    // Contact/support
    if (strpos($message, 'contact') !== false || strpos($message, 'help') !== false || strpos($message, 'support') !== false) {
        return ['response' => "Need help? Call +91-9876543210. Email: support@medicare.com. Chat with me anytime. We are here 24/7."];
    }
    
    // Admin features
    if (strpos($message, 'admin') !== false || strpos($message, 'manage') !== false) {
        return ['response' => "Admin can: Add new medicines. Check all orders. Update order status. Manage inventory. View customer details."];
    }
    
    // Technology/technical
    if (strpos($message, 'technology') !== false || strpos($message, 'built') !== false || strpos($message, 'made') !== false) {
        return ['response' => "MediCare is built with: PHP for backend. HTML/CSS for design. JavaScript for interactions. MySQL database. Works on all devices."];
    }
    
    // Safety/security
    if (strpos($message, 'safe') !== false || strpos($message, 'secure') !== false || strpos($message, 'security') !== false) {
        return ['response' => "Your data is safe. Payments are secure. Only licensed pharmacists verify prescriptions. All medicines are genuine."];
    }
    
    // Greetings
    $greetings = ['hello', 'hi', 'hey', 'good morning', 'good evening', 'good afternoon'];
    foreach ($greetings as $greeting) {
        if (strpos($message, $greeting) !== false) {
            return ['response' => "Hello! I'm MediCare chatbot. I can help you buy medicines automatically. Just tell me what you need!"];
        }
    }
    
    // Thank you
    if (strpos($message, 'thank') !== false || strpos($message, 'thanks') !== false) {
        return ['response' => "You're welcome! Ask me more questions about MediCare. I'm here to help!"];
    }
    
    // Default response
    return ['response' => "I can help you buy medicines automatically! Just say 'I need paracetamol' or 'buy aspirin'. I'll handle everything for you!"];
}

function handleMedicineRequest($message) {
    // Extract medicine name from message
    $medicineName = extractMedicineName($message);
    
    if (!$medicineName) {
        return [
            'response' => "What medicine do you need? Please tell me the name like 'I need paracetamol' or 'buy aspirin'.",
            'action' => 'ask_medicine'
        ];
    }
    
    // Search for medicine in database
    $medicine = searchMedicine($medicineName);
    
    if (!$medicine) {
        return [
            'response' => "Sorry, I couldn't find '$medicineName'. Try different spelling or check our catalog for available medicines.",
            'action' => 'medicine_not_found'
        ];
    }
    
    // Check if prescription needed
    if ($medicine['prescription_required'] == 1) {
        return [
            'response' => "Found {$medicine['name']} - ₹{$medicine['price']}. This needs prescription. I can help you order it! Do you have prescription?",
            'action' => 'prescription_required',
            'medicine' => $medicine
        ];
    }
    
    // Medicine found and no prescription needed
    return [
        'response' => "Great! Found {$medicine['name']} - ₹{$medicine['price']}. I'll add it to your cart automatically. How many do you need?",
        'action' => 'add_to_cart',
        'medicine' => $medicine
    ];
}

function extractMedicineName($message) {
    // Common medicine names to look for
    $medicines = ['paracetamol', 'aspirin', 'ibuprofen', 'crocin', 'dolo', 'combiflam', 'disprin', 'brufen'];
    
    foreach ($medicines as $med) {
        if (strpos($message, $med) !== false) {
            return $med;
        }
    }
    
    // Try to extract medicine name after keywords
    $keywords = ['buy', 'need', 'want', 'order', 'get'];
    foreach ($keywords as $keyword) {
        $pos = strpos($message, $keyword);
        if ($pos !== false) {
            $afterKeyword = trim(substr($message, $pos + strlen($keyword)));
            $words = explode(' ', $afterKeyword);
            if (!empty($words[0]) && strlen($words[0]) > 2) {
                return $words[0];
            }
        }
    }
    
    return null;
}

function searchMedicine($name) {
    $conn = getDBConnection();
    if (!$conn) return null;
    
    $stmt = $conn->prepare("SELECT * FROM medicines WHERE name LIKE ? OR generic_name LIKE ? LIMIT 1");
    $searchTerm = "%$name%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}
?>
