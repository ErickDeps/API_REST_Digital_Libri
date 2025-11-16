<?php

namespace App\Controllers;

use App\Models\RatingModel;
use App\Core\Response;

class RatingController
{
    private $ratingModel;

    public function __construct()
    {
        $this->ratingModel = new RatingModel();
    }

    /**
     * Listar ratings de un libro.
     *
     * @param int $bookId ID del libro.
     * @return void
     */
    public function index(int $bookId)
    {
        $ratings = $this->ratingModel->getByBookId($bookId);
        Response::json(['success' => true, 'ratings' => $ratings]);
    }

    /**
     * Agregar o actualizar rating de un libro por usuario.
     *
     * @param int $userId ID del usuario autenticado.
     * @param int $bookId ID del libro.
     * @return void
     */
    public function store(int $userId, int $bookId)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $ratingValue = $data['rating'] ?? null;

        if (empty($ratingValue)) {
            Response::json(['success' => false, 'message' => 'El rating no puede estar vacío'], 400);
            return;
        }

        if (!is_numeric($ratingValue) || $ratingValue < 1 || $ratingValue > 5) {
            Response::json(['success' => false, 'message' => 'Rating inválido, debe ser entre 1 y 5'], 422);
            return;
        }

        $success = $this->ratingModel->addOrUpdate($userId, $bookId, (float)$ratingValue);

        if (!$success) {
            Response::json(['success' => false, 'message' => 'Error al agregar rating'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Rating registrado']);
    }
}
