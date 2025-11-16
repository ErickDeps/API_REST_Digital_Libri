<?php

namespace App\Middleware;

use App\Core\JWTHandler;

class SoftAuthMiddleware
{
    private $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JWTHandler();
    }

    public function handle()
    {
        // 1️⃣ Intentar obtener token del HEADER Authorization: Bearer xxx
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $decoded = $this->jwtHandler->verify($token);

            if ($decoded !== false) {
                return $decoded; // Usuario autenticado
            }

            return null; // Token inválido → tratamos como usuario no autenticado
        }

        // 2️⃣ Si no hay Authorization, intentamos con la cookie "auth_token"
        $tokenFromCookie = $this->jwtHandler->getTokenFromCookie();

        if ($tokenFromCookie) {
            $decoded = $this->jwtHandler->verify($tokenFromCookie);
            if ($decoded !== false) {
                return $decoded;
            }
        }

        // 3️⃣ Si no hay token o es inválido → usuario NO autenticado
        return null;
    }
}
