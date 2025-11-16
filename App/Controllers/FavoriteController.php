<?php

namespace App\Controllers;

use App\Core\Response;
use App\Models\FavoriteModel;

class FavoriteController
{
    private $favoriteModel;

    public function __construct()
    {
        $this->favoriteModel = new FavoriteModel();
    }

    /**
     * Agregar o quitar un libro de favoritos
     * @param int $userId
     * @param int $bookId
     */
    public function toggle(int $userId, int $bookId)
    {
        $result = $this->favoriteModel->toggleFavorite($userId, $bookId);
        if ($result) {
            Response::json(['success' => true, 'message' => 'Operación de favoritos exitosa']);
        } else {
            Response::json(['success' => false, 'message' => 'Error al actualizar favoritos'], 500);
        }
    }

    /**
     * Obtener todos los libros favoritos del usuario
     * @param int $userId
     */
    public function index(int $userId)
    {
        $favorites = $this->favoriteModel->getFavoritesByUser($userId);
        Response::json(['success' => true, 'favorites' => $favorites]);
    }
}
