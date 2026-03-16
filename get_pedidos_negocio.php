<?php
// get_pedidos_negocio.php - Devuelve los pedidos de un negocio para el panel de cocina
header('Content-Type: application/json');
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rol'], ['socio', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$id_negocio = intval($_GET['id'] ?? $_SESSION['id_negocio'] ?? 0);

// Verificar que el socio sólo vea su propio negocio
if ($_SESSION['rol'] === 'socio' && $id_negocio !== intval($_SESSION['id_negocio'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

try {
    // Ultimas 6 horas para mantener el panel ágil
    $stmt = $pdo->prepare("
        SELECT id, comercio_id, cliente_zona, items_json, subtotal, descuento, envio, total, cupon, estado, created_at, metodo_entrega
        FROM pedidos
        WHERE comercio_id = ?
          AND created_at >= NOW() - INTERVAL 6 HOUR
        ORDER BY
            FIELD(estado, 'pendiente','aceptado','en_preparacion','en_camino','entregado','cancelado'),
            created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$id_negocio]);
    $pedidos = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $pedidos]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
