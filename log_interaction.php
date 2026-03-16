<?php
// log_interaction.php - Track non-sensitive user interactions (zone selection)
header('Content-Type: application/json');
require_once 'conexion.php';

$id_zona = isset($_GET['zona_id']) ? intval($_GET['zona_id']) : null;

if ($id_zona) {
    try {
        $sql = "INSERT INTO stats_zonas (id_zona, fecha, cantidad) 
                VALUES (?, CURDATE(), 1) 
                ON DUPLICATE KEY UPDATE cantidad = cantidad + 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_zona]);
        echo json_encode(['status' => 'ok']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error']);
    }
} else {
    echo json_encode(['status' => 'missing_id']);
}
?>
