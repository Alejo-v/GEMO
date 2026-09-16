<?php

require_once __DIR__ . '/../config/database.php';

/**
 * Indica si el rol dado tiene permiso para abrir la página indicada.
 *
 * Los permisos se consultan desde la tabla `permiso_rol` (ver
 * sql/alter_permisos.sql), que el Super Administrador puede editar
 * visualmente desde views/super_admin/permisos.php.
 *
 * Si la tabla aún no existe (migración no aplicada en este ambiente) o
 * la BD no responde, no se bloquea el acceso: el chequeo de rol que ya
 * hace cada *_auth.php sigue aplicando igual, este es un filtro
 * adicional, no el único candado.
 *
 * @param int    $idRol   id_rol de la sesión ($_SESSION['usuario_rol_id'])
 * @param string $pagina  nombre del archivo actual, ej: basename($_SERVER['PHP_SELF'])
 */
function usuarioTienePermiso($idRol, $pagina)
{
    static $cache = [];

    $idRol = (int) $idRol;

    if (!array_key_exists($idRol, $cache)) {
        try {
            $pdo = (new Database())->conectar();
            $stmt = $pdo->prepare(
                'SELECT pagina FROM permiso_rol WHERE id_rol = :id_rol AND activo = TRUE'
            );
            $stmt->execute([':id_rol' => $idRol]);
            $cache[$idRol] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            // Tabla no migrada aún u otro problema de BD: no bloquear el acceso.
            return true;
        }
    }

    return in_array($pagina, $cache[$idRol], true);
}
