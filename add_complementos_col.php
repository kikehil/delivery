<?php
require_once 'conexion.php';
try {
    $pdo->exec("ALTER TABLE productos ADD COLUMN complementos TEXT AFTER descripcion");
    echo "Column 'complementos' added successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
