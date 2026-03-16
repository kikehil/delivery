<?php
// enviar_pedido.php - Guarda el pedido en BD y lo envía a n8n (Version Segura)
header('Content-Type: application/json');
require_once 'conexion.php';

$n8n_webhook_url = 'https://n8n-n8n.amv1ou.easypanel.host/webhook/pideloya';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['comercio_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

try {
    $comercio_id = intval($input['comercio_id']);
    
    // --- SEGURIDAD 1: Validar negocio existe y está abierto ---
    $stmt_biz = $pdo->prepare("SELECT nombre, whatsapp_pedidos, telefono_contacto, modulo_abierto, estado FROM negocios WHERE id = ?");
    $stmt_biz->execute([$comercio_id]);
    $biz = $stmt_biz->fetch();
    
    if (!$biz) {
        throw new Exception("El negocio seleccionado no existe.");
    }
    if ($biz['estado'] !== 'activo' || (int)$biz['modulo_abierto'] === 0) {
        throw new Exception("⚠️ El negocio se encuentra cerrado actualmente. Tu pedido no puede ser procesado.");
    }

    // --- SEGURIDAD 2: Calcular Subtotal Real (Ignorar precios del usuario) ---
    $subtotal_real = 0.0;
    $items = $input['items'] ?? [];
    $processed_items = [];

    foreach ($items as $item) {
        if (!isset($item['id'])) continue; // Ignorar items maliciosos sin ID
        
        $item_id = intval($item['id']);
        $qty = intval($item['qty'] ?? 1);
        if ($qty <= 0) $qty = 1;
        
        $stmt_prod = $pdo->prepare("SELECT nombre, precio, complementos FROM productos WHERE id = ? AND id_negocio = ? AND disponible = 1");
        $stmt_prod->execute([$item_id, $comercio_id]);
        $prod = $stmt_prod->fetch();
        
        if ($prod) {
            $base_price = floatval($prod['precio']);
            $comps_price = 0.0;
            $processed_comps = [];
            
            // Validar precios de los complementos directamente desde BD
            if (isset($item['complementos']) && is_array($item['complementos'])) {
                $db_comps = json_decode($prod['complementos'] ?: '[]', true);
                if (is_array($db_comps)) {
                    foreach ($item['complementos'] as $c_req) {
                        foreach ($db_comps as $c_db) {
                            if (isset($c_db['nombre']) && isset($c_req['nombre']) && $c_db['nombre'] === $c_req['nombre']) {
                                $c_price = floatval($c_db['precio'] ?? 0);
                                $comps_price += $c_price;
                                $processed_comps[] = [
                                    'nombre' => $c_db['nombre'],
                                    'precio' => $c_price
                                ];
                                break;
                            }
                        }
                    }
                }
            }
            
            $item_total = ($base_price + $comps_price) * $qty;
            $subtotal_real += $item_total;
            
            // Construir el item con información 100% validada desde BD
            $processed_items[] = [
                'id' => $item_id,
                'nombre' => $prod['nombre'],
                'qty' => $qty,
                'precio' => $base_price,
                'complementos' => $processed_comps,
                'instrucciones' => htmlspecialchars(substr($item['instrucciones'] ?? '', 0, 255)) // XSS basic protection
            ];
        } else {
             throw new Exception("⚠️ Un producto de tu carrito ya no está disponible.");
        }
    }

    if (empty($processed_items)) {
        throw new Exception("El carrito está vacío o contiene datos corruptos.");
    }

    // --- SEGURIDAD 3: Validar Envío Real basado en la Zona de la BD ---
    $envio_real = 0.0;
    $cliente_zona = $input['cliente_zona'] ?? 'Desconocido';
    $metodo_entrega = $input['metodo_entrega'] ?? 'delivery';

    if ($metodo_entrega === 'delivery') {
        $stmt_zona = $pdo->prepare("SELECT costo_envio FROM zonas WHERE nombre_colonia = ? LIMIT 1");
        $stmt_zona->execute([$cliente_zona]);
        $zona = $stmt_zona->fetch();
        if ($zona) {
            $envio_real = floatval($zona['costo_envio']);
        }
    }

    // --- SEGURIDAD 4: Validar Estado y Descuento Matemático del Cupón ---
    $descuento_real = 0.0;
    $cupon_codigo = $input['cupon'] ?? null;
    
    if ($cupon_codigo) {
        $stmt_cupon = $pdo->prepare("SELECT tipo, valor FROM cupones WHERE codigo = ? AND estado = 'activo' AND usos_actuales < limite_uso LIMIT 1");
        $stmt_cupon->execute([$cupon_codigo]);
        $cupon = $stmt_cupon->fetch();
        
        if ($cupon) {
            if ($cupon['tipo'] === 'fijo') {
                $descuento_real = floatval($cupon['valor']);
            } else {
                $descuento_real = $subtotal_real * (floatval($cupon['valor']) / 100);
            }
            
            // Registrar uso de cupón (previene abusos si varios dan clic a la vez - control básico concurrente)
            $pdo->prepare("UPDATE cupones SET usos_actuales = usos_actuales + 1 WHERE codigo = ?")->execute([$cupon_codigo]);
        } else {
            $cupon_codigo = null; // Ignóralo si intentaron enviar un cupón alterado
        }
    }

    $total_real = max(0, $subtotal_real + $envio_real - $descuento_real);

    // 1. Guardar pedido VALIDADO
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (comercio_id, cliente_zona, items_json, subtotal, descuento, envio, total, cupon, estado, metodo_entrega)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente', ?)
    ");
    $stmt->execute([
        $comercio_id,
        $cliente_zona,
        json_encode($processed_items),
        $subtotal_real,
        $descuento_real,
        $envio_real,
        $total_real,
        $cupon_codigo,
        $metodo_entrega
    ]);
    
    $order_id = $pdo->lastInsertId();

    // Actualizar el $input con los datos SEGUROS Y RECALCULADOS para mandar a n8n
    // De lo contrario n8n mandaría por WhatsApp "Pizzas de 0 pesos"
    $input_seguro = [
        'comercio_id' => $comercio_id,
        'negocio_nombre' => $biz['nombre'],
        'cliente_zona' => $cliente_zona,
        'order_id' => $order_id,
        'subtotal' => $subtotal_real,
        'descuento' => $descuento_real,
        'envio' => $envio_real,
        'total' => $total_real,
        'cupon' => $cupon_codigo,
        'metodo_entrega' => $metodo_entrega,
        'whatsapp_pedidos' => $biz['whatsapp_pedidos'] ?: $biz['telefono_contacto'],
        'telefono_contacto' => $biz['telefono_contacto'],
        'items' => $processed_items
    ];
    
    // 2. Log stats
    try {
        $sql_stats = "INSERT INTO stats_pedidos (id_negocio, fecha, cantidad) VALUES (?, CURDATE(), 1) ON DUPLICATE KEY UPDATE cantidad = cantidad + 1";
        $pdo->prepare($sql_stats)->execute([$comercio_id]);
    } catch (Exception $statsErr) { /* ignorar */ }

    // 3. Enviar a n8n
    if (!empty($n8n_webhook_url)) {
        $ch = curl_init($n8n_webhook_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input_seguro));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

    echo json_encode([
        'status'   => 'success',
        'message'  => 'Pedido procesado de forma segura',
        'order_id' => $order_id
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
