-- Dashboard and Tracking System for YaLoPido
USE night_market_db;

-- 1. Table to track order volume per business per day
CREATE TABLE IF NOT EXISTS stats_pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_negocio INT NOT NULL,
    fecha DATE NOT NULL,
    cantidad INT DEFAULT 1,
    UNIQUE KEY biz_date (id_negocio, fecha),
    FOREIGN KEY (id_negocio) REFERENCES negocios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table to track zone activity (Unique user interactions per zone)
CREATE TABLE IF NOT EXISTS stats_zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_zona INT NOT NULL,
    fecha DATE NOT NULL,
    cantidad INT DEFAULT 1,
    UNIQUE KEY zone_date (id_zona, fecha),
    FOREIGN KEY (id_zona) REFERENCES zonas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Ensure some initial stats data for the charts demo
INSERT IGNORE INTO stats_pedidos (id_negocio, fecha, cantidad) VALUES 
(1, CURDATE() - INTERVAL 2 DAY, 5),
(1, CURDATE() - INTERVAL 1 DAY, 8),
(1, CURDATE(), 12),
(2, CURDATE() - INTERVAL 1 DAY, 3),
(2, CURDATE(), 7);

INSERT IGNORE INTO stats_zonas (id_zona, fecha, cantidad) VALUES 
(1, CURDATE(), 50),
(2, CURDATE(), 30),
(3, CURDATE(), 15),
(4, CURDATE(), 10);
