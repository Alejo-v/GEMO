<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
$error = $_SESSION['recuperacion_error'] ?? null;
unset($_SESSION['recuperacion_error']);
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>GEMO | Recuperar contraseña</title><link rel="stylesheet" href="assets/css/bootstrap.min.css"><link rel="stylesheet" href="assets/css/fonts.min.css"><link rel="stylesheet" href="assets/css/gemo.css"></head><body class="gemo-login"><div class="login-wrap"><div class="login-card"><div class="gemo-login-institucional"><img src="assets/img/branding/alcaldia-escudo.png" alt="Alcaldía de Santiago de Cali"><div class="gemo-login-institucional-texto"><strong>Alcaldía de Santiago de Cali</strong><span>Secretaría de Salud &middot; Subgrupo ETV</span></div></div><div class="text-center mb-4"><img src="assets/img/branding/gemo-logo-color.png" alt="GEMO" class="gemo-mark-img"><h1 class="login-title">Recuperar contraseña</h1><p class="text-muted mb-0">Ingrese el correo registrado en GEMO</p></div><?php if($error): ?><div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div><?php endif; ?><form method="post" action="controllers/RecuperacionController.php"><input type="hidden" name="accion" value="solicitar"><div class="mb-4"><label class="form-label">Correo electrónico</label><input class="form-control form-control-lg" type="email" name="correo" maxlength="150" required autofocus></div><button class="btn btn-gemo btn-lg w-100" type="submit">Enviar código</button><a href="login.php" class="btn btn-outline-secondary btn-lg w-100 mt-2">Volver al inicio de sesión</a></form></div></div></body></html>
