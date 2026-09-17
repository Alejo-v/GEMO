<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    $rolId = (int)($_SESSION['usuario_rol_id'] ?? 0);
    $destino = 'index.php';
    if ($rolId === 1) {
        $destino = 'views/admin/dashboard.php';
    } elseif ($rolId === 2) {
        $destino = 'views/coordinador_zoocriadero/inicio.php';
    } elseif ($rolId === 3) {
        $destino = 'views/auxiliar_zoocriadero/inicio.php';
    } elseif ($rolId === 4) {
        $destino = 'views/auxiliar_terreno/inicio.php';
    } elseif ($rolId === 5) {
        $destino = 'views/coordinador_terreno/inicio.php';
    }
    elseif ($rolId === 6) {
        $destino = 'views/super_admin/dashboard.php';
    }
    header('Location: ' . $destino);
    exit;
}
$error = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEMO | Iniciar sesión</title><link rel="stylesheet" href="assets/css/bootstrap.min.css"><link rel="stylesheet" href="assets/css/fonts.min.css"><link rel="stylesheet" href="assets/css/gemo.css"></head><body class="gemo-login"><div class="login-wrap"><div class="login-card"><div class="gemo-login-institucional"><img src="assets/img/branding/alcaldia-escudo.png" alt="Alcaldía de Santiago de Cali"><div class="gemo-login-institucional-texto"><strong>Alcaldía de Santiago de Cali</strong><span>Secretaría de Salud &middot; Subgrupo ETV</span></div></div><div class="text-center mb-4"><img src="assets/img/branding/gemo-logo-color.png" alt="GEMO" class="gemo-mark-img"><h1 class="login-title">Sistema de Gestión y Control del Dengue</h1><p class="text-muted mb-0">Acceso al sistema</p></div><?php if($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?><form method="post" action="controllers/LoginController.php"><div class="mb-3"><label class="form-label">Correo electrónico</label><input class="form-control form-control-lg" type="email" name="correo" required autofocus></div><div class="mb-4"><label class="form-label">Contraseña</label><input class="form-control form-control-lg" type="password" name="password" required></div><button class="btn btn-gemo btn-lg w-100" type="submit">Iniciar sesión</button></form></div></div><div class="gemo-a11y"><button type="button" class="gemo-a11y-toggle" id="gemoA11yToggle" aria-haspopup="true" aria-expanded="false" aria-controls="gemoA11yPanel" title="Accesibilidad"><i class="fas fa-universal-access" aria-hidden="true"></i></button><div class="gemo-a11y-panel" id="gemoA11yPanel" role="dialog" aria-label="Opciones de accesibilidad" hidden><h5>Accesibilidad</h5><div class="gemo-a11y-row"><span>Tamaño de texto</span><div class="gemo-a11y-btns"><button type="button" data-a11y-fs="md">A</button><button type="button" data-a11y-fs="lg">A+</button><button type="button" data-a11y-fs="xl">A++</button></div></div><div class="gemo-a11y-row"><label><input type="checkbox" id="gemoA11yContraste"> Alto contraste</label></div><div class="gemo-a11y-row"><label><input type="checkbox" id="gemoA11ySubrayado"> Subrayar enlaces</label></div><div class="gemo-a11y-row"><button type="button" class="btn btn-sm btn-gemo w-100" id="gemoA11yLeer"><i class="fas fa-volume-up me-1"></i> Leer esta página</button></div><div class="gemo-a11y-row"><button type="button" class="btn btn-sm btn-outline-secondary w-100" id="gemoA11yReset">Restablecer</button></div></div></div><script src="assets/js/accesibilidad.js"></script></body></html>
