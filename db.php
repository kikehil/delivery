<?php
// db.php - Database connection using PDO
// Unificado con el .env de Laravel

// Cargamos el Autoload de Composer del backend
require_once __DIR__ . '/backend/vendor/autoload.php';

use Dotenv\Dotenv;

// El archivo .env vive en la carpeta /backend
$dotenv = Dotenv::createImmutable(__DIR__ . '/backend');
$dotenv->load();

// Configuramos la conexión usando las variables de Laravel
$host = $_ENV['DB_HOST'];
$db   = $_ENV['DB_DATABASE'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}
?>
