<?php

session_start();
require_once __DIR__ . '/../models/Sitio.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 4) {
    header('Location: ../login.php');
    exit;
}

function volverSitioConError(string $mensaje, string $destino = '../views/auxiliar_terreno/sitios.php'): never
{
    $_SESSION['sitio_error'] = $mensaje;
    header("Location: $destino");
    exit;
}

$accion = $_POST['accion'] ?? '';
$modelo = new Sitio();

try {
    if ($accion === 'crear' || $accion === 'actualizar') {
        $direccion = trim($_POST['direccion'] ?? '');
        $latitud = trim($_POST['latitud'] ?? '');
        $longitud = trim($_POST['longitud'] ?? '');
        $idsBarrios = array_map('intval', $_POST['barrios'] ?? []);

        if ($direccion === '' || mb_strlen($direccion) > 50) {
            volverSitioConError('La dirección es obligatoria (máximo 50 caracteres).');
        }

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

        header('Location: ../views/auxiliar_terreno/sitios.php');
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
        header('Location: ../views/auxiliar_terreno/sitios.php');
        exit;
    }

    header('Location: ../views/auxiliar_terreno/sitios.php');
    exit;
} catch (Throwable $e) {
    volverSitioConError('No fue posible completar la operación. Revise la configuración de PostgreSQL.');
}
