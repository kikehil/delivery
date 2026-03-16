<?php
// get_zonas.php - API to fetch delivery zones
header('Content-Type: application/json');
require_once 'db.php';

try {
    $sql = "SELECT id, nombre_colonia, costo_envio FROM zonas ORDER BY nombre_colonia ASC";
    $stmt = $pdo->query($sql);
    $zonas = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $zonas
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
