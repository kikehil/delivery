<?php
// validar_cupon.php - Validate promo codes for Urbix
header('Content-Type: application/json');
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$codigo = isset($_POST['codigo']) ? trim(strtoupper($_POST['codigo'])) : '';

if (empty($codigo)) {
    echo json_encode(['status' => 'error', 'message' => 'Ingresa un código']);
    exit;
}

try {
    // 1. Check if table exists, if not create it (Auto-fix)
    $table_check = $pdo->query("SHOW TABLES LIKE 'cupones'");
    if (!$table_check->fetch()) {
        $pdo->exec("CREATE TABLE cupones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(50) NOT NULL UNIQUE,
            tipo ENUM('fijo', 'porcentaje') NOT NULL,
            valor DECIMAL(10, 2) NOT NULL,
            limite_uso INT DEFAULT 100,
            usos_actuales INT DEFAULT 0,
            estado ENUM('activo', 'expirado') DEFAULT 'activo'
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        
        // Add initial data
        $pdo->exec("INSERT INTO cupones (codigo, tipo, valor, limite_uso) VALUES ('HOLAPANUCO', 'fijo', 30.00, 500)");
    }

    // 2. Query coupon
    $stmt = $pdo->prepare("SELECT * FROM cupones WHERE codigo = ? AND estado = 'activo'");
    $stmt->execute([$codigo]);
    $cupon = $stmt->fetch();

    if ($cupon) {
        if ($cupon['usos_actuales'] >= $cupon['limite_uso']) {
            echo json_encode(['status' => 'error', 'message' => 'Este cupón ha agotado su límite de uso']);
        } else {
            echo json_encode([
                'status' => 'success',
                'codigo' => $cupon['codigo'],
                'tipo' => $cupon['tipo'],
                'valor' => (float)$cupon['valor']
            ]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Este código no es válido o ya expiró']);
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error de base de datos']);
}
?>
