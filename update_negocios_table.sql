-- Add missing columns to 'negocios' table for the new registration flow
ALTER TABLE negocios 
ADD COLUMN dueño_nombre VARCHAR(255) AFTER id_zona_base,
ADD COLUMN email VARCHAR(255) AFTER dueño_nombre,
ADD COLUMN telefono_contacto VARCHAR(20) AFTER email,
ADD COLUMN whatsapp_pedidos VARCHAR(20) AFTER telefono_contacto,
ADD COLUMN direccion TEXT AFTER whatsapp_pedidos;

-- Ensure the 'estado' field is correct (already exists in bocao_db.sql but just in case)
ALTER TABLE negocios MODIFY COLUMN estado ENUM('pendiente', 'activo') DEFAULT 'pendiente';
