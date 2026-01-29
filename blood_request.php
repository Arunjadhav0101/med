<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_name = $_POST['patient_name'];
    $patient_phone = $_POST['patient_phone'];
    $blood_group = $_POST['blood_group'];
    $units_needed = $_POST['units_needed'];
    $hospital_name = $_POST['hospital_name'];
    $hospital_address = $_POST['hospital_address'];
    $urgency = $_POST['urgency'];
    $required_date = $_POST['required_date'];
    $notes = $_POST['notes'];
    
    $conn = getDBConnection();
    if ($conn) {
        $stmt = $conn->prepare("INSERT INTO blood_requests (patient_name, patient_phone, blood_group, units_needed, hospital_name, hospital_address, urgency, required_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssisssss", $patient_name, $patient_phone, $blood_group, $units_needed, $hospital_name, $hospital_address, $urgency, $required_date, $notes);
        
        if ($stmt->execute()) {
            $request_id = $conn->insert_id;
            $success = "Blood request submitted successfully! Request ID: #$request_id. We will contact you soon.";
        } else {
            $error = "Failed to submit request. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Request - MediCare Blood Bank</title>
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
        <h2 style="text-align: center; color: #e74c3c; margin-bottom: 30px;">🏥 Request Blood</h2>
        
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
                <label>Patient Name *</label>
                <input type="text" name="patient_name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label>Patient Phone *</label>
                    <input type="tel" name="patient_phone" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
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
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px;">
                <div>
                    <label>Units Needed *</label>
                    <input type="number" name="units_needed" min="1" max="10" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label>Urgency *</label>
                    <select name="urgency" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <div>
                    <label>Required Date *</label>
                    <input type="date" name="required_date" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
            </div>
            
            <div>
                <label>Hospital Name *</label>
                <input type="text" name="hospital_name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div>
                <label>Hospital Address *</label>
                <textarea name="hospital_address" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; height: 80px;"></textarea>
            </div>
            
            <div>
                <label>Additional Notes</label>
                <textarea name="notes" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; height: 60px;" placeholder="Any additional information about the patient or urgency"></textarea>
            </div>
            
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; font-size: 14px;">
                <strong>Emergency Contact:</strong> For immediate blood requirements, call <strong>+91-9876543210</strong>
            </div>
            
            <button type="submit" style="background: #e74c3c; color: white; padding: 15px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;">
                Submit Blood Request
            </button>
        </form>
    </div>

    <script src="chatbot.js"></script>
</body>
</html>
