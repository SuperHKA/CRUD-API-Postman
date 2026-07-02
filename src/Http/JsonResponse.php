<?php

namespace App\Http;

/**
 * Estandariza las respuestas JSON de exito y error.
 */
class JsonResponse
{
    /**
     * Envia una respuesta JSON exitosa.
     *
     * @param string $message Mensaje de resultado.
     * @param mixed $data Datos opcionales de la respuesta.
     * @param int $status Codigo HTTP.
     * @return void
     */
    public static function success($message, $data = null, $status = 200)
    {
        self::send([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Envia una respuesta JSON de error.
     *
     * @param string $message Mensaje seguro para el cliente.
     * @param int $status Codigo HTTP.
     * @param array $errors Errores de validacion.
     * @return void
     */
    public static function error($message, $status = 400, array $errors = [])
    {
        self::send([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    /**
     * Escribe el cuerpo JSON y termina la ejecucion.
     *
     * @param array $payload Datos a serializar.
     * @param int $status Codigo HTTP.
     * @return void
     */
    public static function send(array $payload, $status)
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
