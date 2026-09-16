<?php

/**
 * Mapa de permisos por rol.
 *
 * La clave es el id_rol (tabla `rol` en la BD) y el valor es la lista de
 * páginas (nombre de archivo dentro de la carpeta de ese rol en /views)
 * que ese rol tiene permitido abrir.
 *
 * Esto permite, sin tocar la lógica de cada vista, restringir el acceso
 * a páginas puntuales dentro de un mismo rol (por ejemplo, si mañana
 * "reportes.php" solo debe verlo el coordinador y no el auxiliar que
 * comparte esa misma carpeta).
 */
$GLOBALS['PERMISOS_POR_ROL'] = [

    // 1 - Administrador del Sistema
    1 => [
        'dashboard.php',
        'usuarios.php',
        'registrar_usuario.php',
        'reportes.php',
        'informacion_personal.php',
    ],

    // 2 - Coordinador Zoocriadero
    2 => [
        'inicio.php',
        'reportes.php',
        'informacion_personal.php',
    ],

    // 3 - Auxiliar Zoocriadero
    3 => [
        'inicio.php',
        'mis_registros.php',
        'registrar_seguimiento.php',
        'informacion_personal.php',
    ],

    // 4 - Auxiliar Terreno
    4 => [
        'inicio.php',
        'actividades.php',
        'depositos.php',
        'estadisticas.php',
        'mis_registros.php',
        'registrar_seguimiento.php',
        'reportes.php',
        'sitios.php',
        'Formulario_seguimiento.php',
        'informacion_personal.php',
    ],

    // 5 - Coordinador Terreno
    5 => [
        'inicio.php',
        'reportes.php',
        'informacion_personal.php',
    ],

    // 6 - Super Administrador
    6 => [
        'dashboard.php',
        'auditoria_terreno.php',
        'informacion_personal.php',
    ],

];

/**
 * Indica si el rol dado tiene permiso para abrir la página indicada.
 *
 * @param int    $idRol   id_rol de la sesión ($_SESSION['usuario_rol_id'])
 * @param string $pagina  nombre del archivo actual, ej: basename($_SERVER['PHP_SELF'])
 */
function usuarioTienePermiso($idRol, $pagina)
{
    if (!isset($GLOBALS['PERMISOS_POR_ROL'][$idRol])) {
        return false;
    }

    return in_array($pagina, $GLOBALS['PERMISOS_POR_ROL'][$idRol]);
}
