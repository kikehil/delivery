-- Refined Setup for YaLoPido (Bocao Style)
USE night_market_db;

-- 1. Specific Admin Table for Prompt #13
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'socio') NOT NULL DEFAULT 'socio',
    id_negocio INT DEFAULT NULL,
    FOREIGN KEY (id_negocio) REFERENCES negocios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Add 'status' control to negocios
ALTER TABLE negocios ADD COLUMN IF NOT EXISTS modulo_abierto TINYINT(1) DEFAULT 1;

-- 3. Add 'disponibilidad' to productos
ALTER TABLE productos ADD COLUMN IF NOT EXISTS disponible TINYINT(1) DEFAULT 1;

-- 4. Sample Admin (Pass: admin123)
-- Hash: $2y$10$8v8mFzN2m.zH.mY3K.M.eO8mFzN2m.zH.mY3K.M.eO8mFzN2m.zH (Just for reference, use password_hash in PHP)
