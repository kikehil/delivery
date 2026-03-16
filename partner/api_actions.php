<?php
// partner/api_actions.php - Background actions for Partner Panel
session_start();
header('Content-Type: application/json');
require_once '../conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'socio') {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$id_negocio = $_SESSION['id_negocio'];
$action = $_POST['action'] ?? '';

if ($action === 'toggle_negocio') {
    $nuevo_estado = ($_POST['abierto'] === 'true') ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE negocios SET modulo_abierto = ? WHERE id = ?");
    $stmt->execute([$nuevo_estado, $id_negocio]);
    echo json_encode(['status' => 'success']);
}

elseif ($action === 'toggle_producto') {
    $id_prod = intval($_POST['id_producto']);
    $disponible = ($_POST['disponible'] === 'true') ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE productos SET disponible = ? WHERE id = ? AND id_negocio = ?");
    $stmt->execute([$disponible, $id_prod, $id_negocio]);
    echo json_encode(['status' => 'success']);
}

elseif ($action === 'update_profile') {
    $nombre = $_POST['nombre'];
    $wa = $_POST['whatsapp'];
    $stmt = $pdo->prepare("UPDATE negocios SET nombre = ?, whatsapp_pedidos = ? WHERE id = ?");
    $stmt->execute([$nombre, $wa, $id_negocio]);
    echo json_encode(['status' => 'success']);
}
?>
