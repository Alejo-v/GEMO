<?php

session_start();
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../models/Zoocriadero.php';



gemoExigirRoles(GEMO_ROLES_CRUD_ZOO);

function volverZoocriaderoConError(string $mensaje): never
{
    $_SESSION['zoocriadero_error'] = $mensaje;
    header('Location: ' . gemoVistaDelRol('zoocriaderos.php'));
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new Zoocriadero();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $tipoVia = trim($_POST['tipo_via'] ?? '');
        $numeroVia = trim($_POST['numero_via'] ?? '');
        $letraVia = strtoupper(trim($_POST['letra_via'] ?? ''));
        $orientacion = trim($_POST['orientacion'] ?? '');
        $numeroPlaca = trim($_POST['numero_placa'] ?? '');
        $letraPlaca = strtoupper(trim($_POST['letra_placa'] ?? ''));
        $complemento = trim($_POST['complemento'] ?? '');
        $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
        $idBarrio = (int) ($_POST['id_barrio'] ?? 0);

        $tiposVia = ['Calle','Carrera','Avenida','Diagonal','Transversal','Circular','Autopista','Vía','Kilómetro'];
        $orientaciones = ['Norte','Sur','Este','Oeste'];
        if (!in_array($tipoVia, $tiposVia, true)) volverZoocriaderoConError('Debe seleccionar un tipo de vía válido.');
        if (!preg_match('/^[0-9]{1,4}$/', $numeroVia)) volverZoocriaderoConError('El número de vía debe contener entre 1 y 4 dígitos.');
        if ($letraVia !== '' && !preg_match('/^[A-Z]{1,2}$/', $letraVia)) volverZoocriaderoConError('La letra de la vía no es válida.');
        if ($orientacion !== '' && !in_array($orientacion, $orientaciones, true)) volverZoocriaderoConError('La orientación no es válida.');
        if (!preg_match('/^[0-9]{1,4}$/', $numeroPlaca)) volverZoocriaderoConError('El número de placa debe contener entre 1 y 4 dígitos.');
        if ($letraPlaca !== '' && !preg_match('/^[A-Z]{1,2}$/', $letraPlaca)) volverZoocriaderoConError('La letra de la placa no es válida.');
        if ($complemento !== '' && (mb_strlen($complemento) > 20 || !preg_match('/^[\p{L}\p{N} .#\/\-]+$/u', $complemento))) volverZoocriaderoConError('El complemento de la dirección contiene caracteres no válidos.');
        $direccion = $tipoVia . ' ' . $numeroVia . $letraVia . ($orientacion !== '' ? ' ' . $orientacion : '') . ' # ' . $numeroPlaca . $letraPlaca . ($complemento !== '' ? '-' . $complemento : '');
        if (mb_strlen($direccion) > 50) volverZoocriaderoConError('La dirección completa no puede superar 50 caracteres.');

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

        header('Location: ' . gemoVistaDelRol('zoocriaderos.php'));
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
        header('Location: ' . gemoVistaDelRol('zoocriaderos.php'));
        exit;
    }

    header('Location: ' . gemoVistaDelRol('zoocriaderos.php'));
    exit;
} catch (Throwable $e) {
    volverZoocriaderoConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
