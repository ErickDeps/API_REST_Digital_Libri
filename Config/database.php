<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

/**
 * Crea y retorna una instancia de conexión PDO utilizando las variables de entorno.
 *
 * Esta función autoejecutable obtiene los parámetros de conexión desde las
 * variables de entorno (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) y crea
 * una nueva instancia de PDO configurada con:
 *
 * - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION` para lanzar excepciones en errores.
 * - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC` para devolver resultados como arrays asociativos.
 *
 * Si la conexión falla, detiene la ejecución y muestra el mensaje de error.
 *
 * @return PDO La instancia de conexión PDO establecida.
 *
 * @throws PDOException Si ocurre un error al intentar conectar con la base de datos.
 */
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
