<?php

session_start();
require_once __DIR__ . '/../models/SeguimientoZoocriadero.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 3) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['accion'] ?? '') !== 'registrar_seguimiento') {
    header('Location: ../views/auxiliar_zoocriadero/registrar_seguimiento.php');
    exit;
}

function volverConError(string $mensaje): never
{
    $_SESSION['seguimiento_error'] = $mensaje;
    header('Location: ../views/auxiliar_zoocriadero/registrar_seguimiento.php');
    exit;
}

$idTanque = (int)($_POST['id_tanque'] ?? 0);
$ph = trim($_POST['ph'] ?? '');
$temperatura = trim($_POST['temperatura'] ?? '');
$cloro = trim($_POST['cloro'] ?? '');
$alevinesNacidos = trim($_POST['alevines_nacidos'] ?? '');
$muertosMacho = trim($_POST['muertos_macho'] ?? '');
$muertosHembra = trim($_POST['muertos_hembra'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');
$actividades = array_map('intval', (array)($_POST['actividades'] ?? []));

if ($idTanque <= 0) {
    volverConError('Debe seleccionar el número de tanque.');
}

if ($ph === '' || !is_numeric($ph) || $ph < 0 || $ph > 14) {
    volverConError('El pH debe ser un valor numérico entre 0 y 14.');
}

if ($temperatura === '' || !is_numeric($temperatura) || $temperatura < 0 || $temperatura > 50) {
    volverConError('La temperatura debe ser un valor numérico válido.');
}

if ($cloro === '' || !is_numeric($cloro) || $cloro < 0 || $cloro > 20) {
    volverConError('El cloro debe ser un valor numérico válido.');
}

foreach ([
    'Número de alevines nacidos' => $alevinesNacidos,
    'Número de muertos (macho)' => $muertosMacho,
    'Número de muertos (hembra)' => $muertosHembra,
] as $etiqueta => $valor) {
    if ($valor === '' || !ctype_digit($valor)) {
        volverConError("El campo \"$etiqueta\" debe ser un número entero positivo.");
    }
}

if (strlen($observaciones) > 1000) {
    volverConError('Las observaciones no pueden superar los 1000 caracteres.');
}

try {
    $modelo = new SeguimientoZoocriadero();

    if (!$modelo->tanqueExiste($idTanque)) {
        volverConError('El tanque seleccionado no existe.');
    }

    if (!$modelo->actividadesValidas($actividades)) {
        volverConError('Una de las actividades seleccionadas no es válida.');
    }

    $modelo->registrar([
        ':id_tanque' => $idTanque,
        ':id_usuario' => $_SESSION['usuario_id'],
        ':fecha' => date('Y-m-d'),
        ':ph' => $ph,
        ':temperatura' => $temperatura,
        ':cloro' => $cloro,
        ':alevines_nacidos' => (int) $alevinesNacidos,
        ':muertos_macho' => (int) $muertosMacho,
        ':muertos_hembra' => (int) $muertosHembra,
        ':observaciones' => $observaciones !== '' ? $observaciones : null,
    ], $actividades);

    $_SESSION['seguimiento_exito'] = 'Registro guardado correctamente.';
    header('Location: ../views/auxiliar_zoocriadero/registrar_seguimiento.php');
    exit;
} catch (Throwable $e) {
    volverConError('No fue posible guardar el registro. Revise la configuración de PostgreSQL.');
}
