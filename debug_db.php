<?php
require_once 'conexion.php';

function dump_table($pdo, $sql, $label) {
    echo "--- $label ---\n";
    $stmt = $pdo->query($sql);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($data as $row) {
        echo json_encode($row) . "\n";
    }
    echo "\n";
}

dump_table($pdo, "SELECT id, nombre_colonia FROM zonas", "ZONAS");
dump_table($pdo, "SELECT id, nombre, estado, modulo_abierto, id_zona_base, categoria FROM negocios", "NEGOCIOS");
dump_table($pdo, "SELECT id, nombre, id_negocio, disponible FROM productos", "PRODUCTOS ALL");
?>
