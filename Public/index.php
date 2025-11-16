<?php
// CABECERAS CORS PARA PERMITIR PETICIONES DESDE OTRO DOMINIO
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

use Dotenv\Dotenv;

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$routes = require_once __DIR__ . '/../Config/routes.php';
$router = new \App\Core\Router($routes);

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
