<?php
session_start();
require_once 'config.php';

// Simple admin check (you can enhance this)
$is_admin = true; // Set to false for production

if (!$is_admin) {
    header('Location: login.html');
    exit;
}

$conn = getDBConnection();

// Handle actions
if ($_POST['action'] ?? '' === 'approve_request') {
    $request_id = $_POST['request_id'];
    $stmt = $conn->prepare("UPDATE blood_requests SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
}

if ($_POST['action'] ?? '' === 'add_inventory') {
    $blood_group = $_POST['blood_group'];
    $units = $_POST['units'];
    $expiry_date = $_POST['expiry_date'];
    
    $stmt = $conn->prepare("INSERT INTO blood_inventory (blood_group, units_available, expiry_date, collection_date) VALUES (?, ?, ?, CURDATE())");
    $stmt->bind_param("sis", $blood_group, $units, $expiry_date);
    $stmt->execute();
}

// Get data
$requests = $conn->query("SELECT * FROM blood_requests ORDER BY urgency DESC, created_at DESC")->fetch_all(MYSQLI_ASSOC);
$donors = $conn->query("SELECT * FROM blood_donors ORDER BY created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);
$inventory = $conn->query("SELECT blood_group, SUM(units_available) as total FROM blood_inventory WHERE status = 'available' GROUP BY blood_group")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Bank Admin - MediCare</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        .admin-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
        .admin-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th, .admin-table td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 14px; }
        .admin-table th { background: #f5f5f5; }
        .btn-small { padding: 5px 10px; font-size: 12px; border: none; border-radius: 3px; cursor: pointer; }
        .btn-approve { background: #27ae60; color: white; }
        .btn-reject { background: #e74c3c; color: white; }
        .status-emergency { color: #e74c3c; font-weight: bold; }
        .status-urgent { color: #f39c12; font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <div class="logo">
                <span class="logo-icon">🩸</span>
                <span class="logo-text">Blood Bank Admin - by Arun Jadhav</span>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <h2>Blood Bank Management Dashboard - Created by Arun Jadhav</h2>
        
        <div class="admin-grid">
            <!-- Blood Inventory -->
            <div class="admin-card">
                <h3>Current Inventory</h3>
                <table class="admin-table">
                    <tr><th>Blood Group</th><th>Units</th></tr>
                    <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?php echo $item['blood_group']; ?></td>
                        <td><?php echo $item['total']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                
                <h4>Add Inventory</h4>
                <form method="POST" style="display: grid; gap: 10px;">
                    <input type="hidden" name="action" value="add_inventory">
                    <select name="blood_group" required>
                        <option value="">Select Blood Group</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                    <input type="number" name="units" placeholder="Units" required>
                    <input type="date" name="expiry_date" required>
                    <button type="submit" class="btn-small btn-approve">Add Stock</button>
                </form>
            </div>

            <!-- Blood Requests -->
            <div class="admin-card">
                <h3>Blood Requests</h3>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="admin-table">
                        <tr><th>Patient</th><th>Blood</th><th>Units</th><th>Urgency</th><th>Action</th></tr>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                            <td><?php echo $request['blood_group']; ?></td>
                            <td><?php echo $request['units_needed']; ?></td>
                            <td class="status-<?php echo $request['urgency']; ?>">
                                <?php echo strtoupper($request['urgency']); ?>
                            </td>
                            <td>
                                <?php if ($request['status'] === 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="approve_request">
                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                    <button type="submit" class="btn-small btn-approve">Approve</button>
                                </form>
                                <?php else: ?>
                                <span style="color: #27ae60;">✓ <?php echo ucfirst($request['status']); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <!-- Recent Donors -->
            <div class="admin-card">
                <h3>Recent Donors</h3>
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="admin-table">
                        <tr><th>Name</th><th>Blood Group</th><th>Phone</th><th>Status</th></tr>
                        <?php foreach ($donors as $donor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donor['name']); ?></td>
                            <td><?php echo $donor['blood_group']; ?></td>
                            <td><?php echo $donor['phone']; ?></td>
                            <td><?php echo ucfirst($donor['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
