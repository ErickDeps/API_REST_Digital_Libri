<?php

namespace App\Core;

use PDO;

class Database
{
    private static $instance = null;


    /**
     * Obtiene una instancia única de la conexión PDO a la base de datos.
     *
     * Utiliza el patrón Singleton para asegurar que solo exista una conexión activa
     * durante todo el ciclo de ejecución. Si la conexión aún no ha sido creada,
     * carga la configuración desde el archivo `database.php` y almacena la instancia
     * en la propiedad estática `$instance`.
     *
     * @return PDO La instancia de conexión PDO activa.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $connection = require __DIR__ . '/../../config/database.php';
            self::$instance = $connection;
        }
        return self::$instance;
    }
}
