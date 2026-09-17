<?php

session_start();
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../models/ActividadTerreno.php';



gemoExigirRoles(GEMO_ROLES_CRUD_TERRENO);

function volverActividadConError(string $mensaje): never
{
    $_SESSION['actividad_terreno_error'] = $mensaje;
    header('Location: ' . gemoVistaDelRol('actividades.php'));
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new ActividadTerreno();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '' || mb_strlen($nombre) > 120) {
            volverActividadConError('El nombre de la actividad es obligatorio (máximo 120 caracteres).');
        }

        if ($accion === 'crear') {
            if ($modelo->nombreExiste($nombre)) {
                volverActividadConError('Ya existe una actividad con ese nombre.');
            }
            $modelo->crear($nombre);
            $_SESSION['actividad_terreno_exito'] = 'Actividad registrada correctamente.';
        } else {
            $id = (int) ($_POST['id_actividad_terreno'] ?? 0);
            if (!$modelo->obtenerPorId($id)) {
                volverActividadConError('La actividad que intenta editar no existe.');
            }
            if ($modelo->nombreExiste($nombre, $id)) {
                volverActividadConError('Ya existe otra actividad con ese nombre.');
            }
            $modelo->actualizar($id, $nombre);
            $_SESSION['actividad_terreno_exito'] = 'Actividad actualizada correctamente.';
        }

        header('Location: ' . gemoVistaDelRol('actividades.php'));
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $id = (int) ($_POST['id_actividad_terreno'] ?? 0);
        if (!$modelo->obtenerPorId($id)) {
            volverActividadConError('La actividad no existe.');
        }
        $modelo->cambiarEstado($id, $accion === 'habilitar');
        $_SESSION['actividad_terreno_exito'] = $accion === 'habilitar'
            ? 'Actividad habilitada correctamente.'
            : 'Actividad inhabilitada correctamente.';
        header('Location: ' . gemoVistaDelRol('actividades.php'));
        exit;
    }

    header('Location: ' . gemoVistaDelRol('actividades.php'));
    exit;
} catch (Throwable $e) {
    volverActividadConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
