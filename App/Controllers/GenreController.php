<?php

namespace App\Controllers;

use App\Models\GenreModel;
use App\Core\Response;

/**
 * Controlador encargado de gestionar los géneros literarios.
 */
class GenreController
{
    /**
     * Instancia del modelo de géneros.
     *
     * @var GenreModel
     */
    private $genreModel;

    /**
     * Constructor del controlador.
     * Inicializa el modelo de géneros.
     */
    public function __construct()
    {
        $this->genreModel = new GenreModel();
    }

    /**
     * Obtiene todos los géneros existentes.
     */
    public function index()
    {
        $genres = $this->genreModel->getAll();
        Response::json(['success' => true, 'genres' => $genres]);
    }

    /**
     * Muestra un género específico por su ID.
     *
     * @param int $id ID del género.
     */
    public function show(int $id)
    {
        $genre = $this->genreModel->getById($id);
        if (!$genre) {
            Response::json(['success' => false, 'message' => 'Género no encontrado'], 404);
            return;
        }

        Response::json(['success' => true, 'genre' => $genre]);
    }

    /**
     * Crea un nuevo género.
     */
    public function store()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['genre'])) {
            Response::json(['success' => false, 'message' => 'El campo genre es obligatorio'], 400);
            return;
        }

        $genreId = $this->genreModel->create(strtolower($data['genre']));
        if (!$genreId) {
            Response::json(['success' => false, 'message' => 'No se pudo crear el género'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Género creado', 'genreId' => $genreId]);
    }

    /**
     * Actualiza un género existente.
     *
     * @param int $genreId ID del género a actualizar.
     */
    public function update(int $userId, int $genreId)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['genre'])) {
            Response::json(['success' => false, 'message' => 'El campo genre es obligatorio'], 400);
            return;
        }

        $genre = $this->genreModel->getById($genreId);
        if (!$genre) {
            Response::json(['success' => false, 'message' => 'Género no encontrado'], 404);
            return;
        }

        $updated = $this->genreModel->update($genreId, strtolower($data['genre']));
        if (!$updated) {
            Response::json(['success' => false, 'message' => 'No se pudo actualizar el género'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Género actualizado']);
    }

    /**
     * Elimina un género.
     *
     * @param int $genreId ID del género a eliminar.
     */
    public function destroy(int $userId, int $genreId)
    {
        $genre = $this->genreModel->getById($genreId);
        if (!$genre) {
            Response::json(['success' => false, 'message' => 'Género no encontrado'], 404);
            return;
        }

        $deleted = $this->genreModel->delete($genreId);
        if (!$deleted) {
            Response::json(['success' => false, 'message' => 'No se pudo eliminar el género'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Género eliminado']);
    }
}
