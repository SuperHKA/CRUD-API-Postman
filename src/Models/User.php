<?php

namespace App\Models;

/**
 * Normaliza los datos publicos del usuario autenticado.
 */
class User
{
    /**
     * Normaliza una fila de usuario para exponerla sin password_hash.
     *
     * @param array $row Fila obtenida de la base de datos.
     * @return array
     */
    public static function publicData(array $row)
    {
        return [
            'id' => (int) $row['id'],
            'username' => $row['username']
        ];
    }
}
