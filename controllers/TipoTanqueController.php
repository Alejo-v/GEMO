<?php

session_start();
require_once __DIR__ . '/../models/TipoTanque.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 3) {
    header('Location: ../login.php');
    exit;
}

function volverTipoTanqueConError(string $mensaje): never
{
    $_SESSION['tipo_tanque_error'] = $mensaje;
    header('Location: ../views/auxiliar_zoocriadero/tipos_tanque.php');
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new TipoTanque();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($descripcion === '' || mb_strlen($descripcion) > 50) {
            volverTipoTanqueConError('La descripción es obligatoria (máximo 50 caracteres).');
        }

        if ($accion === 'crear') {
            if ($modelo->descripcionExiste($descripcion)) {
                volverTipoTanqueConError('Ya existe un tipo de tanque con esa descripción.');
            }
            $modelo->crear($descripcion);
            $_SESSION['tipo_tanque_exito'] = 'Tipo de tanque registrado correctamente.';
        } else {
            $id = (int) ($_POST['id_tipo_tanque'] ?? 0);
            if (!$modelo->obtenerPorId($id)) {
                volverTipoTanqueConError('El tipo de tanque que intenta editar no existe.');
            }
            if ($modelo->descripcionExiste($descripcion, $id)) {
                volverTipoTanqueConError('Ya existe otro tipo de tanque con esa descripción.');
            }
            $modelo->actualizar($id, $descripcion);
            $_SESSION['tipo_tanque_exito'] = 'Tipo de tanque actualizado correctamente.';
        }

        header('Location: ../views/auxiliar_zoocriadero/tipos_tanque.php');
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $id = (int) ($_POST['id_tipo_tanque'] ?? 0);
        if (!$modelo->obtenerPorId($id)) {
            volverTipoTanqueConError('El tipo de tanque no existe.');
        }
        $modelo->cambiarEstado($id, $accion === 'habilitar');
        $_SESSION['tipo_tanque_exito'] = $accion === 'habilitar'
            ? 'Tipo de tanque habilitado correctamente.'
            : 'Tipo de tanque inhabilitado correctamente.';
        header('Location: ../views/auxiliar_zoocriadero/tipos_tanque.php');
        exit;
    }

    header('Location: ../views/auxiliar_zoocriadero/tipos_tanque.php');
    exit;
} catch (Throwable $e) {
    volverTipoTanqueConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
