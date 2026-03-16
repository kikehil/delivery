<?php
require 'conexion.php';
$stmt = $pdo->query("DESCRIBE productos");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("DESCRIBE cupones");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("DESCRIBE zonas");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
