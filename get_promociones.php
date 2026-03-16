<?php
// get_promociones.php - Fetch active promotions for index
header('Content-Type: application/json');
require_once 'conexion.php';

try {
    $stmt = $pdo->query("SELECT id, imagen_url, etiqueta, titulo, subtitulo FROM promociones WHERE activa = 1 ORDER BY orden ASC");
    $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $promos
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
