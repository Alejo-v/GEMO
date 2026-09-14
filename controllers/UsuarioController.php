<?php

session_start();
require_once __DIR__ . '/../models/Usuario.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 1) {
    header('Location: ../login.php');
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($accion, ['registrar', 'editar'], true)) {
    header('Location: ../views/admin/usuarios.php');
    exit;
}

if ($accion === 'editar') {
    editarUsuario();
    exit;
}

function volverConError(string $mensaje): never
{
    $_SESSION['usuario_error'] = $mensaje;
    header('Location: ../views/admin/registrar_usuario.php');
    exit;
}

function volverAUsuariosConError(string $mensaje): never
{
    $_SESSION['usuario_error'] = $mensaje;
    header('Location: ../views/admin/usuarios.php');
    exit;
}

function editarUsuario(): void
{
    $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
    $apellidos = trim($_POST['apellidos'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar = $_POST['confirmar_password'] ?? '';
    $idRol = (int) ($_POST['id_rol'] ?? 0);

    if ($idUsuario <= 0) {
        volverAUsuariosConError('Usuario no válido.');
    }

    if ($apellidos === '' || $correo === '' || $idRol <= 0) {
        volverAUsuariosConError('El apellido, el correo y el rol son obligatorios.');
    }

    if (!preg_match('/^[\p{L}\s\-\']{2,50}$/u', $apellidos)) {
        volverAUsuariosConError('El apellido contiene caracteres no válidos.');
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        volverAUsuariosConError('El correo electrónico no es válido.');
    }

    if ($password !== '' || $confirmar !== '') {
        if ($password !== $confirmar) {
            volverAUsuariosConError('Las contraseñas nuevas no coinciden.');
        }
        if (strlen($password) < 8) {
            volverAUsuariosConError('La nueva contraseña debe tener mínimo 8 caracteres.');
        }
    }

    try {
        $usuarioModel = new Usuario();

        if (!$usuarioModel->buscarPorId($idUsuario)) {
            volverAUsuariosConError('El usuario que intenta editar no existe.');
        }

        if ($usuarioModel->correoExisteEnOtroUsuario($correo, $idUsuario)) {
            volverAUsuariosConError('Ese correo electrónico ya está en uso por otro usuario.');
        }

        $roles = array_map('intval', array_column($usuarioModel->obtenerRoles(), 'id_rol'));
        if (!in_array($idRol, $roles, true)) {
            volverAUsuariosConError('El rol seleccionado no es válido.');
        }

        $usuarioModel->actualizar([
            ':id_usuario' => $idUsuario,
            ':apellidos' => $apellidos,
            ':correo' => $correo,
            ':id_rol' => $idRol,
            ':contrasena' => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null,
        ]);

        if ($idUsuario === (int) ($_SESSION['usuario_id'] ?? 0)) {
            $_SESSION['usuario_apellido'] = $apellidos;
            $_SESSION['usuario_nombre'] = $_SESSION['usuario_nombre'] ?? '';
        }

        $_SESSION['usuario_exito'] = 'Usuario actualizado correctamente.';
        header('Location: ../views/admin/usuarios.php');
        exit;
    } catch (Throwable $e) {
        die('ERROR REAL: ' . $e->getMessage());
    }
}

$nombres = trim($_POST['nombres'] ?? '');
$apellidos = trim($_POST['apellidos'] ?? '');
$documento = trim($_POST['documento'] ?? '');
$fecha = trim($_POST['fecha_nacimiento'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';
$confirmar = $_POST['confirmar_password'] ?? '';
$idRol = (int)($_POST['id_rol'] ?? 0);

if ($nombres === '' || $apellidos === '' || $documento === '' || $fecha === '' || $telefono === '' || $correo === '' || $password === '' || $confirmar === '' || $idRol <= 0) {
    volverConError('Todos los campos son obligatorios.');
}

if (!preg_match('/^[\p{L}\s\-\']{2,50}$/u', $nombres) || !preg_match('/^[\p{L}\s\-\']{2,50}$/u', $apellidos)) {
    volverConError('El nombre y el apellido contienen caracteres no válidos.');
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    volverConError('El correo electrónico no es válido.');
}

if (!preg_match('/^[0-9A-Za-z\-\.]{5,50}$/', $documento)) {
    volverConError('El número de cédula no tiene un formato válido.');
}

if (!preg_match('/^[0-9+\s\-]{7,20}$/', $telefono)) {
    volverConError('El número de teléfono no tiene un formato válido.');
}

if ($password !== $confirmar) {
    volverConError('Las contraseñas no coinciden.');
}

if (strlen($password) < 8) {
    volverConError('La contraseña debe tener mínimo 8 caracteres.');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    volverConError('La fecha de nacimiento no es válida.');
}

try {
    $usuarioModel = new Usuario();

    if ($usuarioModel->correoExiste($correo)) {
        volverConError('El correo electrónico ya está registrado.');
    }

    if ($usuarioModel->documentoExiste($documento)) {
        volverConError('La cédula ya está registrada.');
    }

    $roles = array_column($usuarioModel->obtenerRoles(), 'id_rol');
    if (!in_array($idRol, array_map('intval', $roles), true)) {
        volverConError('El rol seleccionado no es válido.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $usuarioModel->registrar([
        ':id_rol' => $idRol,
        ':documento' => $documento,
        ':nombres' => $nombres,
        ':apellidos' => $apellidos,
        ':id_tipo_documento' => 1,
        ':contrasena' => $hash,
        ':correo' => $correo,
        ':fecha_nacimiento' => $fecha,
        ':telefono' => $telefono,
    ]);

    $_SESSION['usuario_exito'] = 'Usuario registrado correctamente.';
    header('Location: ../views/admin/usuarios.php');
    exit;
} catch (Throwable $e) {
    die('ERROR REAL: ' . $e->getMessage());
}
