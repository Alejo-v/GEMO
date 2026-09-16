<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php'); exit;
}
if ((int)($_SESSION['usuario_rol_id'] ?? 0) === 1) {
    header('Location: views/admin/dashboard.php'); exit;
}
if ((int)($_SESSION['usuario_rol_id'] ?? 0) === 2) {
    header('Location: views/coordinador_zoocriadero/inicio.php'); exit;
}
if ((int)($_SESSION['usuario_rol_id'] ?? 0) === 3) {
    header('Location: views/auxiliar_zoocriadero/inicio.php'); exit;
}
if ((int)($_SESSION['usuario_rol_id'] ?? 0) === 4) {
    header('Location: views/auxiliar_terreno/inicio.php'); exit;
}
if ((int)($_SESSION['usuario_rol_id'] ?? 0) === 5) {
    header('Location: views/coordinador_terreno/inicio.php'); exit;
}

$nombreCompleto = trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellido'] ?? ''));
$rol = $_SESSION['usuario_rol'] ?? '';
$hora = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>GEMO | Inicio</title>
<link rel="icon" href="assets/img/kaiadmin/favicon.ico">
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/plugins.min.css">
<link rel="stylesheet" href="assets/css/kaiadmin.min.css">
<link rel="stylesheet" href="assets/css/gemo.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar gemo-sidebar" data-background-color="dark">
        <div class="sidebar-logo">
            <div class="logo-header gemo-logo-header">
                <a href="index.php" class="logo text-decoration-none"><span class="gemo-sidebar-brand">GEMO</span></a>
                <div class="nav-toggle">
                    <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
                    <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
                </div>
                <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
            </div>
        </div>
        <div class="sidebar-wrapper scrollbar scrollbar-inner">
            <div class="sidebar-content">
                <ul class="nav nav-secondary">
                    <li class="nav-item active"><a href="index.php"><i class="fas fa-home"></i><p>Inicio</p></a></li>
                    <li class="nav-item"><a href="logout.php"><i class="fas fa-sign-out-alt"></i><p>Cerrar sesión</p></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="main-panel">
        <div class="main-header">
            <div class="main-header-logo">
                <div class="logo-header gemo-logo-header">
                    <a href="index.php" class="logo"><span class="gemo-top-brand">GEMO</span></a>
                </div>
            </div>
            <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
                <div class="container-fluid">
                    <ul class="navbar-nav topbar-nav ms-auto align-items-center">
                        <li class="nav-item"><span class="navbar-text"><strong><?= htmlspecialchars($nombreCompleto) ?></strong> · <?= htmlspecialchars($rol) ?></span></li>
                    </ul>
                </div>
            </nav>
        </div>
        <div class="container">
            <div class="page-inner">

                <div class="gemo-welcome-card mb-4">
                    <div class="gemo-welcome-text">
                        <p class="mb-1 text-uppercase gemo-welcome-eyebrow"><?= htmlspecialchars($saludo) ?></p>
                        <h2 class="fw-bold mb-2"><?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'Bienvenido') ?></h2>
                        <p class="mb-0 opacity-75">Has ingresado como <strong><?= htmlspecialchars($rol) ?></strong> al Sistema de Gestión y Control del Dengue.</p>
                    </div>
                    <div class="gemo-welcome-icon"><i class="fas fa-shield-alt"></i></div>
                </div>

                <div class="row">
                    <div class="col-sm-6 col-md-4">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-fish"></i></div></div>
                                    <div class="col col-stats ms-3 ms-sm-0">
                                        <div class="numbers">
                                            <p class="card-category">Zoocriadero</p>
                                            <h4 class="card-title">Peces</h4>
                                            <p class="card-category mb-0">Control de tanques, nacimientos y mortalidad</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-bug"></i></div></div>
                                    <div class="col col-stats ms-3 ms-sm-0">
                                        <div class="numbers">
                                            <p class="card-category">Terreno</p>
                                            <h4 class="card-title">Sitios</h4>
                                            <p class="card-category mb-0">Depósitos y sitios que requieren intervención</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="card card-stats card-round">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-shield-alt"></i></div></div>
                                    <div class="col col-stats ms-3 ms-sm-0">
                                        <div class="numbers">
                                            <p class="card-category">Acceso</p>
                                            <h4 class="card-title"><?= htmlspecialchars($rol) ?></h4>
                                            <p class="card-category mb-0">Rol asignado por el administrador</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-round mt-3">
                    <div class="card-body">
                        <h4 class="card-title">Tu módulo está en camino</h4>
                        <p class="card-text">GEMO gestiona el control del dengue en dos frentes: el <strong>zoocriadero</strong>, donde se hace seguimiento diario a los peces vivos y muertos y a las condiciones de los tanques, y el <strong>terreno</strong>, donde se registran los sitios y depósitos afectados por mosquitos que requieren intervención. Las pantallas de captura y consulta para tu rol se habilitarán en las próximas fases del proyecto.</p>
                        <p class="card-text mb-0 text-muted">Si necesitas acceso anticipado a algún módulo, comunícate con el administrador del sistema.</p>
                    </div>
                </div>

            </div>
        </div>
        <footer class="footer">
            <div class="container-fluid">
                <div class="copyright text-center w-100">GEMO · Sistema de Gestión y Control del Dengue</div>
            </div>
        </footer>
    </div>
</div>
<script src="assets/js/core/jquery-3.7.1.min.js"></script>
<script src="assets/js/core/popper.min.js"></script>
<script src="assets/js/core/bootstrap.min.js"></script>
<script src="assets/js/kaiadmin.min.js"></script>
<script src="assets/js/gemo.js"></script>
</body>
</html>
