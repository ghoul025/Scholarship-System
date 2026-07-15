-- Migration: Create exported_credentials table for permanent credential export
CREATE TABLE IF NOT EXISTS exported_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scholar_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_plain VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scholar_id) REFERENCES scholars(id) ON DELETE CASCADE
);

-- Add missing columns to scholars table if not present
ALTER TABLE scholars 
    ADD COLUMN phone VARCHAR(20) NULL,
    ADD COLUMN sex VARCHAR(10) NULL,
    ADD COLUMN units INT NULL,
    ADD COLUMN tuition_fee DECIMAL(10,2) NULL,
    DROP COLUMN email;

-- Remove NOT NULL from full_name if needed
ALTER TABLE scholars MODIFY full_name VARCHAR(100) NULL;

-- Migration: split existing full_name into first_name, middle_name, last_name
SET @has_first = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='scholars' AND COLUMN_NAME='first_name');
IF @has_first = 0 THEN
    ALTER TABLE scholars ADD COLUMN first_name VARCHAR(60) NULL, ADD COLUMN middle_name VARCHAR(60) NULL, ADD COLUMN last_name VARCHAR(60) NULL;
    -- Split full_name by spaces: naive split (first, middle, last)
    UPDATE scholars SET
        first_name = TRIM(SUBSTRING_INDEX(full_name, ' ', 1)),
        last_name = TRIM(SUBSTRING_INDEX(full_name, ' ', -1)),
        middle_name = NULL;
    -- For names with three or more parts, set middle_name to the middle portion
    UPDATE scholars SET middle_name = TRIM(SUBSTRING(full_name, LENGTH(first_name) + 2, LENGTH(full_name) - LENGTH(first_name) - LENGTH(last_name) - 1))
        WHERE full_name LIKE '% % %';
    -- Set reasonable defaults for NOT NULL columns
    ALTER TABLE scholars MODIFY first_name VARCHAR(60) NOT NULL, MODIFY last_name VARCHAR(60) NOT NULL;
END IF;
