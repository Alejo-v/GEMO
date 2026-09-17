<?php

require_once __DIR__ . '/../config/database.php';

class Zoocriadero
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT z.id_zoocriadero, z.direccion, z.activo,
                       z.id_barrio, b.nombre AS barrio,
                       z.id_usuario, TRIM(CONCAT(u.nombres, ' ', u.apellidos)) AS encargado
                FROM zoocriadero z
                INNER JOIN barrio b ON b.id_barrio = z.id_barrio
                INNER JOIN usuario u ON u.id_usuario = z.id_usuario
                ORDER BY z.id_zoocriadero DESC";
        return $this->conexion->query($sql)->fetchAll();
    }

    /** Solo los activos, para el <select> del formulario de Tanques. */
    public function obtenerActivos(): array
    {
        $sql = 'SELECT id_zoocriadero, direccion FROM zoocriadero WHERE activo = TRUE ORDER BY id_zoocriadero';
        return $this->conexion->query($sql)->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_zoocriadero, direccion, id_usuario, id_barrio, activo FROM zoocriadero WHERE id_zoocriadero = :id'
        );
        $stmt->execute([':id' => $id]);
        $zoocriadero = $stmt->fetch();
        return $zoocriadero ?: null;
    }

    /** Catálogo de barrios (con su comuna) para el <select> del formulario. */
    public function obtenerBarrios(): array
    {
        $sql = 'SELECT b.id_barrio, b.nombre, c.nombre AS comuna
                FROM barrio b
                INNER JOIN comuna c ON c.id_comuna = b.id_comuna
                ORDER BY c.nombre, b.nombre';
        return $this->conexion->query($sql)->fetchAll();
    }

    /** Usuarios que pueden quedar como encargados (Coordinador y Auxiliar de Zoocriadero). */
    public function obtenerEncargados(): array
    {
        $sql = "SELECT id_usuario, TRIM(CONCAT(nombres, ' ', apellidos)) AS nombre_completo
                FROM usuario
                WHERE id_rol IN (2, 3)
                ORDER BY nombre_completo";
        return $this->conexion->query($sql)->fetchAll();
    }

    public function barrioExiste(int $idBarrio): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM barrio WHERE id_barrio = :id LIMIT 1');
        $stmt->execute([':id' => $idBarrio]);
        return (bool) $stmt->fetchColumn();
    }

    public function usuarioExiste(int $idUsuario): bool
    {
        $stmt = $this->conexion->prepare('SELECT 1 FROM usuario WHERE id_usuario = :id LIMIT 1');
        $stmt->execute([':id' => $idUsuario]);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(array $datos): int
    {
        $stmt = $this->conexion->prepare(
            'INSERT INTO zoocriadero (direccion, id_usuario, id_barrio)
             VALUES (:direccion, :id_usuario, :id_barrio) RETURNING id_zoocriadero'
        );
        $stmt->execute($datos);
        return (int) $stmt->fetchColumn();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $datos[':id'] = $id;
        $stmt = $this->conexion->prepare(
            'UPDATE zoocriadero SET direccion = :direccion, id_usuario = :id_usuario, id_barrio = :id_barrio
             WHERE id_zoocriadero = :id'
        );
        return $stmt->execute($datos);
    }

    public function cambiarEstado(int $id, bool $activo): bool
    {
        $stmt = $this->conexion->prepare('UPDATE zoocriadero SET activo = :activo WHERE id_zoocriadero = :id');
        return $stmt->execute([':activo' => $activo, ':id' => $id]);
    }
}
