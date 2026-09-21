<?php

require_once __DIR__ . '/../config/database.php';

class ActividadTerreno
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerTodas(): array
    {
        return $this->conexion->query(
            'SELECT id_actividad_terreno, nombre, activo FROM actividad_terreno ORDER BY id_actividad_terreno'
        )->fetchAll();
    }

    
    public function obtenerActivas(): array
    {
        return $this->conexion->query(
            'SELECT id_actividad_terreno, nombre FROM actividad_terreno WHERE activo = TRUE ORDER BY id_actividad_terreno'
        )->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_actividad_terreno, nombre, activo FROM actividad_terreno WHERE id_actividad_terreno = :id'
        );
        $stmt->execute([':id' => $id]);
        $actividad = $stmt->fetch();
        return $actividad ?: null;
    }

    public function nombreExiste(string $nombre, ?int $idExcluir = null): bool
    {
        $sql = 'SELECT 1 FROM actividad_terreno WHERE LOWER(nombre) = LOWER(:nombre)';
        $parametros = [':nombre' => $nombre];
        if ($idExcluir !== null) {
            $sql .= ' AND id_actividad_terreno <> :id_excluir';
            $parametros[':id_excluir'] = $idExcluir;
        }
        $stmt = $this->conexion->prepare($sql . ' LIMIT 1');
        $stmt->execute($parametros);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(string $nombre): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO actividad_terreno (nombre) VALUES (:nombre) RETURNING id_actividad_terreno'
        );
        $stmt->execute([':nombre' => $nombre]);
        return (int) $stmt->fetchColumn();
    }

    public function actualizar(int $id, string $nombre): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE actividad_terreno SET nombre = :nombre WHERE id_actividad_terreno = :id'
        );
        return $stmt->execute([':nombre' => $nombre, ':id' => $id]);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE actividad_terreno SET activo = :activo WHERE id_actividad_terreno = :id'
        );
        $stmt->bindValue(':activo', $activo, PDO::PARAM_BOOL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
