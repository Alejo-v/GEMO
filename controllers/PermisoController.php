<?php

session_start();
require_once __DIR__ . '/../models/Permiso.php';

// Solo el Super Administrador (rol 6) puede modificar permisos.
if (!isset($_SESSION['usuario_id']) || (int) ($_SESSION['usuario_rol_id'] ?? 0) !== 6) {
    header('Location: ../login.php');
    exit;
}

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

$modelo = new Permiso();

$idRol = $modelo->obtenerRolDelPermiso($idPermiso);

if ($idRol === null) {
    volverConError('El permiso indicado no existe.');
}

if (!$modelo->esRolAdministrable($idRol)) {
    volverConError('Ese rol no se administra desde este panel.');
}

// No dejar un rol sin ninguna página habilitada: si se va a desactivar
// el último permiso activo de ese rol, se rechaza el cambio.
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

header('Location: ../views/super_admin/permisos.php');
exit;
