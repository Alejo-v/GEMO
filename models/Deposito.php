<?php

require_once __DIR__ . '/../config/database.php';

class Deposito
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerTodos(): array
    {
        $sql = 'SELECT d.id_deposito, d.id_sitio, d.id_tipo_deposito, d.activo,
                        td.descripcion AS tipo_deposito, st.direccion AS sitio_direccion
                FROM deposito d
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                INNER JOIN sitio_terreno st ON st.id_sitio = d.id_sitio
                ORDER BY d.id_deposito DESC';
        return $this->conexion->query($sql)->fetchAll();
    }

    
    public function obtenerActivos(): array
    {
        $sql = 'SELECT d.id_deposito, d.id_sitio, d.id_tipo_deposito, td.descripcion AS tipo_deposito
                FROM deposito d
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                WHERE d.activo = TRUE
                ORDER BY d.id_sitio, d.id_deposito';
        return $this->conexion->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $idDeposito): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_deposito, id_sitio, id_tipo_deposito, activo FROM deposito WHERE id_deposito = :id'
        );
        $stmt->execute([':id' => $idDeposito]);
        $deposito = $stmt->fetch();
        return $deposito ?: null;
    }

    public function obtenerTiposDeposito(): array
    {
        return $this->conexion->query(
                'SELECT id_tipo_deposito, descripcion FROM tipo_deposito WHERE activo = TRUE ORDER BY id_tipo_deposito'
        )->fetchAll();
    }

    public function sitioExiste(int $idSitio): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM sitio_terreno WHERE id_sitio = :id LIMIT 1');
        $stmt->execute([':id' => $idSitio]);
        return (bool) $stmt->fetchColumn();
    }

    public function tipoDepositoExiste(int $idTipo): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM tipo_deposito WHERE id_tipo_deposito = :id LIMIT 1');
        $stmt->execute([':id' => $idTipo]);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO deposito (id_sitio, id_tipo_deposito) VALUES (:id_sitio, :id_tipo_deposito)
                RETURNING id_deposito'
        );
        $stmt->execute($datos);
        return (int) $stmt->fetchColumn();
    }

    public function actualizar(int $idDeposito, array $datos): bool
    {
        $datos[':id'] = $idDeposito;
        $stmt = $this->conexion->prepare(
            'UPDATE deposito SET id_sitio = :id_sitio, id_tipo_deposito = :id_tipo_deposito WHERE id_deposito = :id'
        );
        return $stmt->execute($datos);
    }

    public function cambiarEstado(int $idDeposito, bool $activo): bool
    {
        $stmt = $this->conexion->prepare('UPDATE deposito SET activo = :activo WHERE id_deposito = :id');
        return $stmt->execute([':activo' => $activo, ':id' => $idDeposito]);
    }
}
