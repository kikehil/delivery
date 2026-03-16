<?php
// save_negocio.php - URBIX VERSION 3.0 (DEBUG MODE)
header('Content-Type: application/json');
header('X-Urbix-Debug: Enabled');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$owner_name = $_POST['owner_name'] ?? 'N/A';
$biz_name = $_POST['biz_name'] ?? 'N/A';
$whatsapp_orders = $_POST['whatsapp_orders'] ?? 'N/A';

try {
    // Attempting ONLY insertion. 
    // If "Duplicate column" occurs here, it's a server/DB ghost.
    
    $sql = "INSERT INTO negocios (
                nombre, 
                categoria, 
                id_zona_base, 
                nombre_responsable, 
                email, 
                telefono_contacto, 
                whatsapp_pedidos, 
                direccion, 
                estado, 
                plan
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', 'esencial'
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $biz_name,
        $_POST['biz_category'] ?? '',
        $_POST['base_zone'] ?? null,
        $owner_name,
        $_POST['owner_email'] ?? '',
        $_POST['owner_phone'] ?? '',
        $whatsapp_orders,
        $_POST['biz_address'] ?? ''
    ]);

    echo json_encode([
        'status' => 'success',
        'message' => '¡URBIX V3.0! Registro guardado correctamente',
        'debug_info' => 'Insert successful'
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error', 
        'message' => 'FALLO URBIX V3.0: ' . $e->getMessage(),
        'hint' => 'Si ves "Duplicate Column", limpia la tabla negocios en phpMyAdmin'
    ]);
}
?>
