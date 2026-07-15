-- Create the database (if it doesn't already exist)
CREATE DATABASE IF NOT EXISTS scholarship_system;

-- Use the database
USE scholarship_system;

-- Users Table (kept as in the original system for compatibility)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'scholar') NOT NULL
);

-- Scholars Table (kept compatible with existing code)
CREATE TABLE IF NOT EXISTS scholars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    first_name VARCHAR(60) NOT NULL,
    middle_name VARCHAR(60) DEFAULT NULL,
    last_name VARCHAR(60) NOT NULL,
    status ENUM('enrolled','not_enrolled','graduated') DEFAULT 'not_enrolled',
    email VARCHAR(100) NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Documents Table (original fields kept; added optional non-breaking columns)
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scholar_id INT NOT NULL,
    document_type ENUM('ID', 'Certification of Grades', 'Certificate of Registration') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) DEFAULT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scholar_id) REFERENCES scholars(id)
);

-- Requirements: document types and deadlines
CREATE TABLE IF NOT EXISTS requirements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_type VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    deadline DATE DEFAULT NULL,
    scholarship_type VARCHAR(50) DEFAULT NULL,
    is_required TINYINT(1) DEFAULT 1,
    UNIQUE KEY uq_req_doc_scholarship (document_type, scholarship_type)
);

-- Notifications table for users
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Exported credentials / saved exports
CREATE TABLE IF NOT EXISTS credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scholar_id INT NOT NULL,
    credential_type VARCHAR(150) DEFAULT NULL,
    file_path VARCHAR(255) NOT NULL,
    exported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    permanent TINYINT(1) DEFAULT 0,
    FOREIGN KEY (scholar_id) REFERENCES scholars(id) ON DELETE CASCADE
);

-- School years table: e.g. 2024-2025, with optional start/end dates and flag for current
CREATE TABLE IF NOT EXISTS school_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(50) NOT NULL, -- e.g. "2024-2025"
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    is_current TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Scholar enrollments per school year (single row per scholar+school_year)
CREATE TABLE IF NOT EXISTS scholar_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scholar_id INT NOT NULL,
    school_year_id INT NOT NULL,
    enrolled_1st TINYINT(1) DEFAULT 0,
    enrolled_2nd TINYINT(1) DEFAULT 0,
    notes TEXT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (scholar_id) REFERENCES scholars(id) ON DELETE CASCADE,
    FOREIGN KEY (school_year_id) REFERENCES school_years(id) ON DELETE CASCADE,
    UNIQUE KEY uq_scholar_year (scholar_id, school_year_id)
);

-- Simple action log for auditing
CREATE TABLE IF NOT EXISTS action_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- End of schema