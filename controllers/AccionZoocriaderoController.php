<?php

session_start();
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../models/ActividadZoocriadero.php';



gemoExigirRoles(GEMO_ROLES_CRUD_ZOO);

function volverAccionConError(string $mensaje): never
{
    $_SESSION['accion_zoo_error'] = $mensaje;
    header('Location: ' . gemoVistaDelRol('acciones_zoocriadero.php'));
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new ActividadZoocriadero();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '' || mb_strlen($nombre) > 120) {
            volverAccionConError('El nombre del proceso es obligatorio (máximo 120 caracteres).');
        }

        if ($accion === 'crear') {
            if ($modelo->nombreExiste($nombre)) {
                volverAccionConError('Ya existe un proceso con ese nombre.');
            }
            $modelo->crear($nombre);
            $_SESSION['accion_zoo_exito'] = 'Proceso registrado correctamente.';
        } else {
            $id = (int) ($_POST['id_actividad_zoocriadero'] ?? 0);
            if (!$modelo->obtenerPorId($id)) {
                volverAccionConError('El proceso que intenta editar no existe.');
            }
            if ($modelo->nombreExiste($nombre, $id)) {
                volverAccionConError('Ya existe otro proceso con ese nombre.');
            }
            $modelo->actualizar($id, $nombre);
            $_SESSION['accion_zoo_exito'] = 'Proceso actualizado correctamente.';
        }

        header('Location: ' . gemoVistaDelRol('acciones_zoocriadero.php'));
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $id = (int) ($_POST['id_actividad_zoocriadero'] ?? 0);
        if (!$modelo->obtenerPorId($id)) {
            volverAccionConError('El proceso no existe.');
        }
        $modelo->cambiarEstado($id, $accion === 'habilitar');
        $_SESSION['accion_zoo_exito'] = $accion === 'habilitar'
            ? 'Proceso habilitado correctamente.'
            : 'Proceso inhabilitado correctamente.';
        header('Location: ' . gemoVistaDelRol('acciones_zoocriadero.php'));
        exit;
    }

    header('Location: ' . gemoVistaDelRol('acciones_zoocriadero.php'));
    exit;
} catch (Throwable $e) {
    volverAccionConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
