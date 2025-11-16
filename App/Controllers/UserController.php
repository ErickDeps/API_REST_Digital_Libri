<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Core\Response;

class UserController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Devuelve el perfil del usuario autenticado.
     * 
     * @param int $userId -> ID del usuario autenticado, inyectado por el middleware.
     */
    public function profile(int $userId)
    {
        $user = $this->userModel->getById($userId);

        if (!$user) {
            Response::json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
            return;
        }

        Response::json(['success' => true, 'user' => $user]);
    }

    /**
     * Funcion check de autenticación de usuario.
     * 
     * @param int $userId -> ID del usuario autenticado, inyectado por el SoftMiddleware.
     */
    public function userContext(?int $userId = null)
    {
        if ($userId) {
            $user = $this->userModel->getById($userId);
            Response::json(['success' => true, 'user' => $user]);
        } else {
            Response::json(['success' => false]);
        }
    }
}
