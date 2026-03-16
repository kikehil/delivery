<?php
// update_order_status.php - El negocio actualiza el estado del pedido
header('Content-Type: application/json');
session_start();
require_once 'conexion.php';

// Solo socios autenticados pueden cambiar el estado
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rol'], ['socio', 'admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
$nuevo_estado = $_POST['estado'] ?? '';
$id_negocio = $_SESSION['id_negocio'] ?? null;

$estados_validos = ['pendiente', 'aceptado', 'en_preparacion', 'en_camino', 'entregado', 'cancelado'];

if (!$order_id || !in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

try {
    // Si es socio, verificar que el pedido pertenece a su negocio
    if ($_SESSION['rol'] === 'socio') {
        $stmt = $pdo->prepare("SELECT id FROM pedidos WHERE id = ? AND comercio_id = ?");
        $stmt->execute([$order_id, $id_negocio]);
        if (!$stmt->fetch()) {
            echo json_encode(['status' => 'error', 'message' => 'Pedido no encontrado']);
            exit;
        }
    }

    // Filtrado de XSS
    $repartidor_nombre   = isset($_POST['repartidor_nombre'])   ? htmlspecialchars(trim(substr($_POST['repartidor_nombre'], 0, 100))) : null;
    $repartidor_telefono = isset($_POST['repartidor_telefono']) ? htmlspecialchars(trim(substr($_POST['repartidor_telefono'], 0, 50))) : null;

    if ($nuevo_estado === 'en_camino' && $repartidor_nombre) {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ?, repartidor_nombre = ?, repartidor_telefono = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $repartidor_nombre, $repartidor_telefono, $order_id]);
    } else {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $order_id]);
    }

    echo json_encode(['status' => 'success', 'estado' => $nuevo_estado]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
