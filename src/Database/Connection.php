<?php

namespace App\Database;

use PDO;
use PDOException;
use App\Exceptions\ApiException;

/**
 * Construye la conexion PDO a MySQL con opciones seguras para la API.
 */
class Connection
{
    /**
     * Crea una conexion PDO usando la configuracion privada del laboratorio.
     *
     * @param array $config Configuracion cargada desde config/config.php.
     * @return PDO
     * @throws ApiException Cuando la conexion falla.
     */
    public static function make(array $config)
    {
        $db = $config['db'];
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $db['host'], $db['name']);

        try {
            return new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            throw new ApiException('No se pudo conectar con la base de datos', 500);
        }
    }
}
