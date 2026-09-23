<?php

session_start();
require_once __DIR__ . '/../includes/sesion_unica.php';
require_once __DIR__ . '/../includes/roles.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/password_validation.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login.php');
    exit;
}

validarSesionUnica();





$idUsuario = (int) $_SESSION['usuario_id'];
$idRol = (int) ($_SESSION['usuario_rol_id'] ?? 0);
$destino = gemoVistaDelRol('informacion_personal.php');

function volverConError(string $mensaje, string $destino): never
{
    $_SESSION['perfil_error'] = $mensaje;
    header('Location: ' . $destino);
    exit;
}

function volverConExito(string $mensaje, string $destino): never
{
    $_SESSION['perfil_exito'] = $mensaje;
    header('Location: ' . $destino);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($accion, ['actualizar_telefono', 'actualizar_correo', 'actualizar_password'], true)) {
    header('Location: ' . $destino);
    exit;
}

try {
    $usuarioModel = new Usuario();

    if ($accion === 'actualizar_telefono') {
        $telefono = trim($_POST['telefono'] ?? '');

        if ($telefono === '') {
            volverConError('El número de teléfono es obligatorio.', $destino);
        }

        if (!preg_match('/^[0-9+\s\-]{7,20}$/', $telefono)) {
            volverConError('El número de teléfono no tiene un formato válido.', $destino);
        }

        $usuarioModel->actualizarTelefono($idUsuario, $telefono);
        volverConExito('Teléfono actualizado correctamente.', $destino);
    }

    if ($accion === 'actualizar_correo') {
        $correo = trim($_POST['correo'] ?? '');
        $passwordActual = $_POST['password_actual_correo'] ?? '';

        if ($correo === '' || $passwordActual === '') {
            volverConError('El correo y su contraseña actual son obligatorios.', $destino);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            volverConError('El correo electrónico no es válido.', $destino);
        }

        $usuarioActual = $usuarioModel->buscarPorId($idUsuario);
        $usuarioConHash = $usuarioModel->buscarPorCorreo($usuarioActual['correo'] ?? '');

        if (!$usuarioConHash || !password_verify($passwordActual, $usuarioConHash['contraseña'])) {
            volverConError('La contraseña actual no es correcta.', $destino);
        }

        if ($usuarioModel->correoExisteEnOtroUsuario($correo, $idUsuario)) {
            volverConError('Ese correo electrónico ya está en uso por otro usuario.', $destino);
        }

        $usuarioModel->actualizarCorreo($idUsuario, $correo);
        volverConExito('Correo electrónico actualizado correctamente.', $destino);
    }

    if ($accion === 'actualizar_password') {
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $passwordConfirmar = $_POST['password_confirmar'] ?? '';

        if ($passwordActual === '' || $passwordNueva === '' || $passwordConfirmar === '') {
            volverConError('Todos los campos de contraseña son obligatorios.', $destino);
        }

        $usuario = $usuarioModel->buscarPorId($idUsuario);
        $usuarioConHash = $usuarioModel->buscarPorCorreo($usuario['correo'] ?? '');

        if (!$usuarioConHash || !password_verify($passwordActual, $usuarioConHash['contraseña'])) {
            volverConError('La contraseña actual no es correcta.', $destino);
        }

        if ($passwordNueva !== $passwordConfirmar) {
            volverConError('Las contraseñas nuevas no coinciden.', $destino);
        }

        if (!validarComplejidadPassword($passwordNueva)) {
            volverConError(mensajeComplejidadPassword(), $destino);
        }

        $hash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $usuarioModel->actualizarPassword($idUsuario, $hash);
        volverConExito('Contraseña actualizada correctamente.', $destino);
    }
} catch (Throwable $e) {
    volverConError('No fue posible actualizar la información.', $destino);
}