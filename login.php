<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    $rolId = (int)($_SESSION['usuario_rol_id'] ?? 0);
    $destino = 'index.php';
    if ($rolId === 1) {
        $destino = 'views/admin/dashboard.php';
    } elseif ($rolId === 3) {
        $destino = 'views/auxiliar_zoocriadero/registrar_seguimiento.php';
    } elseif ($rolId === 4) {
        $destino = 'views/auxiliar_terreno/registrar_seguimiento.php';
    }
    header('Location: ' . $destino);
    exit;
}
$error = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEMO | Iniciar sesión</title><link rel="stylesheet" href="assets/css/bootstrap.min.css"><link rel="stylesheet" href="assets/css/gemo.css"></head><body class="gemo-login"><div class="login-wrap"><div class="login-card"><div class="text-center mb-4"><div class="gemo-mark">GEMO</div><h1 class="login-title">Sistema de Gestión y Control del Dengue</h1><p class="text-muted mb-0">Acceso al sistema</p></div><?php if($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?><form method="post" action="controllers/LoginController.php"><div class="mb-3"><label class="form-label">Correo electrónico</label><input class="form-control form-control-lg" type="email" name="correo" required autofocus></div><div class="mb-4"><label class="form-label">Contraseña</label><input class="form-control form-control-lg" type="password" name="password" required></div><button class="btn btn-gemo btn-lg w-100" type="submit">Iniciar sesión</button></form></div></div></body></html>
