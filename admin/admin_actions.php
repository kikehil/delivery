<?php
// admin/admin_actions.php - Centralized actions for Admin Panel
session_start();
header('Content-Type: application/json');
require_once '../conexion.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    if ($action === 'toggle_partner_status') {
        $id = intval($_POST['id']);
        $current_status = $_POST['current_status'];
        $new_status = ($current_status === 'activo') ? 'pendiente' : 'activo';

        $stmt = $pdo->prepare("UPDATE negocios SET estado = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);

        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
    }
    
    elseif ($action === 'delete_partner') {
        $id = intval($_POST['id']);
        $stmt = $pdo->prepare("DELETE FROM negocios WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
    }

    elseif ($action === 'update_category') {
        $id = intval($_POST['id']);
        $category = $_POST['category'];
        $stmt = $pdo->prepare("UPDATE negocios SET categoria = ? WHERE id = ?");
        $stmt->execute([$category, $id]);
        echo json_encode(['status' => 'success']);
    }

    elseif ($action === 'add_partner') {
        $biz_name = trim($_POST['biz_name']);
        $owner_name = trim($_POST['owner_name']);
        $whatsapp = trim($_POST['whatsapp']);
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $zone_id = intval($_POST['zone_id']);
        $category = $_POST['category'] ?? 'Otros';

        $pdo->beginTransaction();

        // 1. Crear Negocio
        $sql_biz = "INSERT INTO negocios (nombre, nombre_responsable, whatsapp_pedidos, id_zona_base, categoria, estado, plan) 
                    VALUES (?, ?, ?, ?, ?, 'activo', 'esencial')";
        $stmt_biz = $pdo->prepare($sql_biz);
        $stmt_biz->execute([$biz_name, $owner_name, $whatsapp, $zone_id, $category]);
        $id_negocio = $pdo->lastInsertId();

        // 2. Crear Usuario Socio
        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->fetch()) {
            throw new Exception("El nombre de usuario (email) ya está registrado en el sistema.");
        }

        $sql_user = "INSERT INTO usuarios (username, password, rol, id_negocio) VALUES (?, ?, 'socio', ?)";
        $stmt_user = $pdo->prepare($sql_user);
        $stmt_user->execute([$username, $password, $id_negocio]);

        $pdo->commit();
        echo json_encode(['status' => 'success', 'id_negocio' => $id_negocio]);
    }

    elseif ($action === 'assign_credentials') {
        $id_negocio = intval($_POST['id_negocio']);
        $username = trim($_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt_check = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->fetch()) {
            throw new Exception("El nombre de usuario ya está registrado.");
        }

        $sql = "INSERT INTO usuarios (username, password, rol, id_negocio) VALUES (?, ?, 'socio', ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username, $password, $id_negocio]);

        echo json_encode(['status' => 'success']);
    }

    elseif ($action === 'change_password') {
        $id_negocio = intval($_POST['id_negocio']);
        $new_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id_negocio = ? AND rol = 'socio'");
        $stmt->execute([$new_pass, $id_negocio]);

        echo json_encode(['status' => 'success']);
    }

    // --- Promociones Actions ---
    elseif ($action === 'add_promo') {
        $imagen_url = trim($_POST['imagen_url']);
        $etiqueta = trim($_POST['etiqueta']);
        $titulo = trim($_POST['titulo']);
        $subtitulo = trim($_POST['subtitulo']);
        $orden = intval($_POST['orden']);

        $stmt = $pdo->prepare("INSERT INTO promociones (imagen_url, etiqueta, titulo, subtitulo, orden, activa) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$imagen_url, $etiqueta, $titulo, $subtitulo, $orden]);

        echo json_encode(['status' => 'success']);
    }
    elseif ($action === 'toggle_promo') {
        $id = intval($_POST['id']);
        $current = intval($_POST['current_status']);
        $new_status = $current ? 0 : 1;

        $stmt = $pdo->prepare("UPDATE promociones SET activa = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);

        echo json_encode(['status' => 'success']);
    }
    elseif ($action === 'delete_promo') {
        $id = intval($_POST['id']);
        
        $stmt = $pdo->prepare("DELETE FROM promociones WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
