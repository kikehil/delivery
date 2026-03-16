<?php
// get_productos.php - Fetch products for a specific merchant
header('Content-Type: application/json');
require_once 'conexion.php';

$id_comercio = isset($_GET['id_comercio']) ? intval($_GET['id_comercio']) : 0;

if ($id_comercio === 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID de comercio no válido']);
    exit;
}

try {
    $sql = "SELECT id, nombre, precio, descripcion, complementos, foto_url FROM productos WHERE id_negocio = ? AND disponible = 1 ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_comercio]);
    $productos = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $productos
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en la consulta']);
}
?>
