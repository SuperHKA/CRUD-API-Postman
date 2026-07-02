<?php

use App\Database\Connection;
use App\Repositories\UserRepository;

require_once __DIR__ . '/../vendor/autoload.php';

$configFile = __DIR__ . '/../config/config.php';
if (!file_exists($configFile)) {
    echo "Falta config/config.php\n";
    exit(1);
}

$config = require $configFile;
$users = new UserRepository(Connection::make($config));

if ($users->count() > 0) {
    echo "Ya existe al menos un usuario. No se creo otro administrador.\n";
    exit(0);
}

$username = trim(readline('Usuario administrador: '));
$password = readline('Contrasena: ');

if ($username === '' || $password === '') {
    echo "Usuario y contrasena son obligatorios.\n";
    exit(1);
}

$users->create($username, password_hash($password, PASSWORD_BCRYPT));
echo "Administrador creado correctamente.\n";
