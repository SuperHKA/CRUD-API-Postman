<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Auth\AuthService;

$config = require __DIR__ . '/config/config.php';
$usuario_autenticado = (object) (new AuthService($config['jwt']))->userFromRequest();
