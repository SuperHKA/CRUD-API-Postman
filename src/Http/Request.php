<?php

namespace App\Http;

use App\Exceptions\ApiException;

/**
 * Lee y valida datos de la solicitud HTTP.
 */
class Request
{
    /**
     * Lee y valida el cuerpo JSON enviado al endpoint.
     *
     * @return array
     * @throws ApiException Cuando el cuerpo esta vacio o el JSON es invalido.
     */
    public static function json()
    {
        $body = file_get_contents('php://input');

        if ($body === false || trim($body) === '') {
            throw new ApiException('El cuerpo JSON es obligatorio', 400);
        }

        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new ApiException('JSON invalido o mal formado', 400);
        }

        return $data;
    }
}
