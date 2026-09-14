<?php

session_start();
require_once __DIR__ . '/../models/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit;
}

$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';

if ($correo === '' || $password === '') {
    $_SESSION['error_login'] = 'Debe completar todos los campos.';
    header('Location: ../login.php');
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_login'] = 'Ingrese un correo electrónico válido.';
    header('Location: ../login.php');
    exit;
}

try {
    $usuario = (new Usuario())->buscarPorCorreo($correo);
} catch (Throwable $e) {
    $_SESSION['error_login'] = 'No fue posible consultar el sistema.';
    header('Location: ../login.php');
    exit;
}

if (!$usuario || !password_verify($password, $usuario['contraseña'])) {
    $_SESSION['error_login'] = 'Correo o contraseña incorrectos.';
    header('Location: ../login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = $usuario['id_usuario'];
$_SESSION['usuario_nombre'] = $usuario['nombres'];
$_SESSION['usuario_apellido'] = $usuario['apellidos'];
$_SESSION['usuario_rol_id'] = $usuario['id_rol'];
$_SESSION['usuario_rol'] = $usuario['nombre_rol'];

if ((int)$usuario['id_rol'] === 1) {
    header('Location: ../views/admin/dashboard.php');
    exit;
}

if ((int)$usuario['id_rol'] === 3) {
    header('Location: ../views/auxiliar_zoocriadero/registrar_seguimiento.php');
    exit;
}

if ((int)$usuario['id_rol'] === 4) {
    header('Location: ../views/auxiliar_terreno/registrar_seguimiento.php');
    exit;
}

header('Location: ../index.php');
exit;
