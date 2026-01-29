CREATE TABLE blood_donors (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15) NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    age INT NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    address TEXT NOT NULL,
    last_donation_date DATE,
    medical_conditions TEXT,
    status ENUM('active', 'inactive', 'blocked') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE blood_inventory (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units_available INT DEFAULT 0,
    expiry_date DATE NOT NULL,
    collection_date DATE NOT NULL,
    donor_id INT,
    status ENUM('available', 'reserved', 'expired', 'used') DEFAULT 'available',
    FOREIGN KEY (donor_id) REFERENCES blood_donors(id)
);

CREATE TABLE blood_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_name VARCHAR(100) NOT NULL,
    patient_phone VARCHAR(15) NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units_needed INT NOT NULL,
    hospital_name VARCHAR(200) NOT NULL,
    hospital_address TEXT NOT NULL,
    urgency ENUM('emergency', 'urgent', 'normal') DEFAULT 'normal',
    required_date DATE NOT NULL,
    status ENUM('pending', 'approved', 'fulfilled', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE blood_donations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    donor_id INT NOT NULL,
    donation_date DATE NOT NULL,
    units_donated INT DEFAULT 1,
    hemoglobin_level DECIMAL(4,2),
    blood_pressure VARCHAR(20),
    status ENUM('completed', 'rejected', 'pending') DEFAULT 'completed',
    notes TEXT,
    FOREIGN KEY (donor_id) REFERENCES blood_donors(id)
);

-- Insert sample data
INSERT INTO blood_donors (name, email, phone, blood_group, age, weight, address) VALUES
('John Doe', 'john@email.com', '9876543210', 'O+', 25, 70.5, '123 Main St, City'),
('Jane Smith', 'jane@email.com', '9876543211', 'A+', 30, 65.0, '456 Oak Ave, City'),
('Mike Johnson', 'mike@email.com', '9876543212', 'B+', 28, 75.0, '789 Pine Rd, City');

INSERT INTO blood_inventory (blood_group, units_available, expiry_date, collection_date) VALUES
('O+', 15, '2026-02-28', '2026-01-15'),
('A+', 12, '2026-02-25', '2026-01-12'),
('B+', 8, '2026-02-20', '2026-01-08'),
('AB+', 5, '2026-02-18', '2026-01-05');
