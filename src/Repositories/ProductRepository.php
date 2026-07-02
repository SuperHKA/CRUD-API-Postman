<?php

namespace App\Repositories;

use PDO;
use App\Models\Product;

/**
 * Encapsula las consultas preparadas sobre la tabla products.
 */
class ProductRepository
{
    private $pdo;

    /**
     * @param PDO $pdo Conexion activa a MySQL.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Devuelve todos los productos.
     *
     * @return array
     */
    public function all()
    {
        $stmt = $this->pdo->query('SELECT * FROM products ORDER BY id DESC');
        return array_map([Product::class, 'fromRow'], $stmt->fetchAll());
    }

    /**
     * Busca un producto por ID.
     *
     * @param int $id Identificador del producto.
     * @return array|null
     */
    public function find($id)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row ? Product::fromRow($row) : null;
    }

    /**
     * Busca un producto por codigo, permitiendo excluir un ID durante actualizacion.
     *
     * @param string $codigo Codigo unico del producto.
     * @param int|null $excludeId ID que no debe considerarse duplicado.
     * @return array|null
     */
    public function findByCodigo($codigo, $excludeId = null)
    {
        $sql = 'SELECT * FROM products WHERE codigo = :codigo';
        $params = ['codigo' => $codigo];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludeId;
        }

        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();

        return $row ? Product::fromRow($row) : null;
    }

    /**
     * Inserta un producto.
     *
     * @param array $data Datos validados.
     * @return array
     */
    public function create(array $data)
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO products (codigo, producto, precio, cantidad)
             VALUES (:codigo, :producto, :precio, :cantidad)'
        );
        $stmt->execute([
            'codigo' => $data['codigo'],
            'producto' => $data['producto'],
            'precio' => $data['precio'],
            'cantidad' => $data['cantidad']
        ]);

        return $this->find((int) $this->pdo->lastInsertId());
    }

    /**
     * Actualiza un producto existente.
     *
     * @param int $id ID del producto.
     * @param array $data Datos validados.
     * @return array|null Producto actualizado.
     */
    public function update($id, array $data)
    {
        $stmt = $this->pdo->prepare(
            'UPDATE products
             SET codigo = :codigo, producto = :producto, precio = :precio, cantidad = :cantidad
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'codigo' => $data['codigo'],
            'producto' => $data['producto'],
            'precio' => $data['precio'],
            'cantidad' => $data['cantidad']
        ]);

        return $this->find($id);
    }

    /**
     * Elimina un producto por ID.
     *
     * @param int $id ID del producto.
     * @return bool
     */
    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }
}
