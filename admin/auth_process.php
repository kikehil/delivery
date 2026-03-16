<?php
// admin/auth_process.php - Secure login logic
session_start();
header('Content-Type: application/json');
require_once '../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

$user = $_POST['username'] ?? '';
$pass = $_POST['password'] ?? '';

if (empty($user) || empty($pass)) {
    echo json_encode(['status' => 'error', 'message' => 'Completa todos los campos']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, password, rol, id_negocio FROM usuarios WHERE username = ?");
    $stmt->execute([$user]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['rol'] = $u['rol'];
        $_SESSION['id_negocio'] = $u['id_negocio'];

        $redirect = ($u['rol'] == 'admin') ? 'dashboard.php' : '../partner/panel_socio.php';
        
        echo json_encode([
            'status' => 'success',
            'redirect' => $redirect
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario o contraseña incorrectos']);
    }

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor']);
}
