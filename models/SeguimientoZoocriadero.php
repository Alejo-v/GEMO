<?php

require_once __DIR__ . '/../config/database.php';

class SeguimientoZoocriadero
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    
    public function obtenerTanques(): array
    {
        $sql = "SELECT t.id_tanque, t.estado, tt.id_tipo_tanque, tt.descripcion AS tipo_tanque
                FROM tanque t
                INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
                WHERE t.activo = TRUE AND t.estado = 'Activo'
                ORDER BY t.id_tanque";
        return $this->conexion->query($sql)->fetchAll();
    }

    
    public function obtenerActividades(): array
    {
        return $this->conexion->query(
            'SELECT id_actividad_zoocriadero, nombre FROM actividad_zoocriadero WHERE activo = TRUE ORDER BY id_actividad_zoocriadero'
        )->fetchAll();
    }

    public function tanqueExiste(int $idTanque): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM tanque WHERE id_tanque = :id LIMIT 1');
        $stmt->execute([':id' => $idTanque]);
        return (bool) $stmt->fetchColumn();
    }

    public function actividadesValidas(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }
        $ids = array_values(array_unique(array_map('intval', $ids)));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->conexion->prepare(
            "SELECT COUNT(*) FROM actividad_zoocriadero WHERE id_actividad_zoocriadero IN ($placeholders)"
        );
        $stmt->execute($ids);
        return (int) $stmt->fetchColumn() === count($ids);
    }

    public function registrar(array $datos, array $actividades): bool
    {
        $this->conexion->beginTransaction();
        try {
            $sql = 'INSERT INTO seguimiento_zoocriadero
                    (id_tanque, id_usuario, fecha, ph, temperatura, cloro,
                     alevines_nacidos, muertos_macho, muertos_hembra, observaciones)
                    VALUES
                    (:id_tanque, :id_usuario, :fecha, :ph, :temperatura, :cloro,
                     :alevines_nacidos, :muertos_macho, :muertos_hembra, :observaciones)
                    RETURNING id_seguimiento_zoo';
            $stmt = $this->conexion->prepare($sql);
            $stmt->execute($datos);
            $idSeguimiento = (int) $stmt->fetchColumn();

            if (!empty($actividades)) {
                $stmtAct = $this->conexion->prepare(
                    'INSERT INTO seguimiento_zoo_actividad (id_seguimiento_zoo, id_actividad_zoocriadero)
                     VALUES (:id_seguimiento_zoo, :id_actividad)'
                );
                foreach (array_unique(array_map('intval', $actividades)) as $idActividad) {
                    $stmtAct->execute([
                        ':id_seguimiento_zoo' => $idSeguimiento,
                        ':id_actividad' => $idActividad,
                    ]);
                }
            }

            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    
    public function obtenerZoocriaderos(): array
    {
        return $this->conexion->query(
            'SELECT id_zoocriadero, direccion FROM zoocriadero ORDER BY id_zoocriadero'
        )->fetchAll();
    }

    






    private function condicionesFiltro(array $filtros): array
    {
        $condiciones = [];
        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $condiciones[] = 's.fecha >= :fecha_inicio';
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }

        if (!empty($filtros['fecha_fin'])) {
            $condiciones[] = 's.fecha <= :fecha_fin';
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }

        if (!empty($filtros['id_zoocriadero'])) {
            $condiciones[] = 't.id_zoocriadero = :id_zoocriadero';
            $params[':id_zoocriadero'] = (int) $filtros['id_zoocriadero'];
        }

        if (!empty($filtros['id_actividad'])) {
            $condiciones[] = 'EXISTS (
                SELECT 1 FROM seguimiento_zoo_actividad sa
                WHERE sa.id_seguimiento_zoo = s.id_seguimiento_zoo
                  AND sa.id_actividad_zoocriadero = :id_actividad
            )';
            $params[':id_actividad'] = (int) $filtros['id_actividad'];
        }

        $where = $condiciones ? ('WHERE ' . implode(' AND ', $condiciones)) : '';
        return [$where, $params];
    }

    



    public function obtenerRegistrosFiltrados(array $filtros): array
    {
        [$where, $params] = $this->condicionesFiltro($filtros);

        $sql = "SELECT s.id_seguimiento_zoo, s.fecha, s.id_tanque,
                       z.id_zoocriadero, z.direccion AS zoocriadero,
                       tt.descripcion AS tipo_tanque,
                       s.ph, s.temperatura, s.cloro, s.alevines_nacidos,
                       s.muertos_macho, s.muertos_hembra,
                       (s.muertos_macho + s.muertos_hembra) AS muertos_total,
                       s.observaciones,
                       TRIM(CONCAT(u.nombres, ' ', u.apellidos)) AS registrado_por,
                       COALESCE((
                           SELECT string_agg(az.nombre, ', ' ORDER BY az.nombre)
                           FROM seguimiento_zoo_actividad sa
                           INNER JOIN actividad_zoocriadero az ON az.id_actividad_zoocriadero = sa.id_actividad_zoocriadero
                           WHERE sa.id_seguimiento_zoo = s.id_seguimiento_zoo
                       ), '—') AS actividades
                FROM seguimiento_zoocriadero s
                INNER JOIN tanque t ON t.id_tanque = s.id_tanque
                INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
                INNER JOIN zoocriadero z ON z.id_zoocriadero = t.id_zoocriadero
                INNER JOIN usuario u ON u.id_usuario = s.id_usuario
                $where
                ORDER BY s.fecha DESC, s.id_seguimiento_zoo DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    



    public function obtenerResumenFiltrado(array $filtros): array
    {
        [$where, $params] = $this->condicionesFiltro($filtros);

        $sql = "SELECT COUNT(*) AS total_registros,
                       COALESCE(SUM(s.alevines_nacidos), 0) AS total_vivos,
                       COALESCE(SUM(s.muertos_macho), 0) AS total_muertos_macho,
                       COALESCE(SUM(s.muertos_hembra), 0) AS total_muertos_hembra,
                       COALESCE(SUM(s.muertos_macho + s.muertos_hembra), 0) AS total_muertos
                FROM seguimiento_zoocriadero s
                INNER JOIN tanque t ON t.id_tanque = s.id_tanque
                $where";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetch();
        return $resultado ?: [
            'total_registros' => 0,
            'total_vivos' => 0,
            'total_muertos_macho' => 0,
            'total_muertos_hembra' => 0,
            'total_muertos' => 0,
        ];
    }

    



    public function obtenerSerieDiariaFiltrada(array $filtros): array
    {
        [$where, $params] = $this->condicionesFiltro($filtros);

        $sql = "SELECT s.fecha,
                       COALESCE(SUM(s.alevines_nacidos), 0) AS vivos,
                       COALESCE(SUM(s.muertos_macho + s.muertos_hembra), 0) AS muertos
                FROM seguimiento_zoocriadero s
                INNER JOIN tanque t ON t.id_tanque = s.id_tanque
                $where
                GROUP BY s.fecha
                ORDER BY s.fecha";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    



    public function obtenerResumenPorTanque(array $filtros): array
    {
        [$where, $params] = $this->condicionesFiltro($filtros);

        $sql = "SELECT t.id_tanque, z.id_zoocriadero, z.direccion AS zoocriadero,
                       tt.descripcion AS tipo_tanque,
                       COUNT(s.id_seguimiento_zoo) AS total_registros,
                       COALESCE(SUM(s.alevines_nacidos), 0) AS total_vivos,
                       COALESCE(SUM(s.muertos_macho), 0) AS total_muertos_macho,
                       COALESCE(SUM(s.muertos_hembra), 0) AS total_muertos_hembra,
                       COALESCE(SUM(s.muertos_macho + s.muertos_hembra), 0) AS total_muertos
                FROM seguimiento_zoocriadero s
                INNER JOIN tanque t ON t.id_tanque = s.id_tanque
                INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
                INNER JOIN zoocriadero z ON z.id_zoocriadero = t.id_zoocriadero
                $where
                GROUP BY t.id_tanque, z.id_zoocriadero, z.direccion, tt.descripcion
                ORDER BY z.id_zoocriadero, t.id_tanque";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerRegistrosPorUsuario(int $idUsuario): array
    {
        $sql = "SELECT s.id_seguimiento_zoo, s.fecha, s.id_tanque, tt.descripcion AS tipo_tanque,
                       s.ph, s.temperatura, s.cloro, s.alevines_nacidos,
                       s.muertos_macho, s.muertos_hembra, s.observaciones,
                       COALESCE(string_agg(az.nombre, ', ' ORDER BY az.nombre), '—') AS actividades
                FROM seguimiento_zoocriadero s
                INNER JOIN tanque t ON t.id_tanque = s.id_tanque
                INNER JOIN tipo_tanque tt ON tt.id_tipo_tanque = t.id_tipo_tanque
                LEFT JOIN seguimiento_zoo_actividad sa ON sa.id_seguimiento_zoo = s.id_seguimiento_zoo
                LEFT JOIN actividad_zoocriadero az ON az.id_actividad_zoocriadero = sa.id_actividad_zoocriadero
                WHERE s.id_usuario = :id_usuario
                GROUP BY s.id_seguimiento_zoo, s.fecha, s.id_tanque, tt.descripcion, s.ph, s.temperatura,
                         s.cloro, s.alevines_nacidos, s.muertos_macho, s.muertos_hembra, s.observaciones
                ORDER BY s.fecha DESC, s.id_seguimiento_zoo DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }
}
