<?php

use App\Auth\AuthService;
use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Database\Connection;
use App\Exceptions\ApiException;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;

require_once __DIR__ . '/../vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

$configFile = __DIR__ . '/../config/config.php';
if (!file_exists($configFile)) {
    JsonResponse::error('Falta config/config.php. Copia config/config.example.php y ajusta tus datos.', 500);
}

$config = require $configFile;

try {
    $authService = new AuthService($config['jwt']);
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $route = routeParts();
    $resource = $route[1] ?? null;
    $id = $route[2] ?? ($_GET['id'] ?? null);
    $isApi = ($route[0] ?? null) === 'api';
    $isLogin = $isApi && $resource === 'login';
    $isProducts = $isApi && $resource === 'products';

    if (!$isLogin && !$isProducts) {
        throw new ApiException('Ruta no encontrada', 404);
    }

    if ($isProducts) {
        $authService->userFromRequest();
    }

    $pdo = Connection::make($config);
    $userRepository = new UserRepository($pdo);
    $productRepository = new ProductRepository($pdo);
    $authController = new AuthController($userRepository, $authService);
    $productController = new ProductController($productRepository);

    switch ($method) {
        case 'GET':
            if ($isProducts) {
                $productController->index($id);
            }
            throw new ApiException('Metodo no permitido', 405);

        case 'POST':
            if ($isLogin) {
                $authController->login(Request::json());
            }

            if ($isProducts && $id === null) {
                $productController->store(Request::json());
            }

            throw new ApiException('Metodo no permitido', 405);

        case 'PUT':
            if ($isProducts) {
                $productController->update($id, Request::json());
            }
            throw new ApiException('Metodo no permitido', 405);

        case 'DELETE':
            if ($isProducts) {
                $productController->destroy($id);
            }
            throw new ApiException('Metodo no permitido', 405);

        default:
            throw new ApiException('Metodo no permitido', 405);
    }
} catch (ApiException $e) {
    JsonResponse::error($e->getMessage(), $e->getStatusCode(), $e->getErrors());
} catch (Throwable $e) {
    JsonResponse::error('Error interno inesperado', 500);
}

/**
 * Interpreta rutas limpias y tambien el formato index.php?resource=products&id=1.
 *
 * @return array
 */
function routeParts()
{
    if (!empty($_GET['resource'])) {
        return ['api', trim($_GET['resource'], '/')];
    }

    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');

    if ($scriptDir !== '' && $scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
        $path = substr($path, strlen($scriptDir));
    }

    $path = preg_replace('#^/index\.php#', '', $path);
    $path = trim($path, '/');

    return $path === '' ? [] : explode('/', $path);
}
