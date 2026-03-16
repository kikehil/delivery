<?php
require_once 'conexion.php';
$cols = $pdo->query("DESCRIBE productos")->fetchAll();
echo json_encode($cols, JSON_PRETTY_PRINT);
?>
