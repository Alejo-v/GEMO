<?php













const GEMO_ROL_ADMIN            = 1;
const GEMO_ROL_COORD_ZOO        = 2;
const GEMO_ROL_AUX_ZOO          = 3;
const GEMO_ROL_AUX_TERRENO      = 4;
const GEMO_ROL_COORD_TERRENO    = 5;
const GEMO_ROL_SUPER_ADMIN      = 6;


const GEMO_ROLES_CRUD_TERRENO = [GEMO_ROL_ADMIN, GEMO_ROL_COORD_TERRENO];


const GEMO_ROLES_CRUD_ZOO = [GEMO_ROL_ADMIN, GEMO_ROL_COORD_ZOO];


function gemoCarpetaRol(int $idRol): string
{
    $carpetas = [
        GEMO_ROL_ADMIN         => 'admin',
        GEMO_ROL_COORD_ZOO     => 'coordinador_zoocriadero',
        GEMO_ROL_AUX_ZOO       => 'auxiliar_zoocriadero',
        GEMO_ROL_AUX_TERRENO   => 'auxiliar_terreno',
        GEMO_ROL_COORD_TERRENO => 'coordinador_terreno',
        GEMO_ROL_SUPER_ADMIN   => 'super_admin',
    ];

    return $carpetas[$idRol] ?? '';
}


function gemoRolActual(): int
{
    return (int) ($_SESSION['usuario_rol_id'] ?? 0);
}





function gemoExigirRoles(array $rolesPermitidos): void
{
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ../login.php');
        exit;
    }

    if (!in_array(gemoRolActual(), $rolesPermitidos, true)) {
        $_SESSION['error'] = 'No tienes permisos para realizar esa acción.';
        header('Location: ../index.php');
        exit;
    }
}





function gemoVistaDelRol(string $pagina): string
{
    $carpeta = gemoCarpetaRol(gemoRolActual());

    if ($carpeta === '') {
        return '../index.php';
    }

    return '../views/' . $carpeta . '/' . $pagina;
}


function gemoVolverAVista(string $pagina): never
{
    header('Location: ' . gemoVistaDelRol($pagina));
    exit;
}


function gemoVolverConError(string $claveSesion, string $mensaje, string $pagina): never
{
    $_SESSION[$claveSesion] = $mensaje;
    gemoVolverAVista($pagina);
}
