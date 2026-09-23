<?php

session_start();
require_once __DIR__ . '/../includes/sesion_unica.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/password_validation.php';
require_once __DIR__ . '/../includes/Mailer.php';

if (!isset($_SESSION['usuario_id']) || (int)($_SESSION['usuario_rol_id'] ?? 0) !== 1) {
    header('Location: ../login.php');
    exit;
}

validarSesionUnica();

$accion = $_POST['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !in_array($accion, ['registrar', 'editar', 'cambiar_estado'], true)) {
    header('Location: ../views/admin/usuarios.php');
    exit;
}

if ($accion === 'editar') {
    editarUsuario();
    exit;
}

if ($accion === 'cambiar_estado') {
    cambiarEstadoUsuario();
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
        if (!validarComplejidadPassword($password)) {
            volverAUsuariosConError(mensajeComplejidadPassword());
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
    } catch (PDOException $e) {
        if ($e->getCode() === '23505') {
            volverAUsuariosConError('Ese correo electrónico ya está en uso por otro usuario.');
        }
        die('ERROR REAL: ' . $e->getMessage());
    } catch (Throwable $e) {
        die('ERROR REAL: ' . $e->getMessage());
    }
}

function cambiarEstadoUsuario(): void
{
    $idUsuario = (int) ($_POST['id_usuario'] ?? 0);
    $nuevoEstado = ($_POST['activo'] ?? '') === '1';

    if ($idUsuario <= 0) {
        volverAUsuariosConError('Usuario no válido.');
    }

    if ($idUsuario === (int) ($_SESSION['usuario_id'] ?? 0)) {
        volverAUsuariosConError('No puede inhabilitar su propio usuario.');
    }

    try {
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorId($idUsuario);

        if (!$usuario) {
            volverAUsuariosConError('El usuario indicado no existe.');
        }

        $usuarioModel->cambiarEstado($idUsuario, $nuevoEstado);

        $_SESSION['usuario_exito'] = $nuevoEstado
            ? 'Usuario habilitado correctamente. Ya puede iniciar sesión.'
            : 'Usuario inhabilitado correctamente. Ya no podrá iniciar sesión.';
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

if (!validarComplejidadPassword($password)) {
    volverConError(mensajeComplejidadPassword());
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    volverConError('La fecha de nacimiento no es válida.');
}
$fechaNacimiento = DateTime::createFromFormat('!Y-m-d', $fecha);
$hoy = new DateTime('today');
if (!$fechaNacimiento || $fechaNacimiento->format('Y-m-d') !== $fecha || $fechaNacimiento > $hoy) {
    volverConError('La fecha de nacimiento no es válida o no puede ser futura.');
}

$edad = $fechaNacimiento->diff($hoy)->y;
if ($edad < 18) {
    volverConError('El usuario debe tener al menos 18 años de edad.');
}
if ($edad > 80) {
    volverConError('La fecha de nacimiento no es válida (la edad máxima permitida es 80 años).');
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

    $nombreCompleto = trim($nombres . ' ' . $apellidos);
    $correoEnviado = true;
    try {
        (new Mailer())->enviar(
            $correo,
            $nombreCompleto,
            'Bienvenido a GEMO - Datos de acceso',
            plantillaCorreoBienvenida($nombreCompleto, $correo, $password)
        );
    } catch (Throwable $e) {
        $correoEnviado = false;
    }

    $_SESSION['usuario_exito'] = $correoEnviado
        ? 'Usuario registrado correctamente y se enviaron sus datos de acceso al correo registrado.'
        : 'Usuario registrado correctamente, pero no fue posible enviar el correo de bienvenida. Revise la configuración de correo.';
    header('Location: ../views/admin/usuarios.php');
    exit;
} catch (PDOException $e) {
    if ($e->getCode() === '23505') {
        $mensaje = str_contains($e->getMessage(), 'documento')
            ? 'La cédula ya está registrada.'
            : 'El correo electrónico ya está registrado.';
        volverConError($mensaje);
    }
    die('ERROR REAL: ' . $e->getMessage());
} catch (Throwable $e) {
    die('ERROR REAL: ' . $e->getMessage());
}


function plantillaCorreoBienvenida(string $nombre, string $correo, string $password): string
{
    $nombre = htmlspecialchars($nombre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $correo = htmlspecialchars($correo, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $password = htmlspecialchars($password, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    return '<!doctype html><html lang="es"><body style="margin:0;background:#E6ECF2;font-family:Arial,sans-serif;color:#153D6B;">'
        . '<div style="max-width:600px;margin:30px auto;background:#ffffff;border-radius:16px;padding:35px;">'
        . '<div style="text-align:center;"><h2 style="color:#1D63B3;margin:0 0 20px;">GEMO</h2>'
        . '<h1 style="font-size:24px;margin:0 0 20px;">Bienvenido a GEMO</h1></div>'
        . '<p>Bienvenido/a, <strong>' . $nombre . '</strong>.</p>'
        . '<p>El administrador ha creado su usuario en el Sistema de Gestión y Control del Dengue.</p>'
        . '<p style="margin-top:25px;"><strong>Sus datos de acceso son:</strong></p>'
        . '<div style="background:#E6ECF2;border-radius:12px;padding:20px;line-height:1.8;">'
        . '<strong>Correo:</strong> ' . $correo . '<br>'
        . '<strong>Contraseña:</strong> ' . $password . '</div>'
        . '<p style="margin-top:25px;">Por seguridad, le recomendamos cambiar esta contraseña después de iniciar sesión.</p>'
        . '<p style="margin-top:30px;color:#6b7785;font-size:13px;">GEMO - Sistema de Gestión y Control del Dengue</p>'
        . '</div></body></html>';
}