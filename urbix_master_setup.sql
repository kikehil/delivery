-- URBIX FULL DATABASE SETUP
-- Target Database: night_market_db

CREATE DATABASE IF NOT EXISTS night_market_db;
USE night_market_db;

-- 1. Table: zonas
CREATE TABLE IF NOT EXISTS zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_colonia VARCHAR(255) NOT NULL,
    costo_envio DECIMAL(10, 2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table: negocios
CREATE TABLE IF NOT EXISTS negocios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    logo_url VARCHAR(255),
    plan ENUM('esencial', 'pro', 'elite') DEFAULT 'esencial',
    estado ENUM('pendiente', 'activo') DEFAULT 'activo',
    id_zona_base INT,
    nombre_responsable VARCHAR(255),
    email VARCHAR(255),
    telefono_contacto VARCHAR(20) DEFAULT '525500000000',
    whatsapp_pedidos VARCHAR(20),
    direccion TEXT,
    FOREIGN KEY (id_zona_base) REFERENCES zonas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table: productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_negocio INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    descripcion TEXT,
    foto_url VARCHAR(255),
    FOREIGN KEY (id_negocio) REFERENCES negocios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table: cupones
CREATE TABLE IF NOT EXISTS cupones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    tipo ENUM('fijo', 'porcentaje') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    limite_uso INT DEFAULT 100,
    usos_actuales INT DEFAULT 0,
    estado ENUM('activo', 'expirado') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- INITIAL DATA FOR PANUCO
INSERT IGNORE INTO zonas (nombre_colonia, costo_envio) VALUES 
('Centro Pánuco', 20.00), 
('Loma Linda', 30.00), 
('Colonia Moralillo', 35.00),
('Mora', 25.00);

INSERT IGNORE INTO negocios (nombre, categoria, plan, estado, id_zona_base, telefono_contacto) VALUES 
('The Burger Lab', 'Hamburguesas', 'elite', 'activo', 1, '521234567890'),
('Sushi Zen', 'Japonesa', 'pro', 'activo', 1, '521234567891'),
('Tacos Don Juan', 'Mexicana', 'elite', 'activo', 2, '521234567892'),
('Pastelería Lili', 'Postres', 'esencial', 'activo', 4, '521234567893');

INSERT IGNORE INTO cupones (codigo, tipo, valor, limite_uso) VALUES 
('URBIX50', 'fijo', 50.00, 100),
('HOLAPANUCO', 'fijo', 30.00, 500),
('DOMINGOGRATIS', 'fijo', 25.00, 200);
