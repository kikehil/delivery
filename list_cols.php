<?php
require_once 'conexion.php';
$stmt = $pdo->query("SHOW COLUMNS FROM negocios");
$columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo implode(", ", $columns);
?>
