<?php

use PHPUnit\Framework\TestCase;
use App\Models\UserModel;

class UserModelTest extends TestCase
{
    private $userModel;

    protected function setUp(): void
    {
        $this->userModel = new UserModel();
    }

    public function testCreateUser()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => '123456'
        ];

        $userId = $this->userModel->createUser($data);
        $this->assertIsNumeric($userId, "Se debe devolver un ID numérico al crear el usuario");
    }

    public function testGetByEmail()
    {
        $user = $this->userModel->getByEmail('testuser@example.com');
        $this->assertNotEmpty($user, "Debe devolver un usuario existente");
        $this->assertEquals('Test User', $user['name']);
    }
}
