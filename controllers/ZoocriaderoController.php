<?php

session_start();
require_once __DIR__ . '/../models/Zoocriadero.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 3) {
    header('Location: ../login.php');
    exit;
}

function volverZoocriaderoConError(string $mensaje): never
{
    $_SESSION['zoocriadero_error'] = $mensaje;
    header('Location: ../views/auxiliar_zoocriadero/zoocriaderos.php');
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new Zoocriadero();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $direccion = trim($_POST['direccion'] ?? '');
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $idBarrio = (int) ($_POST['id_barrio'] ?? 0);

        if ($direccion === '' || mb_strlen($direccion) > 50) {
            volverZoocriaderoConError('La dirección es obligatoria (máximo 50 caracteres).');
        }

        if ($idBarrio <= 0 || !$modelo->barrioExiste($idBarrio)) {
            volverZoocriaderoConError('Debe seleccionar un barrio válido.');
        }

        if ($idUsuario <= 0 || !$modelo->usuarioExiste($idUsuario)) {
            volverZoocriaderoConError('Debe seleccionar un encargado válido.');
        }

        $datos = [':direccion' => $direccion, ':id_usuario' => $idUsuario, ':id_barrio' => $idBarrio];

        if ($accion === 'crear') {
            $modelo->crear($datos);
            $_SESSION['zoocriadero_exito'] = 'Zoocriadero registrado correctamente.';
        } else {
            $id = (int) ($_POST['id_zoocriadero'] ?? 0);
            if (!$modelo->obtenerPorId($id)) {
                volverZoocriaderoConError('El zoocriadero que intenta editar no existe.');
            }
            $modelo->actualizar($id, $datos);
            $_SESSION['zoocriadero_exito'] = 'Zoocriadero actualizado correctamente.';
        }

        header('Location: ../views/auxiliar_zoocriadero/zoocriaderos.php');
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $id = (int) ($_POST['id_zoocriadero'] ?? 0);
        if (!$modelo->obtenerPorId($id)) {
            volverZoocriaderoConError('El zoocriadero no existe.');
        }
        $modelo->cambiarEstado($id, $accion === 'habilitar');
        $_SESSION['zoocriadero_exito'] = $accion === 'habilitar'
            ? 'Zoocriadero habilitado correctamente.'
            : 'Zoocriadero inhabilitado correctamente.';
        header('Location: ../views/auxiliar_zoocriadero/zoocriaderos.php');
        exit;
    }

    header('Location: ../views/auxiliar_zoocriadero/zoocriaderos.php');
    exit;
} catch (Throwable $e) {
    volverZoocriaderoConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
