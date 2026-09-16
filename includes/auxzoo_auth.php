<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/permisos.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../login.php');
    exit;
}

if ((int)($_SESSION['usuario_rol_id'] ?? 0) !== 3) {
    header('Location: ../../index.php');
    exit;
}

if (!usuarioTienePermiso((int)($_SESSION['usuario_rol_id'] ?? 0), basename($_SERVER['PHP_SELF']))) {
    $_SESSION['error'] = 'No tienes permisos para acceder a esa página.';
    header('Location: ../../index.php');
    exit;
}
