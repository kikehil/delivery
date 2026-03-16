<?php
require_once 'conexion.php';
try {
    $sql = "ALTER TABLE negocios 
            ADD COLUMN horario_atencion VARCHAR(255) AFTER direccion,
            ADD COLUMN latitud DECIMAL(10, 8) AFTER horario_atencion,
            ADD COLUMN longitud DECIMAL(11, 8) AFTER latitud,
            ADD COLUMN entrega_domicilio TINYINT(1) DEFAULT 1 AFTER longitud,
            ADD COLUMN recolecta_pedidos TINYINT(1) DEFAULT 1 AFTER entrega_domicilio,
            ADD COLUMN consumo_sucursal TINYINT(1) DEFAULT 0 AFTER recolecta_pedidos";
    $pdo->exec($sql);
    echo "Columns added successfully";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
