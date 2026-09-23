<?php

session_start();
require_once __DIR__ . '/../includes/sesion_unica.php';
require_once __DIR__ . '/../models/SeguimientoTerreno.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 4) {
    header('Location: ../login.php');
    exit;
}

validarSesionUnica();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ($_POST['accion'] ?? '') !== 'registrar_seguimiento') {
    header('Location: ../views/auxiliar_terreno/registrar_seguimiento.php');
    exit;
}

function volverConError(string $mensaje): never
{
    $_SESSION['seguimiento_terreno_error'] = $mensaje;
    header('Location: ../views/auxiliar_terreno/registrar_seguimiento.php');
    exit;
}

$idDeposito = (int)($_POST['id_deposito'] ?? 0);
$idActividad = (int)($_POST['id_actividad_terreno'] ?? 0);
$ph = trim($_POST['ph'] ?? '');
$temperatura = trim($_POST['temperatura'] ?? '');
$larvasAedes = trim($_POST['larvas_aedes'] ?? '');
$pupas = trim($_POST['pupas'] ?? '');
$larvasCulex = trim($_POST['larvas_culex'] ?? '');

if ($idDeposito <= 0) {
    volverConError('Debe seleccionar el depósito inspeccionado.');
}

if ($idActividad <= 0) {
    volverConError('Debe seleccionar la actividad realizada.');
}

if ($ph === '' || !is_numeric($ph) || $ph < 0 || $ph > 14) {
    volverConError('El pH debe ser un valor numérico entre 0 y 14.');
}

if ($temperatura === '' || !is_numeric($temperatura) || $temperatura < 0 || $temperatura > 50) {
    volverConError('La temperatura debe ser un valor numérico válido.');
}

foreach ([
    'Larvas de Aedes' => $larvasAedes,
    'Pupas' => $pupas,
    'Larvas de Culex' => $larvasCulex,
] as $etiqueta => $valor) {
    if ($valor === '' || !ctype_digit($valor)) {
        volverConError("El campo \"$etiqueta\" debe ser un número entero positivo.");
    }
}

try {
    $modelo = new SeguimientoTerreno();

    if (!$modelo->depositoExiste($idDeposito)) {
        volverConError('El depósito seleccionado no existe.');
    }

    if (!$modelo->actividadExiste($idActividad)) {
        volverConError('La actividad seleccionada no es válida.');
    }

    
    
    $modelo->registrar([
        'id_deposito'          => $idDeposito,
        'id_usuario'           => $_SESSION['usuario_id'] ?? 1, 
        'fecha'                => date('Y-m-d'),
        'id_actividad_terreno' => $idActividad,
        'ph'                   => $ph,
        'temperatura'          => $temperatura,
        'larvas_aedes'         => (int) $larvasAedes,
        'pupas'                => (int) $pupas,
        'larvas_culex'         => (int) $larvasCulex,
    ]);

    $_SESSION['seguimiento_terreno_exito'] = 'Registro guardado correctamente.';
    header('Location: ../views/auxiliar_terreno/registrar_seguimiento.php');
    exit;

} catch (Throwable $e) {
    
    volverConError('Error de ejecución: ' . $e->getMessage() . ' (Línea ' . $e->getLine() . ')');
}