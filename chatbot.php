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
    // Emergency/urgent queries
    if (strpos($message, 'emergency') !== false || strpos($message, 'urgent') !== false) {
        return "For medical emergencies, please call 911 or visit your nearest emergency room. For urgent medicine needs, call our 24/7 helpline: +91-9876543210";
    }
    
    // Medicine queries
    if (strpos($message, 'medicine') !== false || strpos($message, 'drug') !== false || strpos($message, 'tablet') !== false) {
        return "I can help you find medicines! Browse our catalog or search for specific medications. Need help with dosage or side effects? Please consult your doctor or pharmacist.";
    }
    
    // Prescription queries
    if (strpos($message, 'prescription') !== false || strpos($message, 'rx') !== false) {
        return "For prescription medicines: 1) Upload clear photo of prescription 2) Our pharmacists verify within 30 minutes 3) We'll process your order. Valid prescriptions required for all Rx medications.";
    }
    
    // Order/delivery queries
    if (strpos($message, 'order') !== false || strpos($message, 'delivery') !== false || strpos($message, 'shipping') !== false) {
        return "Order tracking: Check 'My Orders' section. Standard delivery: 2-3 business days. Express delivery: Same day (metro cities). Free shipping on orders above ₹500.";
    }
    
    // Dosage/side effects
    if (strpos($message, 'dosage') !== false || strpos($message, 'dose') !== false || strpos($message, 'side effect') !== false) {
        return "For dosage information and side effects, please consult the medicine packaging or speak with our licensed pharmacist. Call +91-9876543210 for professional advice.";
    }
    
    // Generic/brand queries
    if (strpos($message, 'generic') !== false || strpos($message, 'brand') !== false) {
        return "We offer both generic and branded medicines. Generic medicines contain the same active ingredients as brands but cost 30-80% less. Need help choosing? Our pharmacists can guide you.";
    }
    
    // Payment queries
    if (strpos($message, 'payment') !== false || strpos($message, 'pay') !== false || strpos($message, 'cost') !== false) {
        return "Payment options: Credit/Debit cards, UPI, Net Banking, Cash on Delivery. We accept insurance claims. HSA/FSA cards accepted for eligible purchases.";
    }
    
    // Store hours/contact
    if (strpos($message, 'hours') !== false || strpos($message, 'time') !== false || strpos($message, 'contact') !== false || strpos($message, 'phone') !== false) {
        return "We're online 24/7! Customer service: +91-9876543210 | Pharmacist consultation: Available 8 AM - 10 PM | Email: support@medicare.com";
    }
    
    // Return/refund
    if (strpos($message, 'return') !== false || strpos($message, 'refund') !== false || strpos($message, 'cancel') !== false) {
        return "Returns: Unopened medicines within 30 days. Prescription medicines cannot be returned once opened. Damaged items: Full refund within 7 days. Contact us for return process.";
    }
    
    // Insurance
    if (strpos($message, 'insurance') !== false) {
        return "We accept most major insurance plans. Upload your insurance card during checkout. Copay amounts vary by plan. For coverage questions, call your insurance provider.";
    }
    
    // Greetings
    $greetings = ['hello', 'hi', 'hey', 'good morning', 'good evening', 'good afternoon'];
    foreach ($greetings as $greeting) {
        if (strpos($message, $greeting) !== false) {
            return "Hello! I'm your MediCare assistant. I can help with medicine information, prescriptions, orders, and pharmacy services. What do you need help with?";
        }
    }
    
    // Thank you
    if (strpos($message, 'thank') !== false || strpos($message, 'thanks') !== false) {
        return "You're welcome! Is there anything else I can help you with today?";
    }
    
    // Default response
    return "I'm here to help with: 🔹 Medicine information 🔹 Prescription uploads 🔹 Order tracking 🔹 Payment & insurance 🔹 Store policies. What would you like to know?";
}
?>
