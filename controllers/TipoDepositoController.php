<?php

session_start();
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../models/TipoDeposito.php';



gemoExigirRoles(GEMO_ROLES_CRUD_TERRENO);

function volverTipoDepositoConError(string $mensaje): never
{
    $_SESSION['tipo_deposito_error'] = $mensaje;
    header('Location: ' . gemoVistaDelRol('tipos_deposito.php'));
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new TipoDeposito();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $descripcion = trim($_POST['descripcion'] ?? '');

        if ($descripcion === '' || mb_strlen($descripcion) > 50) {
            volverTipoDepositoConError('La descripción es obligatoria (máximo 50 caracteres).');
        }

        if ($accion === 'crear') {
            if ($modelo->descripcionExiste($descripcion)) {
                volverTipoDepositoConError('Ya existe un tipo de depósito con esa descripción.');
            }
            $modelo->crear($descripcion);
            $_SESSION['tipo_deposito_exito'] = 'Tipo de depósito registrado correctamente.';
        } else {
            $id = (int) ($_POST['id_tipo_deposito'] ?? 0);
            if (!$modelo->obtenerPorId($id)) {
                volverTipoDepositoConError('El tipo de depósito que intenta editar no existe.');
            }
            if ($modelo->descripcionExiste($descripcion, $id)) {
                volverTipoDepositoConError('Ya existe otro tipo de depósito con esa descripción.');
            }
            $modelo->actualizar($id, $descripcion);
            $_SESSION['tipo_deposito_exito'] = 'Tipo de depósito actualizado correctamente.';
        }

        header('Location: ' . gemoVistaDelRol('tipos_deposito.php'));
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $id = (int) ($_POST['id_tipo_deposito'] ?? 0);
        if (!$modelo->obtenerPorId($id)) {
            volverTipoDepositoConError('El tipo de depósito no existe.');
        }

        if ($accion === 'inhabilitar' && $modelo->tieneDepositosActivos($id)) {
            volverTipoDepositoConError('No se puede inhabilitar: hay depósitos activos que usan este tipo.');
        }

        $modelo->cambiarEstado($id, $accion === 'habilitar');
        $_SESSION['tipo_deposito_exito'] = $accion === 'habilitar'
            ? 'Tipo de depósito habilitado correctamente.'
            : 'Tipo de depósito inhabilitado correctamente.';
        header('Location: ' . gemoVistaDelRol('tipos_deposito.php'));
        exit;
    }

    header('Location: ' . gemoVistaDelRol('tipos_deposito.php'));
    exit;
} catch (Throwable $e) {
    volverTipoDepositoConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
