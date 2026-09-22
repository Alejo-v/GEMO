<?php require_once __DIR__ . '/auxzoo_auth.php'; ?>
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
<a href="inicio.php" class="logo text-decoration-none">
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
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'inicio.php' ? 'active' : '' ?>">
<a href="inicio.php">
<i class="fas fa-home"></i>
<p>Inicio</p>
</a>
</li>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'registrar_seguimiento.php' ? 'active' : '' ?>">
<a href="registrar_seguimiento.php">
<i class="fas fa-clipboard-list"></i>
<p>Registro diario</p>
</a>
</li>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'mis_registros.php' ? 'active' : '' ?>">
<a href="mis_registros.php">
<i class="fas fa-history"></i>
<p>Mis registros</p>
</a>
</li>
<li class="nav-section">
<span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
<h4 class="text-section">Cuenta</h4>
</li>
<li class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'informacion_personal.php' ? 'active' : '' ?>">
<a href="informacion_personal.php">
<i class="fas fa-id-card"></i>
<p>Información personal</p>
</a>
</li>
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
<a href="inicio.php" class="logo">
<img src="../../assets/img/branding/gemo-logo-white.png" alt="GEMO" class="gemo-top-brand-img">
</a>
</div>
</div>
<nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
<div class="container-fluid">
<ul class="navbar-nav topbar-nav ms-auto align-items-center">
<li class="nav-item">
<span class="navbar-text">
<strong><?= htmlspecialchars(trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellido'] ?? ''))) ?></strong>
· <?= htmlspecialchars($_SESSION['usuario_rol'] ?? '') ?>
</span>
</li>
</ul>
</div>
</nav>
</div>
<div class="container">
<div class="page-inner">
