<?php

require_once __DIR__ . '/../config/database.php';

class TipoTanque
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerTodos(): array
    {
        return $this->conexion->query(
            'SELECT id_tipo_tanque, descripcion, activo FROM tipo_tanque ORDER BY id_tipo_tanque'
        )->fetchAll();
    }

    
    public function obtenerActivos(): array
    {
        return $this->conexion->query(
            'SELECT id_tipo_tanque, descripcion FROM tipo_tanque WHERE activo = TRUE ORDER BY id_tipo_tanque'
        )->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_tipo_tanque, descripcion, activo FROM tipo_tanque WHERE id_tipo_tanque = :id'
        );
        $stmt->execute([':id' => $id]);
        $tipo = $stmt->fetch();
        return $tipo ?: null;
    }

    public function descripcionExiste(string $descripcion, ?int $idExcluir = null): bool
    {
        $sql = 'SELECT 1 FROM tipo_tanque WHERE LOWER(descripcion) = LOWER(:descripcion)';
        $parametros = [':descripcion' => $descripcion];
        if ($idExcluir !== null) {
            $sql .= ' AND id_tipo_tanque <> :id_excluir';
            $parametros[':id_excluir'] = $idExcluir;
        }
        $stmt = $this->conexion->prepare($sql . ' LIMIT 1');
        $stmt->execute($parametros);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(string $descripcion): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO tipo_tanque (descripcion) VALUES (:descripcion) RETURNING id_tipo_tanque'
        );
        $stmt->execute([':descripcion' => $descripcion]);
        return (int) $stmt->fetchColumn();
    }

    public function actualizar(int $id, string $descripcion): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE tipo_tanque SET descripcion = :descripcion WHERE id_tipo_tanque = :id'
        );
        return $stmt->execute([':descripcion' => $descripcion, ':id' => $id]);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->conexion->prepare('UPDATE tipo_tanque SET activo = :activo WHERE id_tipo_tanque = :id');
        $stmt->bindValue(':activo', $activo, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
