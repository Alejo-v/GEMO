<?php

require_once __DIR__ . '/../config/database.php';

class Sitio
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    /** Catálogo de barrios (con su comuna) para los checkboxes del formulario. */
    public function obtenerBarrios(): array
    {
        $sql = 'SELECT b.id_barrio, b.nombre, c.nombre AS comuna
                FROM barrio b
                INNER JOIN comuna c ON c.id_comuna = b.id_comuna
                ORDER BY c.nombre, b.nombre';
        return $this->conexion->query($sql)->fetchAll();
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT st.id_sitio, st.direccion, st.latitud, st.longitud, st.activo,
                       COALESCE(string_agg(DISTINCT b.nombre, ', ' ORDER BY b.nombre), 'Sin barrio') AS barrios,
                       COALESCE(array_agg(DISTINCT sb.id_barrio) FILTER (WHERE sb.id_barrio IS NOT NULL), '{}') AS ids_barrios
                FROM sitio_terreno st
                LEFT JOIN sitio_barrio sb ON sb.id_sitio = st.id_sitio
                LEFT JOIN barrio b ON b.id_barrio = sb.id_barrio
                GROUP BY st.id_sitio, st.direccion, st.latitud, st.longitud, st.activo
                ORDER BY st.id_sitio DESC";
        $stmt = $this->conexion->query($sql);
        $filas = $stmt->fetchAll();
        foreach ($filas as &$fila) {
            // PDO (pgsql) devuelve el array de Postgres como texto "{1,2}"; lo convertimos a array PHP de enteros.
            $fila['ids_barrios'] = array_map('intval', array_filter(
                explode(',', trim($fila['ids_barrios'], '{}')),
                fn ($v) => $v !== ''
            ));
        }
        return $filas;
    }

    /** Solo los activos, para los <select> de otros formularios (Depósito, Registro de campo). */
    public function obtenerActivos(): array
    {
        $sql = "SELECT st.id_sitio, st.direccion,
                       COALESCE(string_agg(DISTINCT b.nombre, ', ' ORDER BY b.nombre), 'Sin barrio') AS barrios
                FROM sitio_terreno st
                LEFT JOIN sitio_barrio sb ON sb.id_sitio = st.id_sitio
                LEFT JOIN barrio b ON b.id_barrio = sb.id_barrio
                WHERE st.activo = TRUE
                GROUP BY st.id_sitio, st.direccion
                ORDER BY st.id_sitio";
        return $this->conexion->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $idSitio): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_sitio, direccion, latitud, longitud, activo FROM sitio_terreno WHERE id_sitio = :id'
        );
        $stmt->execute([':id' => $idSitio]);
        $sitio = $stmt->fetch();
        if (!$sitio) {
            return null;
        }

        $stmtBarrios = $this->conexion->prepare(
            'SELECT id_barrio FROM sitio_barrio WHERE id_sitio = :id'
        );
        $stmtBarrios->execute([':id' => $idSitio]);
        $sitio['barrios'] = $stmtBarrios->fetchAll(PDO::FETCH_COLUMN);

        return $sitio;
    }

    public function barrioExiste(int $idBarrio): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM barrio WHERE id_barrio = :id LIMIT 1');
        $stmt->execute([':id' => $idBarrio]);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(array $datos, array $idsBarrios): int
    {
        $this->conexion->beginTransaction();
        try {
            $stmt = $this->conexion->prepare(
                'INSERT INTO sitio_terreno (direccion, latitud, longitud)
                 VALUES (:direccion, :latitud, :longitud)
                 RETURNING id_sitio'
            );
            $stmt->execute($datos);
            $idSitio = (int) $stmt->fetchColumn();

            $this->vincularBarrios($idSitio, $idsBarrios);

            $this->conexion->commit();
            return $idSitio;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function actualizar(int $idSitio, array $datos, array $idsBarrios): bool
    {
        $this->conexion->beginTransaction();
        try {
            $datos[':id'] = $idSitio;
            $stmt = $this->conexion->prepare(
                'UPDATE sitio_terreno
                 SET direccion = :direccion, latitud = :latitud, longitud = :longitud
                 WHERE id_sitio = :id'
            );
            $stmt->execute($datos);

            $stmtBorrar = $this->conexion->prepare('DELETE FROM sitio_barrio WHERE id_sitio = :id');
            $stmtBorrar->execute([':id' => $idSitio]);

            $this->vincularBarrios($idSitio, $idsBarrios);

            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    private function vincularBarrios(int $idSitio, array $idsBarrios): void
    {
        if (empty($idsBarrios)) {
            return;
        }
        $stmt = $this->conexion->prepare(
            'INSERT INTO sitio_barrio (id_sitio, id_barrio) VALUES (:id_sitio, :id_barrio)'
        );
        foreach ($idsBarrios as $idBarrio) {
            $stmt->execute([':id_sitio' => $idSitio, ':id_barrio' => (int) $idBarrio]);
        }
    }

    public function cambiarEstado(int $idSitio, bool $activo): bool
    {
        $stmt = $this->conexion->prepare('UPDATE sitio_terreno SET activo = :activo WHERE id_sitio = :id');
        return $stmt->execute([':activo' => $activo, ':id' => $idSitio]);
    }
}
