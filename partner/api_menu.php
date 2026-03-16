<?php
// partner/api_menu.php - Backend Maestro para Gestión de Inventario
session_start();
header('Content-Type: application/json');
require_once '../conexion.php';

// Verificación de Seguridad
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'socio') {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no autorizada']);
    exit;
}

$id_negocio = $_SESSION['id_negocio'];
$action = $_POST['action'] ?? '';

try {
    // 1. Añadir Producto con Subida de Imagen
    if ($action === 'add_product') {
        $nombre = trim($_POST['nombre']);
        $precio = floatval($_POST['precio']);
        $descripcion = trim($_POST['descripcion'] ?? '');
        
        $comps = [];
        if (isset($_POST['complementos']) && is_array($_POST['complementos'])) {
            foreach ($_POST['complementos'] as $i => $name) {
                if (!empty(trim($name))) {
                    $comps[] = [
                        'nombre' => trim($name),
                        'precio' => floatval($_POST['comp_precios'][$i] ?? 0)
                    ];
                }
            }
        }
        $complementos = !empty($comps) ? json_encode($comps, JSON_UNESCAPED_UNICODE) : null;
        $img_path = 'img/placeholder.png'; // Fallback

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $filename = 'prod_' . uniqid() . '.' . $ext;
                $target = '../img/productos/' . $filename;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                    $img_path = 'img/productos/' . $filename;
                }
            }
        }

        $sql = "INSERT INTO productos (id_negocio, nombre, precio, descripcion, complementos, foto_url, disponible) 
                VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_negocio, $nombre, $precio, $descripcion, $complementos, $img_path]);
        
        echo json_encode(['status' => 'success', 'message' => 'Producto añadido']);
    }

    // 2. Alternar Disponibilidad (Pausar/Reanudar)
    elseif ($action === 'toggle_availability') {
        $id_prod = intval($_POST['id']);
        $current = intval($_POST['current']);
        $new_val = ($current === 1) ? 0 : 1;

        $stmt = $pdo->prepare("UPDATE productos SET disponible = ? WHERE id = ? AND id_negocio = ?");
        $stmt->execute([$new_val, $id_prod, $id_negocio]);
        echo json_encode(['status' => 'success', 'new_val' => $new_val]);
    }

    // 3. Eliminar Producto
    elseif ($action === 'delete_product') {
        $id_prod = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ? AND id_negocio = ?");
        $stmt->execute([$id_prod, $id_negocio]);
        echo json_encode(['status' => 'success']);
    }

    // 4. Alternar Estado del Negocio (Abierto/Cerrado)
    elseif ($action === 'toggle_business_status') {
        $status = intval($_POST['status']); // 1 para abierto, 0 para cerrado
        $stmt = $pdo->prepare("UPDATE negocios SET modulo_abierto = ? WHERE id = ?");
        $stmt->execute([$status, $id_negocio]);
        echo json_encode(['status' => 'success']);
    }

    // 5. Actualizar Datos del Negocio
    elseif ($action === 'update_business_data') {
        $horario = trim($_POST['horario_atencion']);
        $direccion = trim($_POST['direccion']);
        $telefono = trim($_POST['telefono_contacto']);
        $whatsapp = trim($_POST['whatsapp_pedidos']);
        $domicilio = isset($_POST['entrega_domicilio']) ? 1 : 0;
        $recolecta = isset($_POST['recolecta_pedidos']) ? 1 : 0;
        $consumo = isset($_POST['consumo_sucursal']) ? 1 : 0;
        $lat = $_POST['latitud'] ?: null;
        $lng = $_POST['longitud'] ?: null;

        // Manejo del Logo
        $update_logo = "";
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $filename = 'logo_' . $id_negocio . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['logo']['tmp_name'], '../img/' . $filename)) {
                    $update_logo = ", logo_url = 'img/$filename'";
                }
            }
        }

        $sql = "UPDATE negocios SET 
                    horario_atencion = ?, 
                    direccion = ?, 
                    telefono_contacto = ?, 
                    whatsapp_pedidos = ?, 
                    entrega_domicilio = ?, 
                    recolecta_pedidos = ?, 
                    consumo_sucursal = ?,
                    latitud = ?,
                    longitud = ?
                    $update_logo
                WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $horario, $direccion, $telefono, $whatsapp, 
            $domicilio, $recolecta, $consumo, 
            $lat, $lng, $id_negocio
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Datos actualizados correctamente']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
