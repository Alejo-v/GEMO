<?php

session_start();
require_once __DIR__ . '/../models/Tanque.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 3) {
    header('Location: ../login.php');
    exit;
}

function volverTanqueConError(string $mensaje): never
{
    $_SESSION['tanque_error'] = $mensaje;
    header('Location: ../views/auxiliar_zoocriadero/tanques.php');
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new Tanque();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $idZoocriadero = (int) ($_POST['id_zoocriadero'] ?? 0);
        $idTipoTanque = (int) ($_POST['id_tipo_tanque'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');

        if ($idZoocriadero <= 0 || !$modelo->zoocriaderoExiste($idZoocriadero)) {
            volverTanqueConError('Debe seleccionar un zoocriadero válido.');
        }

        if ($idTipoTanque <= 0 || !$modelo->tipoTanqueExiste($idTipoTanque)) {
            volverTanqueConError('Debe seleccionar un tipo de tanque válido.');
        }

        if (!in_array($estado, Tanque::ESTADOS, true)) {
            volverTanqueConError('Debe seleccionar un estado operativo válido.');
        }

        $datos = [':id_zoocriadero' => $idZoocriadero, ':id_tipo_tanque' => $idTipoTanque, ':estado' => $estado];

        if ($accion === 'crear') {
            $modelo->crear($datos);
            $_SESSION['tanque_exito'] = 'Tanque registrado correctamente.';
        } else {
            $id = (int) ($_POST['id_tanque'] ?? 0);
            if (!$modelo->obtenerPorId($id)) {
                volverTanqueConError('El tanque que intenta editar no existe.');
            }
            $modelo->actualizar($id, $datos);
            $_SESSION['tanque_exito'] = 'Tanque actualizado correctamente.';
        }

        header('Location: ../views/auxiliar_zoocriadero/tanques.php');
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $id = (int) ($_POST['id_tanque'] ?? 0);
        if (!$modelo->obtenerPorId($id)) {
            volverTanqueConError('El tanque no existe.');
        }
        $modelo->cambiarEstado($id, $accion === 'habilitar');
        $_SESSION['tanque_exito'] = $accion === 'habilitar'
            ? 'Tanque habilitado correctamente.'
            : 'Tanque inhabilitado correctamente.';
        header('Location: ../views/auxiliar_zoocriadero/tanques.php');
        exit;
    }

    header('Location: ../views/auxiliar_zoocriadero/tanques.php');
    exit;
} catch (Throwable $e) {
    volverTanqueConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
