<?php

require_once __DIR__ . '/../config/database.php';

class Tanque
{
    /** Estados operativos permitidos para un tanque. */
    public const ESTADOS = ['Activo', 'Mantenimiento', 'Fuera de servicio'];

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerTodos(): array
    {
        $sql = 'SELECT t.id_tanque, t.id_zoocriadero, t.id_tipo_tanque, t.estado, t.activo,
                       z.direccion AS zoocriadero, tt.descripcion AS tipo_tanque
                FROM tanque t
                INNER JOIN zoocriadero z ON z.id_zoocriadero = t.id_zoocriadero
                INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
                ORDER BY t.id_tanque DESC';
        return $this->conexion->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_tanque, id_zoocriadero, id_tipo_tanque, estado, activo FROM tanque WHERE id_tanque = :id'
        );
        $stmt->execute([':id' => $id]);
        $tanque = $stmt->fetch();
        return $tanque ?: null;
    }

    public function zoocriaderoExiste(int $idZoocriadero): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM zoocriadero WHERE id_zoocriadero = :id LIMIT 1');
        $stmt->execute([':id' => $idZoocriadero]);
        return (bool) $stmt->fetchColumn();
    }

    public function tipoTanqueExiste(int $idTipoTanque): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM tipo_tanque WHERE id_tipo_tanque = :id LIMIT 1');
        $stmt->execute([':id' => $idTipoTanque]);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO tanque (id_zoocriadero, id_tipo_tanque, estado)
             VALUES (:id_zoocriadero, :id_tipo_tanque, :estado) RETURNING id_tanque'
        );
        $stmt->execute($datos);
        return (int) $stmt->fetchColumn();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $datos[':id'] = $id;
        $stmt = $this->conexion->prepare(
            'UPDATE tanque SET id_zoocriadero = :id_zoocriadero, id_tipo_tanque = :id_tipo_tanque, estado = :estado
             WHERE id_tanque = :id'
        );
        return $stmt->execute($datos);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->conexion->prepare('UPDATE tanque SET activo = :activo WHERE id_tanque = :id');
        return $stmt->execute([':activo' => $activo, ':id' => $id]);
    }

    /**
     * Reporte 3: tanques agrupados por zoocriadero, con la cantidad total,
     * el desglose por tipo de tanque y el encargado de cada zoocriadero.
     * Es un reporte estructural (inventario), por eso no lleva filtro de fechas.
     */
    public function obtenerReportePorZoocriadero($idZoocriadero = null): array
    {
        // El reporte 3 vive en la pantalla de reportes del zoocriadero, que
        // tiene un filtro "Zoocriadero". Si viene ese filtro, se aplica aquí
        // también para que la tabla sea coherente con los otros dos reportes.
        $filtrarPorZoo = ($idZoocriadero !== null && $idZoocriadero !== '');
        $params = $filtrarPorZoo ? [':id_zoocriadero' => (int) $idZoocriadero] : [];
        $whereZoo = $filtrarPorZoo ? 'WHERE z.id_zoocriadero = :id_zoocriadero' : '';
        $andTipos = $filtrarPorZoo ? 'AND t.id_zoocriadero = :id_zoocriadero' : '';

        $sqlZoocriaderos = "SELECT z.id_zoocriadero, z.direccion, z.activo,
                                   TRIM(CONCAT(u.nombres, ' ', u.apellidos)) AS encargado,
                                   COUNT(t.id_tanque) AS total_tanques,
                                   COUNT(t.id_tanque) FILTER (WHERE t.estado = 'Activo') AS tanques_activos,
                                   COUNT(t.id_tanque) FILTER (WHERE t.estado = 'Mantenimiento') AS tanques_mantenimiento,
                                   COUNT(t.id_tanque) FILTER (WHERE t.estado = 'Fuera de servicio') AS tanques_fuera_servicio
                            FROM zoocriadero z
                            INNER JOIN usuario u ON u.id_usuario = z.id_usuario
                            LEFT JOIN tanque t ON t.id_zoocriadero = z.id_zoocriadero AND t.activo = TRUE
                            $whereZoo
                            GROUP BY z.id_zoocriadero, z.direccion, z.activo, encargado
                            ORDER BY z.id_zoocriadero";
        $stmtZoo = $this->conexion->prepare($sqlZoocriaderos);
        $stmtZoo->execute($params);
        $zoocriaderos = $stmtZoo->fetchAll();

        $sqlTipos = "SELECT t.id_zoocriadero, tt.descripcion AS tipo_tanque, COUNT(*) AS cantidad
                     FROM tanque t
                     INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
                     WHERE t.activo = TRUE
                     $andTipos
                     GROUP BY t.id_zoocriadero, tt.descripcion
                     ORDER BY t.id_zoocriadero, tt.descripcion";
        $stmtTipos = $this->conexion->prepare($sqlTipos);
        $stmtTipos->execute($params);
        $tipos = $stmtTipos->fetchAll();

        $tiposPorZoocriadero = [];
        foreach ($tipos as $fila) {
            $id = (int) $fila['id_zoocriadero'];
            $tiposPorZoocriadero[$id][] = $fila['tipo_tanque'] . ': ' . $fila['cantidad'];
        }

        foreach ($zoocriaderos as &$zoo) {
            $id = (int) $zoo['id_zoocriadero'];
            $zoo['detalle_tipos'] = $tiposPorZoocriadero[$id] ?? [];
        }
        unset($zoo);

        return $zoocriaderos;
    }
}
