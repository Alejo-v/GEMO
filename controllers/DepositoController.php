<?php

session_start();
require_once __DIR__ . '/../models/Deposito.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 4) {
    header('Location: ../login.php');
    exit;
}

function volverDepositoConError(string $mensaje): never
{
    $_SESSION['deposito_error'] = $mensaje;
    header('Location: ../views/auxiliar_terreno/depositos.php');
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new Deposito();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $idSitio = (int) ($_POST['id_sitio'] ?? 0);
        $idTipoDeposito = (int) ($_POST['id_tipo_deposito'] ?? 0);

        if ($idSitio <= 0 || !$modelo->sitioExiste($idSitio)) {
            volverDepositoConError('Debe seleccionar un sitio válido.');
        }

        if ($idTipoDeposito <= 0 || !$modelo->tipoDepositoExiste($idTipoDeposito)) {
            volverDepositoConError('Debe seleccionar un tipo de depósito válido.');
        }

        $datos = [':id_sitio' => $idSitio, ':id_tipo_deposito' => $idTipoDeposito];

        if ($accion === 'crear') {
            $modelo->crear($datos);
            $_SESSION['deposito_exito'] = 'Depósito registrado correctamente.';
        } else {
            $idDeposito = (int) ($_POST['id_deposito'] ?? 0);
            if (!$modelo->obtenerPorId($idDeposito)) {
                volverDepositoConError('El depósito que intenta editar no existe.');
            }
            $modelo->actualizar($idDeposito, $datos);
            $_SESSION['deposito_exito'] = 'Depósito actualizado correctamente.';
        }

        header('Location: ../views/auxiliar_terreno/depositos.php');
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $idDeposito = (int) ($_POST['id_deposito'] ?? 0);
        if (!$modelo->obtenerPorId($idDeposito)) {
            volverDepositoConError('El depósito no existe.');
        }
        $modelo->cambiarEstado($idDeposito, $accion === 'habilitar');
        $_SESSION['deposito_exito'] = $accion === 'habilitar'
            ? 'Depósito habilitado correctamente.'
            : 'Depósito inhabilitado correctamente.';
        header('Location: ../views/auxiliar_terreno/depositos.php');
        exit;
    }

    header('Location: ../views/auxiliar_terreno/depositos.php');
    exit;
} catch (Throwable $e) {
    volverDepositoConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
