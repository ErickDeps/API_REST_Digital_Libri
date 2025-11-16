<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Class UserModel
 * 
 * Modelo responsable de interactuar con la tabla `users`.
 *
 * @package App\Models
 */
class UserModel
{
    /**
     * Conexión PDO a la base de datos.
     *
     * @var PDO
     */
    private $connection;

    /**
     * UserModel constructor.
     * Inicializa la conexión a la base de datos.
     */
    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    /**
     * Obtiene un usuario por su id.
     *
     * @param int $user_id ID del usuario a buscar.
     * @return array|null Devuelve los datos del usuario o null si no existe.
     */
    public function getById(int $user_id): ?array
    {
        $stmt = $this->connection->prepare("SELECT name, email, role FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Obtiene un usuario por su email.
     *
     * @param string $email Email del usuario a buscar.
     * @return array|null Devuelve los datos del usuario o null si no existe.
     */
    public function getByEmail(string $email): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }


    /**
     * Crea un nuevo usuario en la base de datos.
     *
     * @param array{name: string, email: string, password: string} $data Datos del usuario a crear.
     * @return int|false Devuelve el ID del usuario creado o false si hubo un error.
     */
    public function createUser(array $data)
    {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
            );

            $stmt->execute([
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role']
            ]);

            return (int) $this->connection->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error en createUser: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }
}
