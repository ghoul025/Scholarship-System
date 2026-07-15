-- Table to store exported usernames and passwords permanently
CREATE TABLE IF NOT EXISTS exported_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_plain VARCHAR(100) NOT NULL
);
