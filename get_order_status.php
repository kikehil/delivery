<?php
// get_order_status.php - Polling endpoint para tracking del cliente
header('Content-Type: application/json');
require_once 'conexion.php';

$order_id = intval($_GET['id'] ?? 0);

if (!$order_id) {
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, estado, repartidor_nombre, repartidor_telefono, updated_at FROM pedidos WHERE id = ?");
    $stmt->execute([$order_id]);
    $pedido = $stmt->fetch();

    if (!$pedido) {
        echo json_encode(['status' => 'error', 'message' => 'Pedido no encontrado']);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'estado' => $pedido['estado'],
        'repartidor_nombre'   => $pedido['repartidor_nombre'],
        'repartidor_telefono' => $pedido['repartidor_telefono'],
        'updated_at' => $pedido['updated_at']
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error de conexión.']);
}
?>
