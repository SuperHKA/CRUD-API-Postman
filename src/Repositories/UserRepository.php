<?php

namespace App\Repositories;

use PDO;

/**
 * Encapsula las consultas preparadas sobre la tabla users.
 */
class UserRepository
{
    private $pdo;

    /**
     * @param PDO $pdo Conexion activa a MySQL.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Busca un usuario por nombre.
     *
     * @param string $username Nombre de usuario.
     * @return array|null
     */
    public function findByUsername($username)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    /**
     * Cuenta los usuarios registrados.
     *
     * @return int
     */
    public function count()
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    }

    /**
     * Crea un usuario administrador con hash Bcrypt.
     *
     * @param string $username Nombre de usuario.
     * @param string $passwordHash Hash generado con password_hash.
     * @return int ID creado.
     */
    public function create($username, $passwordHash)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)'
        );
        $stmt->execute([
            'username' => $username,
            'password_hash' => $passwordHash
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
