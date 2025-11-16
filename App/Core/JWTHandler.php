<?php

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler
{
    private $secret;
    private $algo;

    public function __construct()
    {
        $this->secret = $_ENV['JWT_SECRET_KEY'] ?? 'mi_secreto_super_seguro';
        $this->algo = 'HS256';
    }

    /**
     * Genera un token JWT
     *
     * @param array $payload Datos a incluir en el token (ej: ['sub' => $userId])
     * @param int $exp Expiración en segundos
     * @return string Token JWT
     */
    public function generate(array $payload, int $exp = 3600): string
    {
        $time = time();
        $token = array_merge($payload, [
            'iat' => $time,
            'exp' => $time + $exp,
            'iss' => 'digital-libri.com',
            'aud' => 'digital-libri.com',
        ]);

        return JWT::encode($token, $this->secret, $this->algo);
    }

    /**
     * Verifica un token JWT
     *
     * @param string $token
     * @return array|bool Devuelve el payload si es válido, false si no
     */
    public function verify(string $token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algo));
            return (array) $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtiene el token desde la cookie "auth_token"
     */
    public function getTokenFromCookie(): ?string
    {
        return $_COOKIE['auth_token'] ?? null;
    }

    /**
     * Obtiene el usuario del token actual
     */
    public function getUserFromToken(): ?array
    {
        $token = $this->getTokenFromCookie();
        if (!$token) return null;
        return $this->verify($token);
    }
}
