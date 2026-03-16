<?php
// db_stats_fix.php - Ensure statistics tables exist for the dashboard
require_once 'conexion.php';

try {
    // 1. Table for Order Stats (Daily)
    $pdo->exec("CREATE TABLE IF NOT EXISTS stats_pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fecha DATE NOT NULL UNIQUE,
        cantidad INT DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 2. Table for Zone Interactions (Reach)
    $pdo->exec("CREATE TABLE IF NOT EXISTS stats_zonas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_zona INT NOT NULL,
        cantidad INT DEFAULT 0,
        fecha DATE NOT NULL,
        UNIQUE KEY zone_date (id_zona, fecha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // 3. Seed some dummy data if empty for the dashboard to look "alive"
    $count = $pdo->query("SELECT COUNT(*) FROM stats_pedidos")->fetchColumn();
    if ($count == 0) {
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $qty = rand(10, 50);
            $pdo->exec("INSERT INTO stats_pedidos (fecha, cantidad) VALUES ('$date', $qty)");
        }
    }

    echo "Tables ensured successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
