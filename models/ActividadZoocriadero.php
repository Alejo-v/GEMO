<?php

require_once __DIR__ . '/../config/database.php';

class ActividadZoocriadero
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerTodas(): array
    {
        return $this->conexion->query(
            'SELECT id_actividad_zoocriadero, nombre, activo FROM actividad_zoocriadero ORDER BY id_actividad_zoocriadero'
        )->fetchAll();
    }

    
    public function obtenerActivas(): array
    {
        return $this->conexion->query(
            'SELECT id_actividad_zoocriadero, nombre FROM actividad_zoocriadero WHERE activo = TRUE ORDER BY id_actividad_zoocriadero'
        )->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_actividad_zoocriadero, nombre, activo FROM actividad_zoocriadero WHERE id_actividad_zoocriadero = :id'
        );
        $stmt->execute([':id' => $id]);
        $actividad = $stmt->fetch();
        return $actividad ?: null;
    }

    public function nombreExiste(string $nombre, ?int $idExcluir = null): bool
    {
        $sql = 'SELECT 1 FROM actividad_zoocriadero WHERE LOWER(nombre) = LOWER(:nombre)';
        $parametros = [':nombre' => $nombre];
        if ($idExcluir !== null) {
            $sql .= ' AND id_actividad_zoocriadero <> :id_excluir';
            $parametros[':id_excluir'] = $idExcluir;
        }
        $stmt = $this->conexion->prepare($sql . ' LIMIT 1');
        $stmt->execute($parametros);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(string $nombre): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO actividad_zoocriadero (nombre) VALUES (:nombre) RETURNING id_actividad_zoocriadero'
        );
        $stmt->execute([':nombre' => $nombre]);
        return (int) $stmt->fetchColumn();
    }

    public function actualizar(int $id, string $nombre): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE actividad_zoocriadero SET nombre = :nombre WHERE id_actividad_zoocriadero = :id'
        );
        return $stmt->execute([':nombre' => $nombre, ':id' => $id]);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE actividad_zoocriadero SET activo = :activo WHERE id_actividad_zoocriadero = :id'
        );
        $stmt->bindValue(':activo', $activo, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
