<?php

require_once __DIR__ . '/../config/database.php';

class Ubicacion
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerComunas(): array
    {
        return $this->conexion->query(
            'SELECT id_comuna, nombre FROM comuna ORDER BY nombre'
        )->fetchAll();
    }

    public function obtenerBarrios(): array
    {
        return $this->conexion->query(
            'SELECT b.id_barrio, b.nombre, b.id_comuna, c.nombre AS comuna
             FROM barrio b INNER JOIN comuna c ON c.id_comuna = b.id_comuna
             ORDER BY c.nombre, b.nombre'
        )->fetchAll();
    }

    public function comunaExiste(int $id): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM comuna WHERE id_comuna = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function barrioExiste(int $id): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM barrio WHERE id_barrio = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function comunaNombreExiste(string $nombre, ?int $excepto = null): bool
    {
        $sql = 'SELECT 1 FROM comuna WHERE LOWER(nombre) = LOWER(:nombre)';
        $params = [':nombre' => $nombre];
        if ($excepto !== null) {
            $sql .= ' AND id_comuna <> :id';
            $params[':id'] = $excepto;
        }
        $stmt = $this->conexion->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    public function barrioNombreExiste(string $nombre, int $idComuna, ?int $excepto = null): bool
    {
        $sql = 'SELECT 1 FROM barrio WHERE LOWER(nombre) = LOWER(:nombre) AND id_comuna = :id_comuna';
        $params = [':nombre' => $nombre, ':id_comuna' => $idComuna];
        if ($excepto !== null) {
            $sql .= ' AND id_barrio <> :id';
            $params[':id'] = $excepto;
        }
        $stmt = $this->conexion->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetchColumn();
    }

    private function siguienteId(string $tabla, string $columna): int
    {
        $permitidas = [
            'comuna' => 'id_comuna',
            'barrio' => 'id_barrio',
        ];
        if (!isset($permitidas[$tabla]) || $permitidas[$tabla] !== $columna) {
            throw new InvalidArgumentException('Tabla de ubicación no válida.');
        }
        return (int) $this->conexion->query("SELECT COALESCE(MAX($columna), 0) + 1 FROM $tabla")->fetchColumn();
    }

    public function crearComuna(string $nombre): int
    {
        $id = $this->siguienteId('comuna', 'id_comuna');
        $stmt = $this->conexion->prepare('INSERT INTO comuna (id_comuna, nombre) VALUES (:id, :nombre)');
        $stmt->execute([':id' => $id, ':nombre' => $nombre]);
        return $id;
    }

    public function actualizarComuna(int $id, string $nombre): bool
    {
        $stmt = $this->conexion->prepare('UPDATE comuna SET nombre = :nombre WHERE id_comuna = :id');
        return $stmt->execute([':id' => $id, ':nombre' => $nombre]);
    }

    public function crearBarrio(string $nombre, int $idComuna): int
    {
        $id = $this->siguienteId('barrio', 'id_barrio');
        $stmt = $this->conexion->prepare(
            'INSERT INTO barrio (id_barrio, nombre, id_comuna) VALUES (:id, :nombre, :id_comuna)'
        );
        $stmt->execute([':id' => $id, ':nombre' => $nombre, ':id_comuna' => $idComuna]);
        return $id;
    }

    public function actualizarBarrio(int $id, string $nombre, int $idComuna): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE barrio SET nombre = :nombre, id_comuna = :id_comuna WHERE id_barrio = :id'
        );
        return $stmt->execute([':id' => $id, ':nombre' => $nombre, ':id_comuna' => $idComuna]);
    }

    public function barrioPuedeEliminarse(int $id): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT NOT EXISTS (SELECT 1 FROM sitio_barrio WHERE id_barrio = :id)
                    AND NOT EXISTS (SELECT 1 FROM zoocriadero WHERE id_barrio = :id)'
        );
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function eliminarBarrio(int $id): bool
    {
        $stmt = $this->conexion->prepare('DELETE FROM barrio WHERE id_barrio = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function comunaPuedeEliminarse(int $id): bool
    {
        $stmt = $this->conexion->prepare('SELECT NOT EXISTS (SELECT 1 FROM barrio WHERE id_comuna = :id)');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function eliminarComuna(int $id): bool
    {
        $stmt = $this->conexion->prepare('DELETE FROM comuna WHERE id_comuna = :id');
        return $stmt->execute([':id' => $id]);
    }
}
