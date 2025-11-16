<?php

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Class BookModel
 * 
 * Modelo responsable de interactuar con la tabla `books`.
 *
 * @package App\Models
 */
class BookModel
{
    /**
     * Conexión PDO a la base de datos.
     *
     * @var PDO
     */
    private $connection;

    /**
     * BookModel constructor.
     * Inicializa la conexión a la base de datos.
     */
    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    /**
     * Obtiene todos los libros.
     *
     * @return array Lista de libros.
     */
    public function getAll(): array
    {
        $stmt = $this->connection->query("SELECT * FROM books ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un libro por su ID.
     *
     * @param int $id ID del libro.
     * @return array|null Devuelve los datos del libro o null si no existe.
     */
    public function getBookById(int $id): ?array
    {
        $stmt = $this->connection->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtiene todos los datos del libro, incluyendo genero, rating y comentarios.
     * @param int $id ID del libro.
     * @return array|null Devuelve los datos del libro o null si no existe.
     */
    public function getBookDetails(int $id)
    {
        $stmt = $this->connection->prepare(
            "SELECT
        b.id,
        b.title,
        b.author,
        b.year,
        b.synopsis,
        b.number_of_pages,
        b.image,
        -- Géneros como array JSON (manual)
        CONCAT('[', GROUP_CONCAT(DISTINCT CONCAT('\"', g.genre, '\"')), ']') AS genres,
        -- Comentarios como JSON
        CONCAT('[', GROUP_CONCAT(DISTINCT CONCAT('{\"user\":\"', u1.name, '\",\"comment\":\"', c.comment, '\"}')), ']') AS comments,
        -- Ratings como JSON
        CONCAT('[', GROUP_CONCAT(DISTINCT CONCAT('{\"user\":\"', u2.name, '\",\"rating\":', r.rating, '}')), ']') AS ratings
        FROM books b
        LEFT JOIN books_genres bg ON bg.book_id = b.id
        LEFT JOIN genres g ON g.id = bg.genre_id
        LEFT JOIN comments c ON c.book_id = b.id
        LEFT JOIN users u1 ON c.user_id = u1.id
        LEFT JOIN ratings r ON r.book_id = b.id
        LEFT JOIN users u2 ON r.user_id = u2.id
        WHERE b.id = ?
        GROUP BY b.id"
        );
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);

        // Decodificar manualmente los campos JSON
        $book['genres'] = json_decode($book['genres'], true);
        $book['comments'] = json_decode($book['comments'], true);
        $book['ratings'] = json_decode($book['ratings'], true);

        return $book ?: null;
    }

    /**
     * Obtiene todos los libros de un usuario.
     *
     * @param int $userId ID del usuario.
     * @return array Lista de libros.
     */
    public function getByUserId(int $userId): array
    {
        $stmt = $this->connection->prepare("SELECT * FROM books WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una lista de libros filtrada y paginada.
     *
     * Permite aplicar filtros opcionales sobre título, autor, año y géneros,
     * y devuelve los resultados paginados según el límite y el offset.
     *
     * @param int $limit Número máximo de libros a devolver.
     * @param int $offset Número de registros a saltar para la paginación.
     * @param string|null $search Término de búsqueda para comparar con título o autor (opcional).
     * @param int|null $yearFrom Año mínimo de publicación para filtrar (opcional).
     * @param int|null $yearTo Año máximo de publicación para filtrar (opcional).
     * @param array|null $genres Arreglo de IDs de géneros para filtrar (opcional).
     * @param int $userId ID del usuario autenticado (opcional)
     * 
     * @return array Arreglo de libros que cumplen con los filtros y la paginación.
     *
     * @example
     * Obtener primeros 10 libros publicados desde 2000 con género 3
     * $books = $bookModel->getFilteredPaginated(10, 0, null, 2000, null, [3]);
     */
    public function getFilteredPaginated(int $limit, int $offset, ?string $search = null, ?int $yearFrom = null, ?int $yearTo = null, ?array $genres = null, ?int $userId = null): array
    {
        // Base SELECT
        $select = "SELECT DISTINCT b.*";
        // Si tenemos userId, añadimos campo isFavorited
        if ($userId) {
            $select .= ", uf.book_id AS isFavorited";
        }

        // FROM y joins (books_genres y genres siempre se incluyen para poder filtrar por géneros)
        $query = $select . " FROM books b
        LEFT JOIN books_genres bg ON bg.book_id = b.id
        LEFT JOIN genres g ON g.id = bg.genre_id";

        // Si userId, añadimos LEFT JOIN para favoritos (condición en el JOIN)
        if ($userId) {
            $query .= " LEFT JOIN user_favorites uf ON uf.book_id = b.id AND uf.user_id = :userId";
        }

        $query .= " WHERE 1=1";

        $params = [];

        if ($search) {
            $query .= " AND (b.title LIKE :search OR b.author LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($yearFrom) {
            $query .= " AND b.year >= :yearFrom";
            $params[':yearFrom'] = $yearFrom;
        }

        if ($yearTo) {
            $query .= " AND b.year <= :yearTo";
            $params[':yearTo'] = $yearTo;
        }

        if ($genres && count($genres) > 0) {
            $genrePlaceholders = [];
            foreach ($genres as $index => $genreId) {
                $ph = ":genre$index";
                $genrePlaceholders[] = $ph;
                $params[$ph] = $genreId;
            }
            $query .= " AND g.id IN (" . implode(',', $genrePlaceholders) . ")";
        }

        // Evitar duplicados si hay múltiples géneros por libro
        $query .= " GROUP BY b.id";

        // Orden y paginación
        $query .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->connection->prepare($query);

        // Bind dinámico de parámetros
        foreach ($params as $placeholder => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($placeholder, $value, $type);
        }

        // Si userId existe, lo bindear como INT
        if ($userId) {
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Cuenta el número total de libros que cumplen con los filtros aplicados.
     *
     * Permite aplicar filtros opcionales sobre título, autor, año y géneros,
     * y devuelve solo la cantidad total de registros que cumplen esos criterios.
     *
     * @param string|null $search para comparar con título o autor (opcional).
     * @param int|null $yearFrom año mínimo de publicación para filtrar (opcional).
     * @param int|null $yearTo año máximo de publicación para filtrar (opcional).
     * @param array|null $genres arreglo de IDs de géneros para filtrar (opcional).
     *
     * @return int Número total de libros que cumplen con los filtros.
     *
     * @example
     * // Contar libros publicados entre 1990 y 2000 con los géneros 2 y 5
     * $total = $bookModel->countFiltered(null, 1990, 2000, [2,5]);
     */
    public function countFiltered(?string $search = null, ?int $yearFrom = null, ?int $yearTo = null, ?array $genres = null): int
    {
        $query = "SELECT COUNT(DISTINCT b.id) as total
              FROM books b
              LEFT JOIN books_genres bg ON b.id = bg.book_id
              LEFT JOIN genres g ON bg.genre_id = g.id
              WHERE 1=1";

        $params = [];

        if ($search) {
            $query .= " AND (b.title LIKE :search OR b.author LIKE :search)";
            $params[':search'] = "%$search%";
        }

        if ($yearFrom) {
            $query .= " AND b.year >= :yearFrom";
            $params[':yearFrom'] = $yearFrom;
        }

        if ($yearTo) {
            $query .= " AND b.year <= :yearTo";
            $params[':yearTo'] = $yearTo;
        }

        if ($genres && count($genres) > 0) {
            $genrePlaceholders = [];
            foreach ($genres as $index => $genreId) {
                $placeholder = ":genre$index";
                $genrePlaceholders[] = $placeholder;
                $params[$placeholder] = $genreId;
            }
            $query .= " AND g.id IN (" . implode(',', $genrePlaceholders) . ")";
        }

        $stmt = $this->connection->prepare($query);

        foreach ($params as $placeholder => $value) {
            $stmt->bindValue($placeholder, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }




    /**
     * Crea un nuevo libro.
     *
     * @param array $data Datos del libro: user_id, title, author, year, synopsis, number_of_pages, image.
     * @return int|false Devuelve el ID del libro creado o false si hubo un error.
     */
    public function create(array $data)
    {
        try {
            $stmt = $this->connection->prepare(
                "INSERT INTO books (user_id, title, author, year, synopsis, number_of_pages, image) VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $data['user_id'],
                $data['title'],
                $data['author'],
                $data['year'],
                $data['synopsis'],
                $data['number_of_pages'],
                $data['image']
            ]);
            return (int) $this->connection->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error en BookModel::create: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }

    /**
     * Actualiza un libro existente.
     *
     * @param int $id ID del libro.
     * @param array $data Datos a actualizar: title, author, year, synopsis, number_of_pages, image.
     * @return bool Devuelve true si la actualización fue exitosa, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        try {
            $stmt = $this->connection->prepare(
                "UPDATE books SET title = ?, author = ?, year = ?, synopsis = ?, number_of_pages = ?, image = ?, updated_at = NOW() WHERE id = ?"
            );
            return $stmt->execute([
                $data['title'],
                $data['author'],
                $data['year'],
                $data['synopsis'],
                $data['number_of_pages'],
                $data['image'],
                $id
            ]);
        } catch (\PDOException $e) {
            error_log("Error en BookModel::update: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }

    /**
     * Elimina un libro.
     *
     * @param int $id ID del libro.
     * @return bool Devuelve true si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete(int $id): bool
    {
        try {
            $stmt = $this->connection->prepare("DELETE FROM books WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (\PDOException $e) {
            error_log("Error en BookModel::delete: " . $e->getMessage(), 3, __DIR__ . '/../../logs/app.log');
            return false;
        }
    }
}
