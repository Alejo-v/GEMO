<?php

session_start();
require_once __DIR__ . '/../models/ActividadZoocriadero.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 3) {
    header('Location: ../login.php');
    exit;
}

function volverAccionConError(string $mensaje): never
{
    $_SESSION['accion_zoo_error'] = $mensaje;
    header('Location: ../views/auxiliar_zoocriadero/acciones_zoocriadero.php');
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new ActividadZoocriadero();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $nombre = trim($_POST['nombre'] ?? '');

        if ($nombre === '' || mb_strlen($nombre) > 120) {
            volverAccionConError('El nombre de la acción es obligatorio (máximo 120 caracteres).');
        }

        if ($accion === 'crear') {
            if ($modelo->nombreExiste($nombre)) {
                volverAccionConError('Ya existe una acción con ese nombre.');
            }
            $modelo->crear($nombre);
            $_SESSION['accion_zoo_exito'] = 'Acción registrada correctamente.';
        } else {
            $id = (int) ($_POST['id_actividad_zoocriadero'] ?? 0);
            if (!$modelo->obtenerPorId($id)) {
                volverAccionConError('La acción que intenta editar no existe.');
            }
            if ($modelo->nombreExiste($nombre, $id)) {
                volverAccionConError('Ya existe otra acción con ese nombre.');
            }
            $modelo->actualizar($id, $nombre);
            $_SESSION['accion_zoo_exito'] = 'Acción actualizada correctamente.';
        }

        header('Location: ../views/auxiliar_zoocriadero/acciones_zoocriadero.php');
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $id = (int) ($_POST['id_actividad_zoocriadero'] ?? 0);
        if (!$modelo->obtenerPorId($id)) {
            volverAccionConError('La acción no existe.');
        }
        $modelo->cambiarEstado($id, $accion === 'habilitar');
        $_SESSION['accion_zoo_exito'] = $accion === 'habilitar'
            ? 'Acción habilitada correctamente.'
            : 'Acción inhabilitada correctamente.';
        header('Location: ../views/auxiliar_zoocriadero/acciones_zoocriadero.php');
        exit;
    }

    header('Location: ../views/auxiliar_zoocriadero/acciones_zoocriadero.php');
    exit;
} catch (Throwable $e) {
    volverAccionConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
