-- Database: night_market_db
CREATE DATABASE IF NOT EXISTS night_market_db;
USE night_market_db;

-- Table: zonas
CREATE TABLE IF NOT EXISTS zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_colonia VARCHAR(255) NOT NULL,
    costo_envio DECIMAL(10, 2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: comercios
CREATE TABLE IF NOT EXISTS comercios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    logo_url VARCHAR(255),
    telefono VARCHAR(20) DEFAULT '525500000000', -- Added for WhatsApp integration
    plan_elite BOOLEAN DEFAULT FALSE,
    estado ENUM('pendiente', 'activo') DEFAULT 'activo',
    id_zona_base INT,
    FOREIGN KEY (id_zona_base) REFERENCES zonas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_comercio INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    descripcion TEXT,
    foto_url VARCHAR(255),
    FOREIGN KEY (id_comercio) REFERENCES comercios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Data
INSERT INTO zonas (nombre_colonia, costo_envio) VALUES ('Centro Pánuco', 20.00), ('Loma Linda', 30.00), ('Mora', 25.00);
INSERT INTO comercios (nombre, categoria, plan_elite, id_zona_base) VALUES 
('The Burger Lab', 'Hamburguesas', TRUE, 1),
('Sushi Zen', 'Japonesa', FALSE, 1),
('Tacos Don Juan', 'Mexicana', TRUE, 2),
('Pastelería Lili', 'Postres', FALSE, 3);
