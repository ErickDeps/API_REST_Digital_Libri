<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Core\JWTHandler;
use App\Core\Response;

class AuthController
{

    private $userModel;
    private $jwtHandler;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->jwtHandler = new JWTHandler();
    }

    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($password)) {
            Response::json(['success' => false, 'message' => 'Email y contraseña son requeridos'], 400);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['success' => false, 'message' => 'Formato de email inválido'], 400);
            return;
        }

        $user = $this->userModel->getByEmail($email);
        if (!$user) {
            Response::json(['success' => false, 'message' => 'Usuario no encontrado'], 401);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            Response::json(['success' => false, 'message' => 'Contraseña incorrecta'], 401);
            return;
        }

        $token = $this->jwtHandler->generate([
            'sub' => $user['id'],
            'role' => $user['role']
        ]);

        setcookie("auth_token", $token, [
            'expires' => time() + 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => false,
            'samesite' => 'Strict'
        ]);

        Response::json(['success' => true, 'message' => 'Login exitoso']);
    }

    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $name = strtolower(trim($data['name'] ?? ''));
        $email = strtolower(trim($data['email'] ?? ''));
        $password = trim($data['password'] ?? '');
        $confirmPassword = trim($data['confirmPassword'] ?? '');
        $role = $data['role'] ?? '';

        if (empty($name) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
            Response::json(['success' => false, 'message' => 'Todos los campos son obligatorios'], 400);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::json(['success' => false, 'message' => 'Formato de email inválido'], 400);
            return;
        }

        if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
            Response::json(['success' => false, 'message' => 'El nombre solo puede contener letras y espacios'], 400);
            return;
        }

        if ($password !== $confirmPassword) {
            Response::json(['success' => false, 'message' => 'Las contraseñas no coinciden'], 400);
            return;
        }

        if (strlen($password) < 6) {
            Response::json(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres'], 400);
            return;
        }

        if (
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[\W]/', $password)
        ) {
            Response::json([
                'success' => false,
                'message' => 'La contraseña debe contener al menos una mayúscula, un número y un símbolo'
            ], 400);
            return;
        }

        if ($role !== 'reader' && $role !== 'author') {
            $role = 'reader';
        }

        $existingUser = $this->userModel->getByEmail($email);
        if ($existingUser) {
            Response::json(['success' => false, 'message' => 'El email ya está registrado'], 409);
            return;
        }

        $userId = $this->userModel->createUser([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);

        if (!$userId) {
            Response::json(['success' => false, 'message' => 'Error al crear el usuario'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Usuario registrado exitosamente', 'userId' => $userId]);
    }



    public function logout()
    {
        setcookie("auth_token", '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'secure' => false,
            'samesite' => 'Strict'
        ]);

        Response::json(['success' => true, 'message' => 'Logout exitoso']);
    }
}
