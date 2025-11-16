<?php

namespace App\Models;

use App\Core\Database;

class FavoriteModel
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection(); // tu conexión PDO
    }

    /**
     * Agregar o quitar un libro de favoritos
     */
    public function toggleFavorite(int $userId, int $bookId): bool
    {
        $stmt = $this->connection->prepare("SELECT id FROM user_favorites WHERE user_id = :user_id AND book_id = :book_id");
        $stmt->execute(['user_id' => $userId, 'book_id' => $bookId]);
        $favorite = $stmt->fetch();

        if ($favorite) {
            $stmt = $this->connection->prepare("DELETE FROM user_favorites WHERE id = :id");
            return $stmt->execute(['id' => $favorite['id']]);
        } else {
            $stmt = $this->connection->prepare("INSERT INTO user_favorites (user_id, book_id) VALUES (:user_id, :book_id)");
            return $stmt->execute(['user_id' => $userId, 'book_id' => $bookId]);
        }
    }

    /**
     * Obtener los libros favoritos de un usuario 
     */
    public function getFavoritesByUser(int $userId): array
    {
        $stmt = $this->connection->prepare("
            SELECT f.book_id AS isFavorited, b.*
            FROM books b
            JOIN user_favorites f ON f.book_id = b.id
            WHERE f.user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
