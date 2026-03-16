<?php
// save_final.php - URBIX DEFINITIVE VERSION WITH n8n INTEGRATION
header('Content-Type: application/json');
require_once 'conexion.php';

// --- CONFIGURACIÓN n8n ---
$n8n_webhook_url = 'https://n8n-n8n.amv1ou.easypanel.host/webhook/pideloya'; // Reemplaza con tu URL de n8n
// -------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Error: Debes usar POST']);
    exit;
}

$biz_name  = $_POST['biz_name'] ?? 'Negocio';
$resp      = $_POST['owner_name'] ?? 'Responsable';
$email     = $_POST['owner_email'] ?? '';
$phone     = $_POST['owner_phone'] ?? '';
$orders_wa = $_POST['whatsapp_orders'] ?? '';
$address   = $_POST['biz_address'] ?? '';
$category  = $_POST['biz_category'] ?? '';
$zone_id   = $_POST['base_zone'] ?? null;
$pass      = $_POST['password'] ?? '';
$entrega   = isset($_POST['entrega']) ? 1 : 0;
$reco      = isset($_POST['recolecta']) ? 1 : 0;
$sucursal  = isset($_POST['sucursal']) ? 1 : 0;

try {
    $pdo->beginTransaction();

    // 1. Guardar en Base de Datos local
    $sql = "INSERT INTO negocios (
                nombre, categoria, id_zona_base, nombre_responsable, 
                email, telefono_contacto, whatsapp_pedidos, direccion, 
                estado, plan, entrega_domicilio, recolecta_pedidos, consumo_sucursal
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', 'esencial', ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([ 
        $biz_name, $category, $zone_id, $resp, $email, $phone, $orders_wa, $address,
        $entrega, $reco, $sucursal
    ]);
    
    $negocio_id = $pdo->lastInsertId();

    // 2. Crear cuenta de usuario para el socio
    if (!empty($email) && !empty($pass)) {
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt_check->execute([$email]);
        if ($stmt_check->fetch()) {
            throw new Exception("El correo ingresado ya está registrado como usuario.");
        }

        $hashed_pass = password_hash($pass, PASSWORD_DEFAULT);
        $stmtUser = $pdo->prepare("INSERT INTO usuarios (username, password, rol, id_negocio) VALUES (?, ?, 'socio', ?)");
        $stmtUser->execute([$email, $hashed_pass, $negocio_id]);
    }

    $pdo->commit();

    // 3. Enviar a n8n
    if (!empty($n8n_webhook_url) && $n8n_webhook_url !== 'TU_URL_DE_N8N_AQUI') {
        $payload = [
            'tipo_evento'      => 'nuevo_registro_yalopido',
            'negocio'          => $biz_name,
            'responsable'      => $resp,
            'email'            => $email,
            'entrega'          => $entrega,
            'recolecta'        => $reco,
            'consumo_sucursal' => $sucursal,
            'timestamp'        => date('Y-m-d H:i:s')
        ];
        
        // ... curl logic (skipping for brevity but it's here)
        $ch = curl_init($n8n_webhook_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    echo json_encode([
        'status' => 'success',
        'message' => '¡URBIX FINAL! Registro guardado y usuario creado.'
    ]);

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DETALLE ERROR: ' . $e->getMessage()]);
}
?>
