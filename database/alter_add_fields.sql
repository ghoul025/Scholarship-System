-- ALTER script to add fields and tables for enhanced features
-- BACKUP your database before running these statements.

-- 1) Add columns to scholars
ALTER TABLE scholars
  ADD COLUMN phone_number VARCHAR(30) DEFAULT NULL,
  ADD COLUMN batch VARCHAR(64) DEFAULT NULL,
  ADD COLUMN scholarship_type VARCHAR(64) DEFAULT NULL;

-- 2) Add role to users (if you have a users table for admin logins)
-- If your users table uses a different name adjust accordingly.
ALTER TABLE users
  ADD COLUMN role ENUM('super-admin','admin','staff') NOT NULL DEFAULT 'staff';

-- 3) Credentials table: stores each uploaded credential file and status
CREATE TABLE IF NOT EXISTS scholar_credentials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  scholar_id INT NOT NULL,
  type ENUM('COG','COR','ID') NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  status ENUM('pending','approved','rejected','reupload_requested') NOT NULL DEFAULT 'pending',
  expires_at DATE DEFAULT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reviewed_at TIMESTAMP NULL DEFAULT NULL,
  reviewer_id INT NULL,
  notes TEXT NULL,
  UNIQUE KEY uq_scholar_type (scholar_id, type),
  INDEX idx_status (status),
  FOREIGN KEY (scholar_id) REFERENCES scholars(id) ON DELETE CASCADE
);

-- 4) Audit logs for admin actions
CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(128) NOT NULL,
  details TEXT NULL,
  ip_address VARCHAR(45) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5) Notifications table (in-app notifications)
CREATE TABLE IF NOT EXISTS notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  scholar_id INT NULL,
  type VARCHAR(64) NOT NULL,
  message TEXT NOT NULL,
  meta JSON NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6) Indexes to speed up exports/filters
ALTER TABLE scholars
  ADD INDEX idx_batch (batch),
  ADD INDEX idx_scholarship_type (scholarship_type);

-- End of script
