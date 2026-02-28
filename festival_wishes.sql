CREATE DATABASE IF NOT EXISTS festival_wishes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE festival_wishes;

CREATE TABLE IF NOT EXISTS wishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    festival_name VARCHAR(100) NOT NULL,
    user_name VARCHAR(150) NOT NULL,
    custom_message TEXT,
    image_path VARCHAR(255) DEFAULT NULL,
    ai_image_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_wishes_created_at (created_at),
    INDEX idx_wishes_festival (festival_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migration for existing installations:
-- ALTER TABLE wishes ADD COLUMN image_path VARCHAR(255) DEFAULT NULL AFTER custom_message;
-- ALTER TABLE wishes ADD COLUMN ai_image_path VARCHAR(255) DEFAULT NULL AFTER image_path;
-- CREATE INDEX idx_wishes_created_at ON wishes(created_at);
-- CREATE INDEX idx_wishes_festival ON wishes(festival_name);

-- Generate hash in PHP using password_hash('StrongPasswordHere', PASSWORD_DEFAULT)
-- Then insert the generated hash below.
INSERT INTO admin_users (username, password)
VALUES ('admin', '$2y$10$REPLACE_WITH_PASSWORD_HASH')
ON DUPLICATE KEY UPDATE username = username;
