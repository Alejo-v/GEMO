<?php

require_once __DIR__ . '/../config/database.php';
















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
            
            return true;
        }
    }

    return in_array($pagina, $cache[$idRol], true);
}
