<?php

session_start();
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../models/Sitio.php';



gemoExigirRoles(GEMO_ROLES_CRUD_TERRENO);

function volverSitioConError(string $mensaje, ?string $destino = null): never
{
    $_SESSION['sitio_error'] = $mensaje;
    header('Location: ' . ($destino ?? gemoVistaDelRol('sitios.php')));
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new Sitio();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $tipoVia = trim($_POST['tipo_via'] ?? '');
        $numeroVia = trim($_POST['numero_via'] ?? '');
        $letraVia = strtoupper(trim($_POST['letra_via'] ?? ''));
        $orientacion = trim($_POST['orientacion'] ?? '');
        $numeroPlaca = trim($_POST['numero_placa'] ?? '');
        $letraPlaca = strtoupper(trim($_POST['letra_placa'] ?? ''));
        $numeroMetros = trim($_POST['numero_metros'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');
        $latitud = trim($_POST['latitud'] ?? '');
        $longitud = trim($_POST['longitud'] ?? '');
        $idsBarrios = array_map('intval', $_POST['barrios'] ?? []);

        $tiposVia = ['Calle','Carrera','Avenida','Diagonal','Transversal','Circular','Autopista','Vía','Kilómetro'];
        $orientaciones = ['Norte','Sur','Este','Oeste'];
        if (!in_array($tipoVia, $tiposVia, true)) volverSitioConError('Debe seleccionar un tipo de vía válido.');
        if (!preg_match('/^[0-9]{1,4}$/', $numeroVia)) volverSitioConError('El número de vía debe contener entre 1 y 4 dígitos.');
        if ($letraVia !== '' && !preg_match('/^[A-Z]{1,2}$/', $letraVia)) volverSitioConError('La letra de la vía no es válida.');
        if ($orientacion !== '' && !in_array($orientacion, $orientaciones, true)) volverSitioConError('La orientación no es válida.');
        if (!preg_match('/^[0-9]{1,4}$/', $numeroPlaca)) volverSitioConError('El número de placa debe contener entre 1 y 4 dígitos.');
        if ($letraPlaca !== '' && !preg_match('/^[A-Z]{1,2}$/', $letraPlaca)) volverSitioConError('La letra de la placa no es válida.');
        if (!preg_match('/^[0-9]{1,4}$/', $numeroMetros)) volverSitioConError('El número después del guion debe contener entre 1 y 4 dígitos.');
        if ($complemento !== '' && (mb_strlen($complemento) > 20 || !preg_match('/^[\p{L}\p{N} .#\/\-]+$/u', $complemento))) volverSitioConError('El complemento de la dirección contiene caracteres no válidos.');

        $direccion = $tipoVia . ' ' . $numeroVia . $letraVia . ($orientacion !== '' ? ' ' . $orientacion : '') . ' # ' . $numeroPlaca . $letraPlaca . '-' . $numeroMetros . ($complemento !== '' ? ' ' . $complemento : '');
        if (mb_strlen($direccion) > 50) volverSitioConError('La dirección completa no puede superar 50 caracteres.');

        if ($latitud !== '' && (!is_numeric($latitud) || $latitud < -90 || $latitud > 90)) {
            volverSitioConError('La latitud debe estar entre -90 y 90.');
        }

        if ($longitud !== '' && (!is_numeric($longitud) || $longitud < -180 || $longitud > 180)) {
            volverSitioConError('La longitud debe estar entre -180 y 180.');
        }

        if (empty($idsBarrios)) {
            volverSitioConError('Debe seleccionar al menos un barrio.');
        }

        foreach ($idsBarrios as $idBarrio) {
            if (!$modelo->barrioExiste($idBarrio)) {
                volverSitioConError('Uno de los barrios seleccionados no es válido.');
            }
        }

        $datos = [
            ':direccion' => $direccion,
            ':latitud' => $latitud !== '' ? $latitud : null,
            ':longitud' => $longitud !== '' ? $longitud : null,
        ];

        if ($accion === 'crear') {
            $modelo->crear($datos, $idsBarrios);
            $_SESSION['sitio_exito'] = 'Sitio registrado correctamente.';
        } else {
            $idSitio = (int) ($_POST['id_sitio'] ?? 0);
            if (!$modelo->obtenerPorId($idSitio)) {
                volverSitioConError('El sitio que intenta editar no existe.');
            }
            $modelo->actualizar($idSitio, $datos, $idsBarrios);
            $_SESSION['sitio_exito'] = 'Sitio actualizado correctamente.';
        }

        header('Location: ' . gemoVistaDelRol('sitios.php'));
        exit;
    }

    if ($accion === 'inhabilitar' || $accion === 'habilitar') {
        $idSitio = (int) ($_POST['id_sitio'] ?? 0);
        if (!$modelo->obtenerPorId($idSitio)) {
            volverSitioConError('El sitio no existe.');
        }
        $modelo->cambiarEstado($idSitio, $accion === 'habilitar');
        $_SESSION['sitio_exito'] = $accion === 'habilitar'
            ? 'Sitio habilitado correctamente.'
            : 'Sitio inhabilitado correctamente.';
        header('Location: ' . gemoVistaDelRol('sitios.php'));
        exit;
    }

    header('Location: ' . gemoVistaDelRol('sitios.php'));
    exit;
} catch (Throwable $e) {
    volverSitioConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
