<?php

namespace App\Controllers;

use App\Models\BookGenreModel;
use App\Core\Response;

/**
 * Controlador encargado de manejar la relación libros-géneros.
 */
class BookGenreController
{
    /**
     * Instancia del modelo BookGenre.
     *
     * @var BookGenreModel
     */
    private $bookGenreModel;

    public function __construct()
    {
        $this->bookGenreModel = new BookGenreModel();
    }

    /**
     * Obtiene todos los géneros asignados a un libro.
     *
     * @param int $bookId ID del libro.
     */
    public function index(int $bookId)
    {
        $genres = $this->bookGenreModel->getGenresByBookId($bookId);
        Response::json(['success' => true, 'bookId' => $bookId, 'genres' => $genres]);
    }

    /**
     * Asigna uno o varios géneros a un libro.
     *
     * @param int $bookId ID del libro.
     */
    public function store(int $userId = null, int $bookId)
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['genre_ids']) || !is_array($data['genre_ids'])) {
            Response::json(['success' => false, 'message' => 'Debe enviar un arreglo de IDs de géneros'], 400);
            return;
        }

        $added = $this->bookGenreModel->addGenresToBook($bookId, $data['genre_ids']);
        if (!$added) {
            Response::json(['success' => false, 'message' => 'No se pudieron asignar los géneros'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Géneros asignados al libro']);
    }

    /**
     * Elimina un género de un libro.
     *
     * @param int $bookId ID del libro.
     * @param int $genreId ID del género.
     */
    public function destroy(int $userId = null, int $bookId, int $genreId)
    {
        $deleted = $this->bookGenreModel->removeGenreFromBook($bookId, $genreId);
        if (!$deleted) {
            Response::json(['success' => false, 'message' => 'No se pudo eliminar el género del libro'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Género eliminado del libro']);
    }
}
