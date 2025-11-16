<?php

namespace App\Middleware;

use App\Core\Response;

class RoleMiddleware
{
    private $requiredRole;

    /**
     * Asigna el rol de las rutas como rol requerido.
     * 
     * @param string|null $role
     * @return void  
     */
    public function __construct(string $role = null)
    {
        $this->requiredRole = $role;
    }

    /**
     * Verifica que el usuario autenticado tenga el rol requerido.
     *
     * @param array $payload Payload del JWT proveniente del AuthMiddleware
     * @return array Retorna el payload completo del usuario
     */
    public function handle(array $payload)
    {
        if (!$payload || !isset($payload['sub']) || !isset($payload['role'])) {
            Response::json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
            exit;
        }

        if ($this->requiredRole && $payload['role'] !== $this->requiredRole) {
            Response::json(['success' => false, 'message' => 'No tienes permisos para esta acción'], 403);
            exit;
        }

        return $payload;
    }
}
