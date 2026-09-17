<?php

require_once __DIR__ . '/../config/database.php';

class ReporteTerreno
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    

    public function obtenerComunas(): array
    {
        return $this->conexion->query('SELECT id_comuna, nombre FROM comuna ORDER BY nombre')->fetchAll();
    }

    public function obtenerBarrios(): array
    {
        return $this->conexion->query('SELECT id_barrio, nombre, id_comuna FROM barrio ORDER BY nombre')->fetchAll();
    }

    public function obtenerTiposDeposito(): array
    {
        return $this->conexion->query(
            'SELECT id_tipo_deposito, descripcion FROM tipo_deposito ORDER BY id_tipo_deposito'
        )->fetchAll();
    }

    





    private function condicionesFiltro(array $filtros): array
    {
        $condiciones = [];
        $params = [];

        if (!empty($filtros['id_comuna'])) {
            $condiciones[] = 'EXISTS (
                SELECT 1 FROM sitio_barrio sb
                INNER JOIN barrio b ON b.id_barrio = sb.id_barrio
                WHERE sb.id_sitio = st.id_sitio AND b.id_comuna = :id_comuna
            )';
            $params[':id_comuna'] = (int) $filtros['id_comuna'];
        }

        if (!empty($filtros['id_barrio'])) {
            $condiciones[] = 'EXISTS (
                SELECT 1 FROM sitio_barrio sb
                WHERE sb.id_sitio = st.id_sitio AND sb.id_barrio = :id_barrio
            )';
            $params[':id_barrio'] = (int) $filtros['id_barrio'];
        }

        if (!empty($filtros['id_tipo_deposito'])) {
            $condiciones[] = 'td.id_tipo_deposito = :id_tipo_deposito';
            $params[':id_tipo_deposito'] = (int) $filtros['id_tipo_deposito'];
        }

        if (!empty($filtros['fecha_desde'])) {
            $condiciones[] = 's.fecha >= :fecha_desde';
            $params[':fecha_desde'] = $filtros['fecha_desde'];
        }

        if (!empty($filtros['fecha_hasta'])) {
            $condiciones[] = 's.fecha <= :fecha_hasta';
            $params[':fecha_hasta'] = $filtros['fecha_hasta'];
        }

        $where = $condiciones ? ('WHERE ' . implode(' AND ', $condiciones)) : '';
        return [$where, $params];
    }

    
    public function reporteSitios(array $filtros): array
    {
        [$where, $params] = $this->condicionesFiltro($filtros);

        $sql = "SELECT s.id_seguimiento_terreno, s.fecha,
                       st.direccion,
                       (SELECT string_agg(DISTINCT b.nombre, ', ' ORDER BY b.nombre)
                        FROM sitio_barrio sb INNER JOIN barrio b ON b.id_barrio = sb.id_barrio
                        WHERE sb.id_sitio = st.id_sitio) AS barrios,
                       (SELECT string_agg(DISTINCT c.nombre, ', ' ORDER BY c.nombre)
                        FROM sitio_barrio sb
                        INNER JOIN barrio b ON b.id_barrio = sb.id_barrio
                        INNER JOIN comuna c ON c.id_comuna = b.id_comuna
                        WHERE sb.id_sitio = st.id_sitio) AS comunas,
                       td.descripcion AS tipo_deposito,
                       at2.nombre AS actividad,
                       s.larvas_aedes, s.pupas, s.larvas_culex,
                       (u.nombres || ' ' || u.apellidos) AS auxiliar
                FROM seguimiento_terreno s
                INNER JOIN deposito d ON d.id_deposito = s.id_deposito
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                INNER JOIN sitio_terreno st ON st.id_sitio = d.id_sitio
                INNER JOIN actividad_terreno at2 ON at2.id_actividad_terreno = s.id_actividad_terreno
                INNER JOIN usuario u ON u.id_usuario = s.id_usuario
                $where
                ORDER BY s.fecha DESC, s.id_seguimiento_terreno DESC";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    
    public function reportePorActividad(array $filtros): array
    {
        [$where, $params] = $this->condicionesFiltro($filtros);

        $sql = "SELECT at2.nombre AS etiqueta, COUNT(*) AS total
                FROM seguimiento_terreno s
                INNER JOIN deposito d ON d.id_deposito = s.id_deposito
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                INNER JOIN sitio_terreno st ON st.id_sitio = d.id_sitio
                INNER JOIN actividad_terreno at2 ON at2.id_actividad_terreno = s.id_actividad_terreno
                $where
                GROUP BY at2.nombre
                ORDER BY at2.nombre";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    
    public function reportePorAuxiliar(array $filtros): array
    {
        [$where, $params] = $this->condicionesFiltro($filtros);

        $sql = "SELECT u.id_usuario, (u.nombres || ' ' || u.apellidos) AS auxiliar,
                       at2.nombre AS actividad, COUNT(*) AS total
                FROM seguimiento_terreno s
                INNER JOIN deposito d ON d.id_deposito = s.id_deposito
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                INNER JOIN sitio_terreno st ON st.id_sitio = d.id_sitio
                INNER JOIN actividad_terreno at2 ON at2.id_actividad_terreno = s.id_actividad_terreno
                INNER JOIN usuario u ON u.id_usuario = s.id_usuario
                $where
                GROUP BY u.id_usuario, auxiliar, at2.nombre
                ORDER BY auxiliar, at2.nombre";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll();

        
        $porAuxiliar = [];
        foreach ($filas as $fila) {
            $id = (int) $fila['id_usuario'];
            if (!isset($porAuxiliar[$id])) {
                $porAuxiliar[$id] = ['auxiliar' => $fila['auxiliar'], 'total' => 0, 'actividades' => []];
            }
            $porAuxiliar[$id]['total'] += (int) $fila['total'];
            $porAuxiliar[$id]['actividades'][] = $fila['actividad'] . ': ' . $fila['total'];
        }

        return array_values($porAuxiliar);
    }

    
    public function reportePorTipoDeposito(array $filtros): array
    {
        [$where, $params] = $this->condicionesFiltro($filtros);

        $sql = "SELECT td.descripcion AS etiqueta, COUNT(*) AS total
                FROM seguimiento_terreno s
                INNER JOIN deposito d ON d.id_deposito = s.id_deposito
                INNER JOIN tipo_deposito td ON td.id_tipo_deposito = d.id_tipo_deposito
                INNER JOIN sitio_terreno st ON st.id_sitio = d.id_sitio
                $where
                GROUP BY td.descripcion
                ORDER BY td.descripcion";

        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
