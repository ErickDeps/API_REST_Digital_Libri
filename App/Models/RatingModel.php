<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Class RatingModel
 * 
 * Modelo responsable de interactuar con la tabla `ratings`.
 *
 * @package App\Models
 */
class RatingModel
{
    /**
     * Conexión PDO a la base de datos.
     *
     * @var PDO
     */
    private $connection;

    /**
     * RatingModel constructor.
     * Inicializa la conexión a la base de datos.
     */
    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    /**
     * Obtiene todos los ratings de un libro.
     *
     * @param int $bookId ID del libro.
     * @return array Lista de ratings.
     */
    public function getByBookId(int $bookId): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM ratings WHERE book_id = ?");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Agrega o actualiza el rating de un usuario para un libro.
     *
     * @param int $userId ID del usuario.
     * @param int $bookId ID del libro.
     * @param float $rating Valor del rating (1 a 5).
     * @return bool
     */
    public function addOrUpdate(int $userId, int $bookId, float $rating): bool
    {
        try {

            $stmt = $this->connection->prepare("SELECT id FROM ratings WHERE user_id = ? AND book_id = ?");
            $stmt->execute([$userId, $bookId]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $stmt = $this->connection->prepare("UPDATE ratings SET rating = ? WHERE id = ?");
                return $stmt->execute([$rating, $existing['id']]);
            } else {
                $stmt = $this->connection->prepare("INSERT INTO ratings (user_id, book_id, rating) VALUES (?, ?, ?)");
                return $stmt->execute([$userId, $bookId, $rating]);
            }
        } catch (\PDOException $e) {
            error_log("Error en RatingModel::addOrUpdate: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }
}
