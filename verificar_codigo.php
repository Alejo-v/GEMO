<?php
session_start();

if (empty($_SESSION['recuperacion_correo'])) {
    header('Location: recuperar_contrasena.php');
    exit;
}

$error  = $_SESSION['recuperacion_error_codigo'] ?? null;
$exito  = $_SESSION['recuperacion_exito']         ?? null;
unset($_SESSION['recuperacion_error_codigo'], $_SESSION['recuperacion_exito']);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>GEMO | Verificar código</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/fonts.min.css">
    <link rel="stylesheet" href="assets/css/gemo.css">
</head>
<body class="gemo-login">

    <div class="login-wrap">
        <div class="login-card">

            <div class="gemo-login-institucional">
                <img src="assets/img/branding/alcaldia-escudo.png" alt="Alcaldía de Santiago de Cali">
                <div class="gemo-login-institucional-texto">
                    <strong>Alcaldía de Santiago de Cali</strong>
                    <span>Secretaría de Salud &middot; Subgrupo ETV</span>
                </div>
            </div>

            <div class="text-center mb-4">
                <img src="assets/img/branding/gemo-logo-color.png" alt="GEMO" class="gemo-mark-img">
                <h1 class="login-title">Verificación de código</h1>
                <p class="text-muted mb-0">Revise su correo e ingrese el código de 6 dígitos.</p>
            </div>

            <?php if ($exito): ?>
                <div class="alert alert-success py-2">
                    <?= htmlspecialchars($exito) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="controllers/RecuperacionController.php">
                <input type="hidden" name="accion" value="verificar">

                <div class="mb-4">
                    <label class="form-label">Código de verificación</label>
                    <input
                        class="form-control form-control-lg text-center"
                        type="text"
                        name="codigo"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        autocomplete="one-time-code"
                        required
                        autofocus
                    >
                </div>

                <button class="btn btn-gemo btn-lg w-100" type="submit">
                    Verificar código
                </button>

                <a href="recuperar_contrasena.php" class="btn btn-outline-secondary btn-lg w-100 mt-2">
                    Solicitar otro código
                </a>
            </form>

        </div>
    </div>

</body>
</html>