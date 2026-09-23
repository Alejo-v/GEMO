<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    require_once __DIR__ . '/models/Usuario.php';
    try {
        (new Usuario())->actualizarTokenSesion((int) $_SESSION['usuario_id'], null);
    } catch (Throwable $e) {
        // si falla, de todas formas se cierra la sesión local
    }
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
header('Location: login.php');
exit;
