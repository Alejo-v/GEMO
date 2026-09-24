<?php
require_once __DIR__ . '/auth.php';

$__rolId = (int) ($_SESSION['usuario_rol_id'] ?? 0);
$__perm = static fn(string $pagina): bool => usuarioTienePermiso($__rolId, $pagina);

$__verDashboard = $__perm('dashboard.php');
$__verUsuarios = $__perm('usuarios.php');
$__verRegistrarUsuario = $__perm('registrar_usuario.php');
$__verReportes = $__perm('reportes.php');
$__verReportesZoo = $__perm('reportes_zoocriadero.php');
$__verReportesTerreno = $__perm('reportes_terreno.php');
$__verSitios = $__perm('sitios.php');
$__verDepositos = $__perm('depositos.php');
$__verTiposDeposito = $__perm('tipos_deposito.php');
$__verActividades = $__perm('actividades.php');
$__verZoocriaderos = $__perm('zoocriaderos.php');
$__verTanques = $__perm('tanques.php');
$__verTiposTanque = $__perm('tipos_tanque.php');
$__verAccionesZoo = $__perm('acciones_zoocriadero.php');
$__verInformacionPersonal = $__perm('informacion_personal.php');

$__verSeccionReportes = $__verReportes || $__verReportesZoo || $__verReportesTerreno;
$__verSeccionTerreno = $__verSitios || $__verDepositos || $__verTiposDeposito || $__verActividades;
$__verSeccionZoo = $__verZoocriaderos || $__verTanques || $__verTiposTanque || $__verAccionesZoo;
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'GEMO') ?></title>
<link rel="icon" href="../../assets/img/kaiadmin/favicon.ico">
<link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../../assets/css/plugins.min.css">
<link rel="stylesheet" href="../../assets/css/kaiadmin.min.css">
<link rel="stylesheet" href="../../assets/css/fonts.min.css">
<link rel="stylesheet" href="../../assets/css/gemo.css">
</head>
<body>
<div class="wrapper">
<div class="sidebar gemo-sidebar" data-background-color="dark">
<div class="sidebar-logo">
<div class="logo-header gemo-logo-header">
<a href="dashboard.php" class="logo text-decoration-none">
<img src="../../assets/img/branding/gemo-logo-white.png" alt="GEMO" class="gemo-brand-img">
</a>
<div class="nav-toggle">
<button class="btn btn-toggle toggle-sidebar">
<i class="gg-menu-right"></i>
</button>
<button class="btn btn-toggle sidenav-toggler">
<i class="gg-menu-left"></i>
</button>
</div>
<button class="topbar-toggler more">
<i class="gg-more-vertical-alt"></i>
</button>
</div>
</div>
<div class="sidebar-wrapper scrollbar scrollbar-inner">
<div class="sidebar-content">
<ul class="nav nav-secondary">
<?php if ($__verDashboard): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '' ?>">
<a href="dashboard.php">
<i class="fas fa-home"></i>
<p>Inicio</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verUsuarios): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'usuarios.php' ? 'active' : '' ?>">
<a href="usuarios.php">
<i class="fas fa-users"></i>
<p>Usuarios</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verRegistrarUsuario): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'registrar_usuario.php' ? 'active' : '' ?>">
<a href="registrar_usuario.php">
<i class="fas fa-user-plus"></i>
<p>Registrar usuario</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verSeccionReportes): ?>
<li class="nav-section">
<span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
<h4 class="text-section">Reportes</h4>
</li>
<?php endif; ?>
<?php if ($__verReportes): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'reportes.php' ? 'active' : '' ?>">
<a href="reportes.php">
<i class="fas fa-folder-open"></i>
<p>Centro de reportes</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verReportesZoo): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'reportes_zoocriadero.php' ? 'active' : '' ?>">
<a href="reportes_zoocriadero.php">
<i class="fas fa-fish"></i>
<p>Reportes zoocriadero</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verReportesTerreno): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'reportes_terreno.php' ? 'active' : '' ?>">
<a href="reportes_terreno.php">
<i class="fas fa-map-marked-alt"></i>
<p>Reportes de terreno</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verSeccionTerreno): ?>
<li class="nav-section">
<span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
<h4 class="text-section">Catálogos de terreno</h4>
</li>
<?php endif; ?>
<?php if ($__verSitios): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'sitios.php' ? 'active' : '' ?>">
<a href="sitios.php">
<i class="fas fa-map"></i>
<p>Sitios</p>
</a>
</li>
<?php endif; ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'ubicacion.php' ? 'active' : '' ?>">
<a href="ubicacion.php">
<i class="fas fa-map-marked-alt"></i>
<p>Barrios y comunas</p>
</a>
</li>
<?php if ($__verDepositos): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'depositos.php' ? 'active' : '' ?>">
<a href="depositos.php">
<i class="fas fa-tint"></i>
<p>Depósitos</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verTiposDeposito): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'tipos_deposito.php' ? 'active' : '' ?>">
<a href="tipos_deposito.php">
<i class="fas fa-tags"></i>
<p>Tipos de depósito</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verActividades): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'actividades.php' ? 'active' : '' ?>">
<a href="actividades.php">
<i class="fas fa-tasks"></i>
<p>Actividades</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verSeccionZoo): ?>
<li class="nav-section">
<span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
<h4 class="text-section">Catálogos de zoocriadero</h4>
</li>
<?php endif; ?>
<?php if ($__verZoocriaderos): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'zoocriaderos.php' ? 'active' : '' ?>">
<a href="zoocriaderos.php">
<i class="fas fa-warehouse"></i>
<p>Zoocriaderos</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verTanques): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'tanques.php' ? 'active' : '' ?>">
<a href="tanques.php">
<i class="fas fa-swimming-pool"></i>
<p>Tanques</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verTiposTanque): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'tipos_tanque.php' ? 'active' : '' ?>">
<a href="tipos_tanque.php">
<i class="fas fa-tags"></i>
<p>Tipos de tanque</p>
</a>
</li>
<?php endif; ?>
<?php if ($__verAccionesZoo): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'acciones_zoocriadero.php' ? 'active' : '' ?>">
<a href="acciones_zoocriadero.php">
<i class="fas fa-tasks"></i>
<p>Procesos</p>
</a>
</li>
<?php endif; ?>
<li class="nav-section">
<span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
<h4 class="text-section">Cuenta</h4>
</li>
<?php if ($__verInformacionPersonal): ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'informacion_personal.php' ? 'active' : '' ?>">
<a href="informacion_personal.php">
<i class="fas fa-id-card"></i>
<p>Información personal</p>
</a>
</li>
<?php endif; ?>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'acerca_de.php' ? 'active' : '' ?>">
<a href="acerca_de.php">
<i class="fas fa-info-circle"></i>
<p>Acerca de GEMO</p>
</a>
</li>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'manual_usuario.php' ? 'active' : '' ?>">
<a href="manual_usuario.php">
<i class="fas fa-video"></i>
<p>Manual de usuario</p>
</a>
</li>
<li class="nav-item">
<a href="../../logout.php">
<i class="fas fa-sign-out-alt"></i>
<p>Cerrar sesión</p>
</a>
</li>
</ul>
</div>
</div>
</div>
<div class="main-panel">
<div class="main-header">
<div class="main-header-logo">
<div class="logo-header gemo-logo-header">
<button class="navbar-toggler sidenav-toggler gemo-mobile-toggler" type="button" aria-label="Abrir menú">
<span class="navbar-toggler-icon">
<i class="fas fa-bars"></i>
</span>
</button>
<a href="dashboard.php" class="logo">
<img src="../../assets/img/branding/gemo-logo-white.png" alt="GEMO" class="gemo-top-brand-img">
</a>
</div>
</div>
<nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
<div class="container-fluid">
<ul class="navbar-nav topbar-nav ms-auto align-items-center">
<li class="nav-item">
<span class="navbar-text">
<strong><?= htmlspecialchars($_SESSION['usuario_nombre']) ?></strong>
· <?= htmlspecialchars($_SESSION['usuario_rol']) ?>
</span>
</li>
</ul>
</div>
</nav>
</div>
<div class="container">
<div class="page-inner">
