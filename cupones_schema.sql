-- Table: cupones
CREATE TABLE IF NOT EXISTS cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    tipo ENUM('fijo', 'porcentaje') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    limite_uso INT DEFAULT 100,
    usos_actuales INT DEFAULT 0,
    estado ENUM('activo', 'expirado') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Coupons for Pánuco
INSERT IGNORE INTO cupones (codigo, tipo, valor, limite_uso, estado) VALUES 
('HOLAPANUCO', 'fijo', 30.00, 500, 'activo'),
('URBIX10', 'porcentaje', 10.00, 1000, 'activo'),
('DOMINGOGRATIS', 'fijo', 25.00, 200, 'activo');
