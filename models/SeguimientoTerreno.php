<?php

require_once __DIR__ . '/../config/database.php';

class SeguimientoTerreno
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerSitios(): array
    {
        $sql = "SELECT st.id_sitio, st.direccion,
                       COALESCE(string_agg(DISTINCT b.nombre, ', ' ORDER BY b.nombre), 'Sin barrio') AS barrios,
                       COALESCE(string_agg(DISTINCT c.nombre, ', ' ORDER BY c.nombre), 'Sin comuna') AS comunas
                FROM sitio_terreno st
                LEFT JOIN sitio_barrio sb ON sb.id_sitio = st.id_sitio
                LEFT JOIN barrio b ON b.id_barrio = sb.id_barrio
                LEFT JOIN comuna c ON c.id_comuna = b.id_comuna
                WHERE st.activo = TRUE
                GROUP BY st.id_sitio, st.direccion
                ORDER BY st.id_sitio";
        return $this->conexion->query($sql)->fetchAll();
    }

    public function obtenerDepositos(): array
    {
        $sql = 'SELECT d.id_deposito, d.id_sitio, d.id_tipo_deposito, td.descripcion AS tipo_deposito
                FROM deposito d
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                WHERE d.activo = TRUE
                ORDER BY d.id_sitio, d.id_deposito';
        return $this->conexion->query($sql)->fetchAll();
    }

    public function obtenerActividades(): array
    {
        return $this->conexion->query(
            'SELECT id_actividad_terreno, nombre FROM actividad_terreno WHERE activo = TRUE ORDER BY id_actividad_terreno'
        )->fetchAll();
    }

    public function depositoExiste(int $idDeposito): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM deposito WHERE id_deposito = :id LIMIT 1');
        $stmt->execute([':id' => $idDeposito]);
        return (bool) $stmt->fetchColumn();
    }

    public function actividadExiste(int $idActividad): bool
    {
        $stmt = $this->conexion->prepare(
            'SELECT 1 FROM actividad_terreno WHERE id_actividad_terreno = :id LIMIT 1'
        );
        $stmt->execute([':id' => $idActividad]);
        return (bool) $stmt->fetchColumn();
    }

    /**
     * id_seguimiento_terreno NO es autoincremental en la base de datos actual
     * (a diferencia de id_seguimiento_zoo, que sí tiene IDENTITY). Por eso
     * calculamos el siguiente número nosotros mismos dentro de una
     * transacción. Si dos auxiliares guardan un registro exactamente al
     * mismo tiempo puede haber choque de id (error 23505 = unique_violation);
     * en ese caso reintentamos automáticamente hasta 3 veces con un id nuevo.
     */
    public function registrar(array $datos): bool
    {
        $intentos = 0;

        while ($intentos < 3) {
            $intentos++;
            $this->conexion->beginTransaction();
            try {
                $siguienteId = (int) $this->conexion
                    ->query('SELECT COALESCE(MAX(id_seguimiento_terreno), 0) + 1 FROM seguimiento_terreno')
                    ->fetchColumn();

                $datos[':id_seguimiento_terreno'] = $siguienteId;

                $sql = 'INSERT INTO seguimiento_terreno
                        (id_seguimiento_terreno, id_deposito, id_usuario, fecha, id_actividad_terreno,
                         ph, temperatura, larvas_aedes, pupas, larvas_culex)
                        VALUES
                        (:id_seguimiento_terreno, :id_deposito, :id_usuario, :fecha, :id_actividad_terreno,
                         :ph, :temperatura, :larvas_aedes, :pupas, :larvas_culex)';
                $stmt = $this->conexion->prepare($sql);
                $stmt->execute($datos);

                $this->conexion->commit();
                return true;
            } catch (PDOException $e) {
                $this->conexion->rollBack();
                if ($e->getCode() === '23505' && $intentos < 3) {
                    continue;
                }
                throw $e;
            }
        }

        return false;
    }

    public function obtenerRegistrosPorUsuario(int $idUsuario): array
    {
        $sql = "SELECT s.id_seguimiento_terreno, s.fecha, s.ph, s.temperatura,
                       s.larvas_aedes, s.pupas, s.larvas_culex,
                       at2.nombre AS actividad,
                       td.descripcion AS tipo_deposito,
                       st.direccion AS sitio_direccion
                FROM seguimiento_terreno s
                INNER JOIN deposito d ON d.id_deposito = s.id_deposito
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                INNER JOIN sitio_terreno st ON st.id_sitio = d.id_sitio
                INNER JOIN actividad_terreno at2 ON at2.id_actividad_terreno = s.id_actividad_terreno
                WHERE s.id_usuario = :id_usuario
                ORDER BY s.fecha DESC, s.id_seguimiento_terreno DESC";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }

    public function obtenerConteoPorTipoDeposito(int $idUsuario): array
    {
        $sql = "SELECT td.descripcion AS etiqueta, COUNT(*) AS total
                FROM seguimiento_terreno s
                INNER JOIN deposito d ON d.id_deposito = s.id_deposito
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                WHERE s.id_usuario = :id_usuario
                GROUP BY td.descripcion
                ORDER BY td.descripcion";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }

    public function obtenerConteoPorActividad(int $idUsuario): array
    {
        $sql = "SELECT at2.nombre AS etiqueta, COUNT(*) AS total
                FROM seguimiento_terreno s
                INNER JOIN actividad_terreno at2 ON at2.id_actividad_terreno = s.id_actividad_terreno
                WHERE s.id_usuario = :id_usuario
                GROUP BY at2.nombre
                ORDER BY at2.nombre";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':id_usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }
    /**
     * Historial de auditoría de registros de terreno.
     * Permite filtrar por rango de fechas, usuario y actividad.
     */
    public function obtenerAuditoriaTerreno(?string $fechaDesde = null, ?string $fechaHasta = null, ?int $idUsuario = null, ?int $idActividad = null): array
    {
        $condiciones = [];
        $params = [];

        if ($fechaDesde !== null && $fechaDesde !== '') {
            $condiciones[] = 's.fecha >= :fecha_desde';
            $params[':fecha_desde'] = $fechaDesde;
        }

        if ($fechaHasta !== null && $fechaHasta !== '') {
            $condiciones[] = 's.fecha <= :fecha_hasta';
            $params[':fecha_hasta'] = $fechaHasta;
        }

        if ($idUsuario !== null && $idUsuario > 0) {
            $condiciones[] = 's.id_usuario = :id_usuario';
            $params[':id_usuario'] = $idUsuario;
        }

        if ($idActividad !== null && $idActividad > 0) {
            $condiciones[] = 's.id_actividad_terreno = :id_actividad';
            $params[':id_actividad'] = $idActividad;
        }

        $where = $condiciones ? 'WHERE ' . implode(' AND ', $condiciones) : '';

        $sql = "SELECT s.id_seguimiento_terreno,
                       s.fecha,
                       u.id_usuario,
                       u.nombres || ' ' || u.apellidos AS usuario,
                       u.correo,
                       u.telefono,
                       r.nombre_rol,
                       at2.id_actividad_terreno,
                       at2.nombre AS actividad,
                       st.direccion AS sitio,
                       d.id_deposito,
                       td.descripcion AS tipo_deposito,
                       s.ph,
                       s.temperatura,
                       s.larvas_aedes,
                       s.pupas,
                       s.larvas_culex
                FROM seguimiento_terreno s
                INNER JOIN usuario u ON u.id_usuario = s.id_usuario
                INNER JOIN rol r ON r.id_rol = u.id_rol
                INNER JOIN actividad_terreno at2 ON at2.id_actividad_terreno = s.id_actividad_terreno
                INNER JOIN deposito d ON d.id_deposito = s.id_deposito
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                INNER JOIN sitio_terreno st ON st.id_sitio = d.id_sitio
                $where
                ORDER BY s.fecha DESC, s.id_seguimiento_terreno DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function obtenerUsuariosConRegistrosTerreno(): array
    {
        $sql = "SELECT DISTINCT u.id_usuario,
                       u.nombres || ' ' || u.apellidos AS nombre
                FROM usuario u
                INNER JOIN seguimiento_terreno s ON s.id_usuario = u.id_usuario
                ORDER BY nombre";
        return $this->conexion->query($sql)->fetchAll();
    }

}
