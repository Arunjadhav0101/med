<?php
require_once 'config.php';

$conn = getDBConnection();
$inventory = [];
$requests = [];

if ($conn) {
    // Get blood inventory
    $result = $conn->query("SELECT blood_group, SUM(units_available) as total_units FROM blood_inventory WHERE status = 'available' AND expiry_date > CURDATE() GROUP BY blood_group ORDER BY blood_group");
    while ($row = $result->fetch_assoc()) {
        $inventory[$row['blood_group']] = $row['total_units'];
    }
    
    // Get recent requests
    $result = $conn->query("SELECT * FROM blood_requests WHERE status = 'pending' ORDER BY urgency DESC, created_at DESC LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory - MediCare Blood Bank</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="chatbot.css">
    <style>
        .inventory-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .blood-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 5px solid #e74c3c;
        }
        .blood-type {
            font-size: 2em;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 10px;
        }
        .units-count {
            font-size: 1.5em;
            color: #333;
        }
        .status-available { border-left-color: #27ae60; }
        .status-low { border-left-color: #f39c12; }
        .status-critical { border-left-color: #e74c3c; }
        .requests-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .requests-table th, .requests-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .requests-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .urgency-emergency { color: #e74c3c; font-weight: bold; }
        .urgency-urgent { color: #f39c12; font-weight: bold; }
        .urgency-normal { color: #27ae60; }
    </style>
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

    <div class="container" style="margin: 50px auto; padding: 30px;">
        <h2 style="text-align: center; color: #e74c3c; margin-bottom: 30px;">📊 Blood Inventory Status</h2>
        
        <div style="text-align: center; margin-bottom: 30px; padding: 15px; background: #e8f5e8; border-radius: 10px;">
            <strong>Last Updated:</strong> <?php echo date('Y-m-d H:i:s'); ?> | 
            <strong>Emergency Hotline:</strong> +91-9876543210
        </div>

        <div class="inventory-grid">
            <?php
            $blood_groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
            foreach ($blood_groups as $group) {
                $units = $inventory[$group] ?? 0;
                $status_class = '';
                $status_text = '';
                
                if ($units == 0) {
                    $status_class = 'status-critical';
                    $status_text = 'Not Available';
                } elseif ($units < 5) {
                    $status_class = 'status-low';
                    $status_text = 'Low Stock';
                } else {
                    $status_class = 'status-available';
                    $status_text = 'Available';
                }
            ?>
            <div class="blood-card <?php echo $status_class; ?>">
                <div class="blood-type"><?php echo $group; ?></div>
                <div class="units-count"><?php echo $units; ?> Units</div>
                <div style="font-size: 12px; margin-top: 5px; color: #666;">
                    <?php echo $status_text; ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-top: 40px;">
            <h3 style="color: #e74c3c; margin-bottom: 20px;">🚨 Pending Blood Requests</h3>
            
            <?php if (empty($requests)): ?>
                <p style="text-align: center; color: #666; padding: 20px;">No pending requests at the moment.</p>
            <?php else: ?>
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Blood Group</th>
                            <th>Units</th>
                            <th>Hospital</th>
                            <th>Urgency</th>
                            <th>Required Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                            <td><strong><?php echo $request['blood_group']; ?></strong></td>
                            <td><?php echo $request['units_needed']; ?></td>
                            <td><?php echo htmlspecialchars($request['hospital_name']); ?></td>
                            <td class="urgency-<?php echo $request['urgency']; ?>">
                                <?php echo strtoupper($request['urgency']); ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($request['required_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-top: 40px;">
            <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h4 style="color: #e74c3c;">🩸 Donate Blood</h4>
                <p>Help save lives by donating blood. Every donation can save up to 3 lives.</p>
                <a href="donor_register.php" style="background: #e74c3c; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 10px;">
                    Register as Donor
                </a>
            </div>
            
            <div style="background: white; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h4 style="color: #e74c3c;">🏥 Request Blood</h4>
                <p>Need blood for a patient? Submit a request and we'll help you immediately.</p>
                <a href="blood_request.php" style="background: #e74c3c; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block; margin-top: 10px;">
                    Request Blood
                </a>
            </div>
        </div>
    </div>

    <script src="chatbot.js"></script>
</body>
</html>
