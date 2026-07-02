<?php

namespace App\Controllers;

use App\Auth\AuthService;
use App\Exceptions\ApiException;
use App\Http\JsonResponse;
use App\Models\User;
use App\Repositories\UserRepository;

/**
 * Atiende el endpoint de autenticacion y emision de tokens.
 */
class AuthController
{
    private $users;
    private $auth;

    /**
     * @param UserRepository $users Repositorio de usuarios.
     * @param AuthService $auth Servicio JWT.
     */
    public function __construct(UserRepository $users, AuthService $auth)
    {
        $this->users = $users;
        $this->auth = $auth;
    }

    /**
     * Autentica por JSON y devuelve un JWT si las credenciales son validas.
     *
     * @param array $data Cuerpo JSON con username y password.
     * @return void
     * @throws ApiException Cuando faltan credenciales o son incorrectas.
     */
    public function login(array $data)
    {
        $username = trim($data['username'] ?? '');
        $password = (string) ($data['password'] ?? '');

        if ($username === '' || $password === '') {
            throw new ApiException('Usuario y contrasena son obligatorios', 400);
        }

        $user = $this->users->findByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new ApiException('Credenciales incorrectas', 401);
        }

        JsonResponse::success('Login correcto', [
            'token' => $this->auth->generateToken($user),
            'token_type' => 'Bearer',
            'expires_in' => $this->auth->getExpiration(),
            'user' => User::publicData($user)
        ]);
    }
}
