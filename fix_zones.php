<?php
require_once 'conexion.php';

try {
    $pdo->beginTransaction();

    echo "--- Iniciando Curación de Zonas ---\n";

    // 1. Identificar zonas duplicadas y mapearlas al ID más bajo de cada nombre
    $stmt = $pdo->query("SELECT MIN(id) as master_id, nombre_colonia FROM zonas GROUP BY nombre_colonia");
    $master_zones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($master_zones as $mz) {
        $master_id = $mz['master_id'];
        $name = $mz['nombre_colonia'];

        echo "Procesando zona: $name (Master ID: $master_id)\n";

        // Obtener todos los IDs duplicados para este nombre
        $stmt_ids = $pdo->prepare("SELECT id FROM zonas WHERE nombre_colonia = ? AND id != ?");
        $stmt_ids->execute([$name, $master_id]);
        $duplicate_ids = $stmt_ids->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($duplicate_ids)) {
            $ids_str = implode(',', $duplicate_ids);
            echo "  IDs duplicados encontrados: $ids_str. Migrando a $master_id...\n";

            // Actualizar negocios que usan IDs duplicados
            $stmt_upd_biz = $pdo->prepare("UPDATE negocios SET id_zona_base = ? WHERE id_zona_base IN ($ids_str)");
            $stmt_upd_biz->execute([$master_id]);
            echo "  Negocios actualizados: " . $stmt_upd_biz->rowCount() . "\n";

            // Actualizar stats_zonas
            $stmt_upd_stats = $pdo->prepare("UPDATE stats_zonas SET id_zona = ? WHERE id_zona IN ($ids_str)");
            $stmt_upd_stats->execute([$master_id]);
            echo "  Estadísticas actualizadas: " . $stmt_upd_stats->rowCount() . "\n";

            // Eliminar zonas duplicadas
            $stmt_del = $pdo->prepare("DELETE FROM zonas WHERE id IN ($ids_str)");
            $stmt_del->execute();
            echo "  Zonas duplicadas eliminadas.\n";
        } else {
            echo "  Sin duplicados.\n";
        }
    }

    $pdo->commit();
    echo "\n--- ¡Curación completada con éxito! ---\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
