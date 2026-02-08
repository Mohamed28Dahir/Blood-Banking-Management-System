-- Blood Donation Management System (BDMS) Database Schema
-- Database: bdms
-- Created: 2025-12-29

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS bdms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bdms;

-- ============================================
-- Table: users
-- Stores all user accounts (admin and regular users)
-- ============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user' NOT NULL,
    status ENUM('pending', 'approved', 'blocked') DEFAULT 'pending' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: donors
-- Stores donor applications and information
-- ============================================
CREATE TABLE IF NOT EXISTS donors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    age INT NOT NULL,
    weight DECIMAL(5,2) NOT NULL COMMENT 'Weight in kg',
    last_donation_date DATE NULL,
    medical_conditions TEXT NULL,
    medical_proof VARCHAR(255) NOT NULL COMMENT 'File path to medical document',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' NOT NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_blood_group (blood_group),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: blood_requests
-- Stores blood request submissions
-- ============================================
CREATE TABLE IF NOT EXISTS blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    patient_name VARCHAR(100) NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units_needed INT NOT NULL,
    hospital_name VARCHAR(200) NOT NULL,
    hospital_address TEXT NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    urgency ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium' NOT NULL,
    required_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'fulfilled') DEFAULT 'pending' NOT NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_blood_group (blood_group),
    INDEX idx_status (status),
    INDEX idx_urgency (urgency)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: blood_stock
-- Tracks available blood units by blood group
-- ============================================
CREATE TABLE IF NOT EXISTS blood_stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL UNIQUE,
    units_available INT DEFAULT 0 NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_blood_group (blood_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Table: notifications
-- Stores user notifications
-- ============================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    type ENUM('approval', 'rejection', 'info') DEFAULT 'info' NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Initial Data
-- ============================================

-- Insert default admin account
-- Password: Admin@123 (hashed with bcrypt)
INSERT INTO users (full_name, email, phone, address, password, role, status) VALUES
('System Administrator', 'admin@bdms.com', '+252-61-1234567', 'BDMS Headquarters, Mogadishu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'approved');

-- Initialize blood stock with all blood groups (starting with 0 units)
INSERT INTO blood_stock (blood_group, units_available) VALUES
('A+', 0),
('A-', 0),
('B+', 0),
('B-', 0),
('AB+', 0),
('AB-', 0),
('O+', 0),
('O-', 0);

-- ============================================
-- Sample Data for Testing (Optional)
-- ============================================

-- Sample users (all approved for testing)
INSERT INTO users (full_name, email, phone, address, password, role, status) VALUES
('Ahmed Mohamed Hassan', 'ahmed.hassan@email.com', '+252-61-2345678', 'Hodan District, Mogadishu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'approved'),
('Fatima Abdi Ali', 'fatima.ali@email.com', '+252-61-3456789', 'Hamar Weyne, Mogadishu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'approved'),
('Omar Ibrahim Yusuf', 'omar.yusuf@email.com', '+252-61-4567890', 'Hargeisa, Somaliland', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'approved'),
('Halima Ahmed Mohamud', 'halima.mohamud@email.com', '+252-61-5678901', 'Kismayo, Lower Jubba', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'pending');

-- Sample approved donors with stock updates
INSERT INTO donors (user_id, blood_group, age, weight, last_donation_date, medical_conditions, medical_proof, status, admin_notes) VALUES
(2, 'O+', 28, 68.5, '2024-10-15', NULL, 'uploads/medical_proofs/sample_proof_1.pdf', 'approved', 'All medical requirements met. Approved for donation.'),
(3, 'A+', 35, 75.0, '2024-11-20', NULL, 'uploads/medical_proofs/sample_proof_2.pdf', 'approved', 'Healthy donor. Approved.');

-- Update blood stock based on approved donors
UPDATE blood_stock SET units_available = 5 WHERE blood_group = 'O+';
UPDATE blood_stock SET units_available = 3 WHERE blood_group = 'A+';
UPDATE blood_stock SET units_available = 2 WHERE blood_group = 'B+';
UPDATE blood_stock SET units_available = 4 WHERE blood_group = 'AB+';

-- Sample blood requests
INSERT INTO blood_requests (user_id, patient_name, blood_group, units_needed, hospital_name, hospital_address, contact_number, urgency, required_date, reason, status, admin_notes) VALUES
(2, 'Mohamed Ali Farah', 'O+', 2, 'Medina Hospital', 'Medina District, Mogadishu', '+252-61-7890123', 'high', '2025-01-05', 'Emergency surgery required due to accident', 'approved', 'Blood available. Request approved.'),
(3, 'Amina Hassan Omar', 'A-', 1, 'Benadir Hospital', 'Hodan, Mogadishu', '+252-61-8901234', 'critical', '2025-01-02', 'Maternal emergency - urgent need', 'pending', NULL);

-- Sample notifications
INSERT INTO notifications (user_id, message, type, is_read) VALUES
(2, 'Your donor application has been approved. Thank you for your contribution!', 'approval', TRUE),
(2, 'Your blood request for Patient: Mohamed Ali Farah has been approved.', 'approval', FALSE),
(3, 'Your donor application has been approved. You can now donate blood.', 'approval', TRUE),
(4, 'Your account is pending admin approval. You will be notified once approved.', 'info', FALSE);

-- ============================================
-- End of Database Schema
-- ============================================
