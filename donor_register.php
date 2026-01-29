<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $blood_group = $_POST['blood_group'];
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $address = $_POST['address'];
    $medical_conditions = $_POST['medical_conditions'];
    
    $conn = getDBConnection();
    if ($conn) {
        $stmt = $conn->prepare("INSERT INTO blood_donors (name, email, phone, blood_group, age, weight, address, medical_conditions) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssidss", $name, $email, $phone, $blood_group, $age, $weight, $address, $medical_conditions);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You are now a registered blood donor.";
        } else {
            $error = "Registration failed. Email might already exist.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Registration - MediCare Blood Bank</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="chatbot.css">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">MediCare Blood Bank</span>
            </div>
            <ul class="nav-links">
                <li><a href="blood_bank.html">Blood Bank</a></li>
                <li><a href="home.html">Home</a></li>
            </ul>
        </div>
    </nav>

    <div class="container" style="max-width: 600px; margin: 50px auto; padding: 30px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h2 style="text-align: center; color: #e74c3c; margin-bottom: 30px;">🩸 Become a Blood Donor</h2>
        
        <?php if (isset($success)): ?>
            <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" style="display: grid; gap: 20px;">
            <div>
                <label>Full Name *</label>
                <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label>Email *</label>
                    <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label>Phone *</label>
                    <input type="tel" name="phone" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div>
                    <label>Blood Group *</label>
                    <select name="blood_group" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="">Select</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div>
                    <label>Age *</label>
                    <input type="number" name="age" min="18" max="65" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label>Weight (kg) *</label>
                    <input type="number" name="weight" min="50" step="0.1" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div>
                <label>Address *</label>
                <textarea name="address" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; height: 80px;"></textarea>
            </div>
            
            <div>
                <label>Medical Conditions (if any)</label>
                <textarea name="medical_conditions" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; height: 60px;" placeholder="Any medical conditions, medications, or allergies"></textarea>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; font-size: 14px;">
                <strong>Eligibility Criteria:</strong><br>
                • Age: 18-65 years<br>
                • Weight: Minimum 50 kg<br>
                • Good health condition<br>
                • No recent illness or medication
            </div>
            
            <button type="submit" style="background: #e74c3c; color: white; padding: 15px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                Register as Donor
            </button>
        </form>
    </div>

    <script src="chatbot.js"></script>
</body>
</html>
