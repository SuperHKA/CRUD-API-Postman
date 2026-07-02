<?php

namespace App\Auth;

use App\Exceptions\ApiException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

/**
 * Centraliza la generacion y validacion de tokens JWT para la API.
 */
class AuthService
{
    private $secret;
    private $expiration;
    private $issuer;
    private $algorithm = 'HS256';

    /**
     * @param array $config Configuracion JWT cargada desde config/config.php.
     */
    public function __construct(array $config)
    {
        $this->secret = $config['secret'];
        $this->expiration = (int) $config['expiration'];
        $this->issuer = $config['issuer'];
    }

    /**
     * Genera un token JWT para un usuario autenticado.
     *
     * @param array $user Usuario obtenido de la base de datos.
     * @return string Token firmado.
     */
    public function generateToken(array $user)
    {
        $issuedAt = time();

        $payload = [
            'iss' => $this->issuer,
            'iat' => $issuedAt,
            'exp' => $issuedAt + $this->expiration,
            'sub' => (int) $user['id'],
            'username' => $user['username']
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Devuelve la duracion configurada del token en segundos.
     *
     * @return int
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * Obtiene el encabezado Authorization en Apache u otros servidores.
     *
     * @return string|null
     */
    public function getAuthorizationHeader()
    {
        if (!empty($_SERVER['Authorization'])) {
            return trim($_SERVER['Authorization']);
        }

        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['HTTP_AUTHORIZATION']);
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            foreach ($headers as $name => $value) {
                if (strtolower($name) === 'authorization') {
                    return trim($value);
                }
            }
        }

        return null;
    }

    /**
     * Extrae el token del formato Authorization: Bearer TOKEN.
     *
     * @param string|null $header Encabezado recibido.
     * @return string
     * @throws ApiException Cuando no hay token Bearer.
     */
    public function extractBearerToken($header)
    {
        if (!$header) {
            throw new ApiException('Token de autenticacion requerido', 401);
        }

        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            throw new ApiException('Token Bearer requerido', 401);
        }

        return trim($matches[1]);
    }

    /**
     * Valida un token JWT y devuelve sus datos.
     *
     * @param string $token Token recibido en el encabezado Authorization.
     * @return array Datos almacenados en el token.
     * @throws ApiException Cuando el token no es valido o expiro.
     */
    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            $payload = (array) $decoded;

            if (empty($payload['sub']) || empty($payload['username'])) {
                throw new ApiException('Token invalido', 401);
            }

            if (isset($payload['exp']) && (int) $payload['exp'] < time()) {
                throw new ApiException('Token expirado', 401);
            }

            return [
                'id' => (int) $payload['sub'],
                'username' => $payload['username']
            ];
        } catch (ExpiredException $e) {
            throw new ApiException('Token expirado', 401);
        } catch (SignatureInvalidException $e) {
            throw new ApiException('Token invalido o alterado', 401);
        } catch (UnexpectedValueException $e) {
            throw new ApiException('Token invalido', 401);
        }
    }

    /**
     * Valida la peticion actual y devuelve el usuario autenticado.
     *
     * @return array
     * @throws ApiException Cuando falta o falla el token.
     */
    public function userFromRequest()
    {
        $token = $this->extractBearerToken($this->getAuthorizationHeader());
        return $this->validateToken($token);
    }
}
