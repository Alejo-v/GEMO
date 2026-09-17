<?php

require_once __DIR__ . '/../config/database.php';

class Permiso
{
    private PDO $conexion;

    




    private const ROL_EXCLUIDO = 6;

    public function __construct()
    {
        $this->conexion = (new Database())->conectar();
    }

    




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

    



    public function contarActivosPorRol(int $idRol): int
    {
        $stmt = $this->conexion->prepare(
            'SELECT COUNT(*) FROM permiso_rol WHERE id_rol = :id_rol AND activo = TRUE'
        );
        $stmt->execute([':id_rol' => $idRol]);
        return (int) $stmt->fetchColumn();
    }

    




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
