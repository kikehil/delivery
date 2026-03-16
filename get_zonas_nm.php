<?php
// get_zonas_nm.php - Fetch zones for Night Market
header('Content-Type: application/json');
require_once 'conexion.php';

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
    echo json_encode(['status' => 'error', 'message' => 'Query failed']);
}
?>
