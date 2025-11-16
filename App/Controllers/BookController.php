<?php

namespace App\Controllers;

use App\Models\BookModel;
use App\Core\Response;

class BookController
{
    private $bookModel;

    public function __construct()
    {
        $this->bookModel = new BookModel();
    }

    /**
     * Obtiene la lista de libros con paginación y filtros opcionales.
     *
     * Los filtros se reciben mediante query params en la URL:
     * - `page` (int, opcional): Página actual para paginación (por defecto 1).
     * - `limit` (int, opcional): Número de libros por página (por defecto 10).
     * - `search` (string, opcional): Término de búsqueda que compara título y autor.
     * - `yearFrom` (int, opcional): Año mínimo de publicación.
     * - `yearTo` (int, opcional): Año máximo de publicación.
     * - `genres` (string, opcional): IDs de géneros separados por coma, ej. "1,3,5".
     * - `userId (int opcional): En caso de que haya algun usuario autenticado para mostrar libros en favorito`
     * @return void
     */
    public function index(?int $userId = null)
    {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
        $offset = ($page - 1) * $limit;

        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $yearFrom = isset($_GET['yearFrom']) ? (int)$_GET['yearFrom'] : null;
        $yearTo = isset($_GET['yearTo']) ? (int)$_GET['yearTo'] : null;
        $genres = isset($_GET['genres']) ? array_map('intval', explode(',', $_GET['genres'])) : null;

        $books = $this->bookModel->getFilteredPaginated($limit, $offset, $search, $yearFrom, $yearTo, $genres, $userId);

        $totalBooks = $this->bookModel->countFiltered($search, $yearFrom, $yearTo, $genres);
        $totalPages = ceil($totalBooks / $limit);

        Response::json([
            'success' => true,
            'page' => $page,
            'limit' => $limit,
            'totalBooks' => $totalBooks,
            'totalPages' => $totalPages,
            'books' => $books
        ]);
    }



    /**
     * Muestra un libro específico
     *
     * @param int $id ID del libro
     */
    public function show(int $bookId)
    {
        $book = $this->bookModel->getBookDetails($bookId);
        if (!$book) {
            Response::json(['success' => false, 'message' => 'Libro no encontrado'], 404);
            return;
        }
        Response::json(['success' => true, 'book' => $book]);
    }

    /**
     * Muestra los libros asociados al usuario autenticado
     *
     * @param int $userId ID del usuario autenticado
     */
    public function getByUser(int $userId)
    {
        $books = $this->bookModel->getByUserId($userId);
        if (!$books) {
            Response::json(['success' => false, 'message' => 'Libros no encontrados'], 404);
            return;
        }
        Response::json(['success' => true, 'books' => $books]);
    }

    /**
     * Agrega un nuevo libro
     *
     * @param int $userId ID del usuario autenticado (viene del middleware)
     */
    public function store(int $userId)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validaciones mínimas
        $required = ['title', 'author', 'year', 'synopsis', 'number_of_pages', 'image'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::json(['success' => false, 'message' => "El campo $field es obligatorio"], 400);
                return;
            }
        }

        $bookId = $this->bookModel->create(array_merge($data, ['user_id' => $userId]));

        Response::json(['success' => true, 'message' => 'Libro creado', 'bookId' => $bookId]);
    }

    /**
     * Actualiza un libro
     *
     * @param int $userId ID del usuario autenticado
     * @param int $id ID del libro
     */
    public function update(int $userId, int $bookId)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $required = ['title', 'author', 'year', 'synopsis', 'number_of_pages', 'image'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Response::json(['success' => false, 'message' => "El campo $field es obligatorio"], 400);
                return;
            }
        }

        $book = $this->bookModel->getBookById($bookId);
        if (!$book) {
            Response::json(['success' => false, 'message' => 'Libro no encontrado'], 404);
            return;
        }

        // Solo el dueño puede editar
        if ($book['user_id'] != $userId) {
            Response::json(['success' => false, 'message' => 'No tienes permiso para editar este libro'], 403);
            return;
        }

        $this->bookModel->update($bookId, $data);
        Response::json(['success' => true, 'message' => 'Libro actualizado']);
    }

    /**
     * Elimina un libro
     *
     * @param int $userId ID del usuario autenticado
     * @param int $id ID del libro
     */
    public function destroy(int $userId, int $bookId)
    {
        $book = $this->bookModel->getBookById($bookId);
        if (!$book) {
            Response::json(['success' => false, 'message' => 'Libro no encontrado'], 404);
            return;
        }

        if ($book['user_id'] != $userId) {
            Response::json(['success' => false, 'message' => 'No tienes permiso para eliminar este libro'], 403);
            return;
        }

        $this->bookModel->delete($bookId);
        Response::json(['success' => true, 'message' => 'Libro eliminado']);
    }
}
