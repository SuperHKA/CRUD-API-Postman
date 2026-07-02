<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Representa errores controlados que deben responderse como JSON.
 */
class ApiException extends RuntimeException
{
    private $statusCode;
    private $errors;

    /**
     * Crea una excepcion controlada para respuestas JSON de la API.
     *
     * @param string $message Mensaje seguro para el cliente.
     * @param int $statusCode Codigo HTTP que debe enviarse.
     * @param array $errors Detalles de validacion sin datos sensibles.
     */
    public function __construct($message, $statusCode = 400, array $errors = [])
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errors = $errors;
    }

    /**
     * Devuelve el codigo HTTP asociado al error.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Devuelve errores de validacion aptos para mostrar al cliente.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
