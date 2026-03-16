-- Database: bocao_db
CREATE DATABASE IF NOT EXISTS bocao_db;
USE bocao_db;

-- Table: zonas
CREATE TABLE IF NOT EXISTS zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_colonia VARCHAR(255) NOT NULL,
    costo_envio DECIMAL(10, 2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: negocios
CREATE TABLE IF NOT EXISTS negocios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    categoria VARCHAR(100),
    logo_url VARCHAR(255),
    plan ENUM('esencial', 'pro', 'elite') DEFAULT 'esencial',
    estado ENUM('pendiente', 'activo') DEFAULT 'pendiente',
    id_zona_base INT,
    FOREIGN KEY (id_zona_base) REFERENCES zonas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_negocio INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL(10, 2) NOT NULL,
    descripcion TEXT,
    foto_url VARCHAR(255),
    FOREIGN KEY (id_negocio) REFERENCES negocios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion of some initial data for testing
INSERT INTO zonas (nombre_colonia, costo_envio) VALUES ('Centro Pánuco', 25.00), ('Loma Linda', 35.50);
INSERT INTO negocios (nombre, categoria, plan, estado, id_zona_base) VALUES 
('Gorditas de la Esquina', 'Comida Mexicana', 'elite', 'activo', 1),
('Tacos El Chino', 'Cena', 'pro', 'activo', 1),
('Pizza Nostra', 'Pizza', 'esencial', 'activo', 2);
