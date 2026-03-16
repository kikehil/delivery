<?php
// get_feed.php - Fetch businesses for Night Market
header('Content-Type: application/json');
require_once 'conexion.php';

$zoneId = isset($_GET['zona']) ? intval($_GET['zona']) : 0;
$categoria = isset($_GET['categoria']) ? trim($_GET['categoria']) : '';

try {
    $sql = "SELECT n.id, n.nombre, n.categoria, n.logo_url as img, n.telefono_contacto as telefono, n.plan, 
                   z.nombre_colonia as zona_nombre, n.modulo_abierto,
                   n.entrega_domicilio, n.recolecta_pedidos, n.consumo_sucursal
            FROM negocios n 
            LEFT JOIN zonas z ON n.id_zona_base = z.id 
            WHERE n.estado = 'activo'";
            
    if ($zoneId > 0) {
        $sql .= " AND n.id_zona_base = $zoneId";
    }
    
    if ($categoria !== '') {
        $sql .= " AND n.categoria = " . $pdo->quote($categoria);
    }

    $sql .= " ORDER BY n.modulo_abierto DESC, n.plan DESC, n.nombre ASC";
    
    $stmt = $pdo->query($sql);
    $feed = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $feed
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Query failed']);
}
?>
