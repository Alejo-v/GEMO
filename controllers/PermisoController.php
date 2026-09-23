<?php

session_start();
require_once __DIR__ . '/../includes/sesion_unica.php';
require_once __DIR__ . '/../models/Permiso.php';


if (!isset($_SESSION['usuario_id']) || (int) ($_SESSION['usuario_rol_id'] ?? 0) !== 6) {
    header('Location: ../login.php');
    exit;
}

validarSesionUnica();

function volverConError(string $mensaje): never
{
    $_SESSION['permiso_error'] = $mensaje;
    header('Location: ../views/super_admin/permisos.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['accion'] ?? '') !== 'cambiar_estado') {
    header('Location: ../views/super_admin/permisos.php');
    exit;
}

$idPermiso = (int) ($_POST['id_permiso'] ?? 0);
$activo = ($_POST['activo'] ?? '0') === '1';

if ($idPermiso <= 0) {
    volverConError('Permiso inválido.');
}

try {
    $modelo = new Permiso();

    $idRol = $modelo->obtenerRolDelPermiso($idPermiso);

    if ($idRol === null) {
        volverConError('El permiso indicado no existe.');
    }

    if (!$modelo->esRolAdministrable($idRol)) {
        volverConError('Ese rol no se administra desde este panel.');
    }

    if (!$activo && $modelo->contarActivosPorRol($idRol) <= 1) {
        volverConError('No puedes quitar el último permiso activo de ese rol: el usuario se quedaría sin ninguna página disponible.');
    }

    if ($modelo->actualizarEstado($idPermiso, $activo)) {
        $_SESSION['permiso_exito'] = $activo
            ? 'Permiso habilitado correctamente.'
            : 'Permiso deshabilitado correctamente.';
    } else {
        $_SESSION['permiso_error'] = 'No se pudo actualizar el permiso.';
    }
} catch (Throwable $e) {
    volverConError('No fue posible actualizar el permiso. Verifique la conexión con PostgreSQL.');
}

header('Location: ../views/super_admin/permisos.php');
exit;
