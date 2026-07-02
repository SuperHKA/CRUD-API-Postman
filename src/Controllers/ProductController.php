<?php

namespace App\Controllers;

use App\Exceptions\ApiException;
use App\Http\JsonResponse;
use App\Repositories\ProductRepository;

/**
 * Atiende las operaciones HTTP del recurso productos.
 */
class ProductController
{
    private $products;

    /**
     * @param ProductRepository $products Repositorio de productos.
     */
    public function __construct(ProductRepository $products)
    {
        $this->products = $products;
    }

    /**
     * Lista productos o devuelve uno por ID.
     *
     * @param int|null $id ID opcional.
     * @return void
     */
    public function index($id = null)
    {
        if ($id === null) {
            JsonResponse::success('Productos consultados correctamente', $this->products->all());
        }

        $product = $this->products->find($this->validId($id));
        if (!$product) {
            throw new ApiException('Producto no encontrado', 404);
        }

        JsonResponse::success('Producto consultado correctamente', $product);
    }

    /**
     * Crea un producto.
     *
     * @param array $data Datos recibidos por JSON.
     * @return void
     */
    public function store(array $data)
    {
        $validated = $this->validateProduct($data);

        if ($this->products->findByCodigo($validated['codigo'])) {
            throw new ApiException('El codigo de producto ya existe', 409);
        }

        JsonResponse::success(
            'Producto creado correctamente',
            $this->products->create($validated),
            201
        );
    }

    /**
     * Actualiza un producto existente.
     *
     * @param int|null $id ID del producto.
     * @param array $data Datos recibidos por JSON.
     * @return void
     */
    public function update($id, array $data)
    {
        $id = $this->validId($id);

        if (!$this->products->find($id)) {
            throw new ApiException('Producto no encontrado', 404);
        }

        $validated = $this->validateProduct($data);

        if ($this->products->findByCodigo($validated['codigo'], $id)) {
            throw new ApiException('El codigo de producto ya existe', 409);
        }

        JsonResponse::success(
            'Producto actualizado correctamente',
            $this->products->update($id, $validated)
        );
    }

    /**
     * Elimina un producto existente.
     *
     * @param int|null $id ID del producto.
     * @return void
     */
    public function destroy($id)
    {
        $id = $this->validId($id);

        if (!$this->products->find($id)) {
            throw new ApiException('Producto no encontrado', 404);
        }

        $this->products->delete($id);
        JsonResponse::success('Producto eliminado correctamente', ['id' => $id]);
    }

    /**
     * Valida los campos requeridos del producto.
     *
     * @param array $data Datos recibidos por JSON.
     * @return array Datos normalizados.
     * @throws ApiException Cuando hay errores de validacion.
     */
    private function validateProduct(array $data)
    {
        $errors = [];
        $codigo = trim($data['codigo'] ?? '');
        $producto = trim($data['producto'] ?? '');
        $precio = $data['precio'] ?? null;
        $cantidad = $data['cantidad'] ?? null;

        if ($codigo === '') {
            $errors[] = 'El codigo es obligatorio';
        }

        if ($producto === '') {
            $errors[] = 'El nombre del producto es obligatorio';
        }

        if (!is_numeric($precio)) {
            $errors[] = 'El precio debe ser numerico';
        } elseif ((float) $precio < 0) {
            $errors[] = 'El precio no puede ser negativo';
        }

        if (filter_var($cantidad, FILTER_VALIDATE_INT) === false) {
            $errors[] = 'La cantidad debe ser un entero';
        } elseif ((int) $cantidad < 0) {
            $errors[] = 'La cantidad no puede ser negativa';
        }

        if ($errors) {
            throw new ApiException('Datos de producto invalidos', 400, $errors);
        }

        return [
            'codigo' => $codigo,
            'producto' => $producto,
            'precio' => number_format((float) $precio, 2, '.', ''),
            'cantidad' => (int) $cantidad
        ];
    }

    /**
     * Valida un ID positivo.
     *
     * @param mixed $id Valor recibido desde la ruta.
     * @return int
     * @throws ApiException Cuando el ID no es valido.
     */
    private function validId($id)
    {
        if (filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            throw new ApiException('ID invalido', 400);
        }

        return (int) $id;
    }
}
