<?php
require_once 'conexion.php';
$cols = $pdo->query("DESCRIBE productos")->fetchAll();
foreach($cols as $c) {
    echo $c['Field'] . ", ";
}
?>
