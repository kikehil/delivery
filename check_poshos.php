<?php
require_once 'conexion.php';

echo "--- POSHOS DETAILS ---\n";
$stmt = $pdo->prepare("SELECT * FROM negocios WHERE nombre = 'poshos'");
$stmt->execute();
$poshos = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($poshos);

echo "\n--- ZONAS TABLE ---\n";
$stmt = $pdo->query("SELECT * FROM zonas");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
