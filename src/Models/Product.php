<?php

namespace App\Models;

/**
 * Normaliza los datos de productos antes de enviarlos como JSON.
 */
class Product
{
    /**
     * Normaliza una fila de producto para la respuesta JSON.
     *
     * @param array $row Fila obtenida de la base de datos.
     * @return array
     */
    public static function fromRow(array $row)
    {
        return [
            'id' => (int) $row['id'],
            'codigo' => $row['codigo'],
            'producto' => $row['producto'],
            'precio' => (float) $row['precio'],
            'cantidad' => (int) $row['cantidad'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
}
