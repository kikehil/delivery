<?php
// search.php - Intelligent search for Night Market
header('Content-Type: application/json');
require_once 'conexion.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$zoneId = isset($_GET['zoneId']) ? intval($_GET['zoneId']) : 0;

if (empty($query) || $zoneId === 0) {
    echo json_encode(['status' => 'success', 'data' => []]);
    exit;
}

try {
    // Search for negocios (by name or category) AND products (by name)
    // Filtered by selected zone
    $searchTerm = "%$query%";
    
    $sql = "SELECT DISTINCT n.id, n.nombre, n.categoria, n.logo_url, n.plan, 'merchant' as type, n.modulo_abierto
            FROM negocios n
            LEFT JOIN productos p ON n.id = p.id_negocio
            WHERE n.id_zona_base = ? 
            AND n.estado = 'activo'
            AND (n.nombre LIKE ? OR n.categoria LIKE ? OR p.nombre LIKE ?)
            ORDER BY n.modulo_abierto DESC, n.plan DESC
            LIMIT 10";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$zoneId, $searchTerm, $searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'data' => $results
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Search failed']);
}
?>
