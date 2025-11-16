<?php

namespace App\Middleware;

use App\Core\JWTHandler;
use App\Core\Response;

class AuthMiddleware
{
    private $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JWTHandler();
    }

    /**
     * Maneja la autenticación JWT.
     * 
     * @return array Retorna el payload completo del token JWT decodificado.
     */
    public function handle()
    {
        if (!isset($_COOKIE['auth_token'])) {
            Response::json(['success' => false, 'message' => 'No autenticado'], 401);
            exit;
        }

        $token = $_COOKIE['auth_token'];
        $payload = $this->jwtHandler->verify($token);

        if (!$payload) {
            Response::json(['success' => false, 'message' => 'Token inválido'], 401);
            exit;
        }

        return $payload;
    }
}
