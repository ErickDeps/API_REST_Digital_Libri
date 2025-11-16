<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

return (function () {
    $host = $_ENV['DB_HOST'];
    $dbname = $_ENV['DB_NAME'];
    $user = $_ENV['DB_USER'];
    $pass = $_ENV['DB_PASS'];

    try {
        $connection = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $connection;
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
})();
