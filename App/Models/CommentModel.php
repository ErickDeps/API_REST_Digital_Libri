<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Class CommentModel
 * 
 * Modelo responsable de interactuar con la tabla `comments`.
 *
 * @package App\Models
 */
class CommentModel
{
    /**
     * Conexión PDO a la base de datos.
     *
     * @var PDO
     */
    private $connection;

    /**
     * CommentModel constructor.
     * Inicializa la conexión a la base de datos.
     */
    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    /**
     * Obtiene todos los comentarios de un libro.
     *
     * @param int $bookId ID del libro.
     * @return array Lista de comentarios.
     */
    public function getByBookId(int $bookId): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM comments WHERE book_id = ? ORDER BY created_at DESC");
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un comentario por su ID.
     *
     * @param int $id ID del comentario.
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getByUser($userId)
    {
        $stmt = $this->connection->prepare(
            "SELECT b.title AS book_title, c.* 
            FROM comments c 
            JOIN books b ON c.book_id = b.id
            WHERE c.user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crea un nuevo comentario.
     *
     * @param array $data Datos del comentario: user_id, book_id, comment.
     * @return int|false ID del comentario creado o false en caso de error.
     */
    public function create(array $data)
    {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO comments (user_id, book_id, comment, created_at) VALUES (?, ?, ?, NOW())"
            );
            $stmt->execute([
                $data['user_id'],
                $data['book_id'],
                $data['comment']
            ]);
            return (int)$this->connection->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error en CommentModel::create: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }

    /**
     * Elimina un comentario.
     *
     * @param int $id ID del comentario.
     * @return bool
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM comments WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Error en CommentModel::delete: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }
}
