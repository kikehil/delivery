<?php
// get_negocios.php - API to fetch businesses
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Select all active businesses, ordered by Plan Elite first
    // Note: Since 'elite' is an ENUM value, we can use a custom order logic
    // or just rely on the fact that PRO and ELITE should come first.
    // A robust way to order: FIELD(plan, 'elite', 'pro', 'esencial')
    $sql = "SELECT n.*, z.nombre_colonia as zona_nombre 
            FROM negocios n 
            LEFT JOIN zonas z ON n.id_zona_base = z.id 
            WHERE n.estado = 'activo'
            ORDER BY FIELD(n.plan, 'elite', 'pro', 'esencial'), n.nombre ASC";
    
    $stmt = $pdo->query($sql);
    $negocios = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $negocios
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
