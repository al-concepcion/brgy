-- Barangay ID Management System Database
-- Created: November 26, 2025

CREATE DATABASE IF NOT EXISTS barangay_db;
USE barangay_db;

-- Table for Registered Users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    contact_number VARCHAR(20),
    address TEXT,
    is_verified TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_verified (is_verified)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Barangay ID Applications
CREATE TABLE IF NOT EXISTS id_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    suffix VARCHAR(10),
    birth_date DATE NOT NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    civil_status ENUM('Single', 'Married', 'Widowed', 'Divorced', 'Separated'),
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    complete_address TEXT NOT NULL,
    preferred_pickup_date DATE,
    proof_of_residency VARCHAR(255),
    valid_id VARCHAR(255),
    id_photo VARCHAR(255),
    claim_method ENUM('pickup', 'delivery') DEFAULT 'pickup',
    price DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    status ENUM('Pending', 'Document Verification', 'Processing', 'Ready for Pickup', 'Completed', 'Rejected') DEFAULT 'Pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_reference (reference_number),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Certification Requests
CREATE TABLE IF NOT EXISTS certification_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) UNIQUE NOT NULL,
    certificate_type ENUM('residency', 'indigency', 'clearance', 'business', 'good_moral') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100),
    last_name VARCHAR(100) NOT NULL,
    complete_address TEXT NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    purpose TEXT NOT NULL,
    supporting_documents VARCHAR(255),
    claim_method ENUM('pickup', 'delivery') DEFAULT 'pickup',
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('Pending', 'Verification', 'Processing', 'Ready for Pickup', 'Ready for Delivery', 'Completed', 'Rejected') DEFAULT 'Pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reference (reference_number),
    INDEX idx_type (certificate_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Application Status History
CREATE TABLE IF NOT EXISTS status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_number VARCHAR(50) NOT NULL,
    application_type ENUM('ID', 'CERT') NOT NULL,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    remarks TEXT,
    updated_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reference (reference_number),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Contact Messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(200) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('New', 'Read', 'Replied', 'Archived') DEFAULT 'New',
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Admin Users
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('Admin', 'Staff') DEFAULT 'Staff',
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table for Announcements
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    badge_type ENUM('Important', 'New', 'Event') DEFAULT 'New',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (username: admin, password: Admin@123)
INSERT INTO admin_users (username, password, full_name, email, role) 
VALUES ('admin', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1pxCVNdE5S7.kHZJkSdqIhJC7v7pq8m', 'System Administrator', 'admin@barangaysantonio.gov.ph', 'Admin');

-- Insert sample announcements
INSERT INTO announcements (title, content, badge_type, is_active) VALUES
('Extended Office Hours for November', 'The Barangay Hall will extend office hours until 7:00 PM on weekdays for the month of November.', 'Important', 1),
('Online Services Now Available', 'Residents can now apply for IDs and certifications online through this portal.', 'New', 1),
('Barangay Assembly Meeting', 'Join us for the quarterly Barangay Assembly on November 15, 2025 at 3:00 PM.', 'Event', 1);

-- Insert sample data for testing
INSERT INTO id_applications (reference_number, first_name, middle_name, last_name, birth_date, gender, civil_status, contact_number, email, complete_address, claim_method, price, status) VALUES
('BID-12345678', 'Juan', 'Santos', 'Dela Cruz', '1995-05-15', 'Male', 'Single', '09171234567', 'juan.delacruz@email.com', 'House No. 123, Street, Purok 1, Barangay Santo Niño', 'pickup', 100.00, 'Processing');

INSERT INTO certification_requests (reference_number, certificate_type, first_name, middle_name, last_name, complete_address, contact_number, email, purpose, price, status) VALUES
('CERT-87654321', 'residency', 'Maria', 'Garcia', 'Santos', 'House No. 456, Street, Purok 2, Barangay Santo Niño', '09187654321', 'maria.santos@email.com', 'For employment requirements', 50.00, 'Ready for Pickup');

-- Insert test user account (email: admin@test.com, password: admin123)
INSERT INTO users (email, password, first_name, last_name, contact_number, address, is_verified) VALUES
('admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', '09123456789', 'Test Address', 1);
