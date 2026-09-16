<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../../login.php');
    exit;
}

if ((int)($_SESSION['usuario_rol_id'] ?? 0) !== 6) {
    header('Location: ../../index.php');
    exit;
}
