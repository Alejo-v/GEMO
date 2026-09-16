<?php

require_once __DIR__ . '/../config/database.php';

class Permiso
{
    private PDO $conexion;

    /**
     * El rol 6 (Super Administrador) queda fuera del panel visual: no se
     * administra a sí mismo desde aquí para evitar que se bloquee el
     * acceso a su propia sesión.
     */
    private const ROL_EXCLUIDO = 6;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    /**
     * Devuelve todos los permisos (roles 1-5) agrupados por rol, listos
     * para pintar el panel: cada elemento del arreglo tiene id_rol,
     * nombre_rol y la lista de páginas con su estado activo/inactivo.
     */
    public function obtenerAgrupadoPorRol(): array
    {
        $sql = 'SELECT p.id_permiso, p.id_rol, p.pagina, p.etiqueta, p.activo, r.nombre_rol
                FROM permiso_rol p
                INNER JOIN rol r ON r.id_rol = p.id_rol
                WHERE p.id_rol <> :rol_excluido
                ORDER BY p.id_rol, p.etiqueta';
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([':rol_excluido' => self::ROL_EXCLUIDO]);
        $filas = $stmt->fetchAll();

        $agrupado = [];
        foreach ($filas as $fila) {
            $idRol = (int) $fila['id_rol'];
            if (!isset($agrupado[$idRol])) {
                $agrupado[$idRol] = [
                    'id_rol' => $idRol,
                    'nombre_rol' => $fila['nombre_rol'],
                    'permisos' => [],
                ];
            }
            $agrupado[$idRol]['permisos'][] = [
                'id_permiso' => (int) $fila['id_permiso'],
                'pagina' => $fila['pagina'],
                'etiqueta' => $fila['etiqueta'],
                'activo' => (bool) $fila['activo'],
            ];
        }

        return $agrupado;
    }

    /**
     * Cuenta cuántos permisos activos tiene un rol. Se usa para no
     * permitir dejar un rol sin ninguna página habilitada.
     */
    public function contarActivosPorRol(int $idRol): int
    {
        $stmt = $this->conexion->prepare(
            'SELECT COUNT(*) FROM permiso_rol WHERE id_rol = :id_rol AND activo = TRUE'
        );
        $stmt->execute([':id_rol' => $idRol]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Devuelve el id_rol dueño de un id_permiso, o null si no existe.
     * Se usa para validar que el permiso pertenece a un rol administrable
     * (no al rol 6) antes de modificarlo.
     */
    public function obtenerRolDelPermiso(int $idPermiso): ?int
    {
        $stmt = $this->conexion->prepare(
            'SELECT id_rol FROM permiso_rol WHERE id_permiso = :id_permiso'
        );
        $stmt->execute([':id_permiso' => $idPermiso]);
        $idRol = $stmt->fetchColumn();
        return $idRol !== false ? (int) $idRol : null;
    }

    public function esRolAdministrable(int $idRol): bool
    {
        return $idRol !== self::ROL_EXCLUIDO;
    }

    public function actualizarEstado(int $idPermiso, bool $activo): bool
    {
        $stmt = $this->conexion->prepare(
            'UPDATE permiso_rol SET activo = :activo WHERE id_permiso = :id_permiso'
        );
        return $stmt->execute([':activo' => $activo, ':id_permiso' => $idPermiso]);
    }
}
