<?php
require '../conexion.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS promociones (
        id INT AUTO_INCREMENT PRIMARY KEY,
        imagen_url VARCHAR(255) NOT NULL,
        etiqueta VARCHAR(50) DEFAULT '',
        titulo VARCHAR(100) NOT NULL,
        subtitulo TEXT,
        orden INT DEFAULT 0,
        activa TINYINT(1) DEFAULT 1,
        creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Insert demo data if empty
    $count = $pdo->query("SELECT COUNT(*) FROM promociones")->fetchColumn();
    if ($count == 0) {
        $pdo->exec("INSERT INTO promociones (imagen_url, etiqueta, titulo, subtitulo, orden, activa) VALUES 
        ('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=800&q=80', 'Promoción', '50% de descuento', 'En tu primer pedido usando el código: YALOPIDO50', 1, 1),
        ('https://images.unsplash.com/photo-1513104890138-7c749659a591?w=800&q=80', 'Limitado', 'Envío Gratis', 'Todo el fin de semana en restaurantes seleccionados', 2, 1),
        ('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=800&q=80', 'Novedad', 'Nuevos Sabores', 'Descubre los nuevos negocios que se unieron hoy', 3, 1)");
    }
    
    echo "Tabla 'promociones' creada correctamente.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
