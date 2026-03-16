<?php
require_once 'conexion.php';
$stmt = $pdo->query("DESCRIBE negocios");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
