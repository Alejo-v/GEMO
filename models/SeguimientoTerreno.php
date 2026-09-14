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
     * Inserta un nuevo registro de seguimiento de terreno.
     * Al utilizar la secuencia nativa de PostgreSQL, no se requiere calcular el MAX() manualmente.
     */
    public function registrar(array $datos): bool
    {
        $sql = 'INSERT INTO seguimiento_terreno
                (id_deposito, id_usuario, fecha, id_actividad_terreno, ph, temperatura, larvas_aedes, pupas, larvas_culex)
                VALUES
                (:id_deposito, :id_usuario, :fecha, :id_actividad_terreno, :ph, :temperatura, :larvas_aedes, :pupas, :larvas_culex)';
        
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            ':id_deposito'          => $datos['id_deposito'],
            ':id_usuario'           => $datos['id_usuario'],
            ':fecha'                => $datos['fecha'],
            ':id_actividad_terreno' => $datos['id_actividad_terreno'],
            ':ph'                   => $datos['ph'],
            ':temperatura'          => $datos['temperatura'],
            ':larvas_aedes'         => $datos['larvas_aedes'],
            ':pupas'                => $datos['pupas'],
            ':larvas_culex'         => $datos['larvas_culex']
        ]);
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
}