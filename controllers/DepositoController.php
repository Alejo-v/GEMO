<?php

session_start();
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../models/Deposito.php';



gemoExigirRoles(GEMO_ROLES_CRUD_TERRENO);

function volverDepositoConError(string $mensaje): never
{
    $_SESSION['deposito_error'] = $mensaje;
    header('Location: ' . gemoVistaDelRol('depositos.php'));
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

        header('Location: ' . gemoVistaDelRol('depositos.php'));
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
        header('Location: ' . gemoVistaDelRol('depositos.php'));
        exit;
    }

    header('Location: ' . gemoVistaDelRol('depositos.php'));
    exit;
} catch (Throwable $e) {
    volverDepositoConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
