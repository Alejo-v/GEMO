<?php

require_once __DIR__ . '/../config/database.php';

class TipoDeposito
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerTodos(): array
    {
        return $this->conexion->query(
            'SELECT id_tipo_deposito, descripcion, activo FROM tipo_deposito ORDER BY id_tipo_deposito'
        )->fetchAll();
    }

    /** Solo los activos, para el <select> del formulario de Depósitos. */
    public function obtenerActivos(): array
    {
        return $this->conexion->query(
            'SELECT id_tipo_deposito, descripcion FROM tipo_deposito WHERE activo = TRUE ORDER BY id_tipo_deposito'
        )->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_tipo_deposito, descripcion, activo FROM tipo_deposito WHERE id_tipo_deposito = :id'
        );
        $stmt->execute([':id' => $id]);
        $tipo = $stmt->fetch();
        return $tipo ?: null;
    }

    public function descripcionExiste(string $descripcion, ?int $idExcluir = null): bool
    {
        $sql = 'SELECT 1 FROM tipo_deposito WHERE LOWER(descripcion) = LOWER(:descripcion)';
        $parametros = [':descripcion' => $descripcion];
        if ($idExcluir !== null) {
            $sql .= ' AND id_tipo_deposito <> :id_excluir';
            $parametros[':id_excluir'] = $idExcluir;
        }
        $stmt = $this->conexion->prepare($sql . ' LIMIT 1');
        $stmt->execute($parametros);
        return (bool) $stmt->fetchColumn();
    }

    /** Antes de inhabilitar: evita dejar depósitos activos apuntando a un tipo sin catálogo visible. */
    public function tieneDepositosActivos(int $id): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT 1 FROM deposito WHERE id_tipo_deposito = :id AND activo = TRUE LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(string $descripcion): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO tipo_deposito (descripcion) VALUES (:descripcion) RETURNING id_tipo_deposito'
        );
        $stmt->execute([':descripcion' => $descripcion]);
        return (int) $stmt->fetchColumn();
    }

    public function actualizar(int $id, string $descripcion): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE tipo_deposito SET descripcion = :descripcion WHERE id_tipo_deposito = :id'
        );
        return $stmt->execute([':descripcion' => $descripcion, ':id' => $id]);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->conexion->prepare('UPDATE tipo_deposito SET activo = :activo WHERE id_tipo_deposito = :id');
        return $stmt->execute([':activo' => $activo, ':id' => $id]);
    }
}
