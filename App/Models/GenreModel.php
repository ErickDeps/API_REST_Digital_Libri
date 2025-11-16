<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Class GenreModel
 * 
 * Modelo encargado de interactuar con la tabla `genres`.
 */
class GenreModel
{
    /**
     * Conexión PDO a la base de datos.
     *
     * @var PDO
     */
    private $connection;

    /**
     * Constructor: inicializa la conexión a la base de datos.
     */
    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    /**
     * Obtiene todos los géneros registrados.
     *
     * @return array Lista de géneros.
     */
    public function getAll(): array
    {
        $stmt = $this->connection->query("SELECT * FROM genres");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un género por su ID.
     *
     * @param int $id ID del género.
     * @return array|null Género encontrado o null si no existe.
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM genres WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crea un nuevo género.
     *
     * @param string $genre Nombre del género.
     * @return int|false ID del nuevo género o false si ocurre un error.
     */
    public function create(string $genre)
    {
        try {
            $stmt = $this->connection->prepare("INSERT INTO genres (genre) VALUES (?)");
            $stmt->execute([$genre]);
            return (int) $this->connection->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error en GenreModel::create: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }

    /**
     * Actualiza un género existente.
     *
     * @param int $id ID del género.
     * @param string $genre Nuevo nombre del género.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     */
    public function update(int $id, string $genre): bool
    {
        try {
            $stmt = $this->connection->prepare("UPDATE genres SET genre = ?, created_at = created_at WHERE id = ?");
            return $stmt->execute([$genre, $id]);
        } catch (\PDOException $e) {
            error_log("Error en GenreModel::update: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }

    /**
     * Elimina un género.
     *
     * @param int $id ID del género.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM genres WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Error en GenreModel::delete: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }
}
