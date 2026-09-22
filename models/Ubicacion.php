<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Modelo de comunas y barrios adaptado a bd_gemo_final:
 * - comuna: id_comuna, nombre  (sin columna activo)
 * - barrio: id_barrio, nombre, id_comuna  (sin columna activo)
 *
 * La UI de "Activo / Inhabilitado" sigue funcionando en lectura
 * tratando todos los registros como activos. La inhabilitación
 * lógica no aplica sin la columna activo (ver alter_crud_ubicacion.sql
 * si en el futuro se desea restaurar esa función).
 */
class Ubicacion
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerComunas(): array
    {
        $filas = $this->conexion->query(
            'SELECT id_comuna, nombre FROM comuna ORDER BY nombre'
        )->fetchAll();
        foreach ($filas as &$f) {
            $f['activo'] = true;
        }
        return $filas;
    }

    public function obtenerComunasActivas(): array
    {
        return $this->conexion->query(
            'SELECT id_comuna, nombre FROM comuna ORDER BY nombre'
        )->fetchAll();
    }

    public function obtenerBarrios(): array
    {
        $filas = $this->conexion->query(
            'SELECT b.id_barrio, b.nombre, b.id_comuna, c.nombre AS comuna
             FROM barrio b INNER JOIN comuna c ON c.id_comuna = b.id_comuna
             ORDER BY c.nombre, b.nombre'
        )->fetchAll();
        foreach ($filas as &$f) {
            $f['activo'] = true;
        }
        return $filas;
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

    /** Sin columna activo: una comuna existe = está disponible. */
    public function comunaActiva(int $id): bool
    {
        return $this->comunaExiste($id);
    }

    public function comunaDelBarrioActiva(int $idBarrio): bool
    {
        return $this->barrioExiste($idBarrio);
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
            throw new InvalidArgumentException('Tabla o columna no permitida.');
        }
        $stmt = $this->conexion->query("SELECT COALESCE(MAX($columna), 0) + 1 FROM $tabla");
        return (int) $stmt->fetchColumn();
    }

    public function crearComuna(string $nombre): int
    {
        $id = $this->siguienteId('comuna', 'id_comuna');
        $stmt = $this->conexion->prepare(
            'INSERT INTO comuna (id_comuna, nombre) VALUES (:id, :nombre)'
        );
        $stmt->execute([':id' => $id, ':nombre' => $nombre]);
        return $id;
    }

    public function actualizarComuna(int $id, string $nombre): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE comuna SET nombre = :nombre WHERE id_comuna = :id'
        );
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

    public function barrioPuedeInhabilitarse(int $id): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT NOT EXISTS (
                        SELECT 1 FROM sitio_barrio sb
                        INNER JOIN sitio_terreno st ON st.id_sitio = sb.id_sitio
                        WHERE sb.id_barrio = :id_sitio AND st.activo = TRUE)
                    AND NOT EXISTS (
                        SELECT 1 FROM zoocriadero z
                        WHERE z.id_barrio = :id_zoo AND z.activo = TRUE)'
        );
        $stmt->execute([':id_sitio' => $id, ':id_zoo' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Sin columna activo en barrio: la inhabilitación lógica no está disponible.
     * Se mantiene la firma para no romper el controlador.
     */
    public function cambiarEstadoBarrio(int $id, bool $activo): bool
    {
        return false;
    }

    public function comunaPuedeInhabilitarse(int $id): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT NOT EXISTS (SELECT 1 FROM barrio WHERE id_comuna = :id)'
        );
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Sin columna activo en comuna: la inhabilitación lógica no está disponible.
     */
    public function cambiarEstadoComuna(int $id, bool $activo): bool
    {
        return false;
    }
}
