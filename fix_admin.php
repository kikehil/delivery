<?php
require_once 'conexion.php';

try {
    // 1. Asegurar que la tabla existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        rol ENUM('admin', 'socio') NOT NULL,
        id_negocio INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 2. Crear o actualizar el usuario admin
    $username = 'admin';
    $password = 'yalopido2026';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user) {
        // Actualizar contraseña por si acaso
        $update = $pdo->prepare("UPDATE usuarios SET password = ?, rol = 'admin' WHERE username = ?");
        $update->execute([$hashed_password, $username]);
        echo "Usuario admin actualizado correctamente.\n";
    } else {
        // Insertar nuevo
        $insert = $pdo->prepare("INSERT INTO usuarios (username, password, rol) VALUES (?, ?, 'admin')");
        $insert->execute([$username, $hashed_password]);
        echo "Usuario admin creado correctamente.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
