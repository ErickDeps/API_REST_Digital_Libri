<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Modelo para manejar la tabla books_genres.
 */
class BookGenreModel
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    /**
     * Obtiene todos los géneros asignados a un libro.
     *
     * @param int $bookId
     * @return array
     */
    public function getGenresByBookId(int $bookId): array
    {
        $stmt = $this->connection->prepare(
            "SELECT g.id, g.genre 
             FROM books_genres bg
             JOIN genres g ON bg.genre_id = g.id
             WHERE bg.book_id = ?"
        );
        $stmt->execute([$bookId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Asigna varios géneros a un libro.
     *
     * @param int $bookId ID del libro
     * @param array $genreIds ID de los generos a asignar
     * @return bool
     */
    public function addGenresToBook(int $bookId, array $genreIds): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "INSERT IGNORE INTO books_genres (book_id, genre_id) VALUES (?, ?)"
            );

            foreach ($genreIds as $genreId) {
                $stmt->execute([$bookId, $genreId]);
            }

            return true;
        } catch (\PDOException $e) {
            error_log("Error en BookGenreModel::addGenresToBook: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }

    /**
     * Elimina un género de un libro.
     *
     * @param int $bookId ID del libro 
     * @param int $genreId ID del genero
     * @return bool
     */
    public function removeGenreFromBook(int $bookId, int $genreId): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "DELETE FROM books_genres WHERE book_id = ? AND genre_id = ?"
            );
            $stmt->execute([$bookId, $genreId]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error en BookGenreModel::removeGenreFromBook: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }
}
