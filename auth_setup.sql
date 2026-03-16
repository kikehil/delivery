-- Security and Partner System for YaLoPido
USE night_market_db;

-- 1. Unified Users Table
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'socio') NOT NULL,
    id_negocio INT DEFAULT NULL, -- Linked to 'negocios' if rol is 'socio'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_negocio) REFERENCES negocios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create Default Admin (User: admin / Pass: yalopido2026)
-- Note: In production, password should be hashed with password_hash()
INSERT IGNORE INTO usuarios (username, password, rol) 
VALUES ('admin', '$2y$10$Wov.3Ew5V7k5sL0z8yN5uOqFpX8eR.vCgBvS8vG6U5QvWz8XqXqXa', 'admin');
-- The hash above is for 'yalopido2026'

-- 3. Update 'negocios' table to allow logo management if not present
-- (Already should exist from previous steps)
