<?php
$pageTitle = 'GEMO | Inicio';
require_once '../../includes/coordzoo_header.php';

$nombreCompleto = trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellido'] ?? ''));
$hora = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
?>
<div class="gemo-welcome-card mb-4">
    <div class="gemo-welcome-text">
        <p class="mb-1 text-uppercase gemo-welcome-eyebrow"><?= htmlspecialchars($saludo) ?></p>
        <h2 class="fw-bold mb-2"><?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'Bienvenido') ?></h2>
        <p class="mb-0 opacity-75">Has ingresado como <strong>Coordinador de zoocriadero</strong> al Sistema de Gestión y Control del Dengue.</p>
    </div>
    <div class="gemo-welcome-icon"><i class="fas fa-fish"></i></div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-12">
        <a href="reportes.php" class="text-decoration-none">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-chart-bar"></i></div></div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Consultar</p>
                                <h4 class="card-title">Reportes y gráficos</h4>
                                <p class="card-category mb-0">Producción, mortalidad y descarga de PDF por rango de fechas</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card card-round mt-3">
    <div class="card-body">
        <h4 class="card-title">Bienvenido a GEMO</h4>
        <p class="card-text">Desde aquí puede consultar los indicadores del zoocriadero (peces vivos, muertos y tasa de mortalidad), visualizar los gráficos por rango de fechas y descargar el reporte en PDF para compartirlo o archivarlo.</p>
        <a href="reportes.php" class="btn btn-outline-success"><i class="fas fa-chart-bar me-1"></i> Ver reportes</a>
    </div>
</div>
<?php require_once '../../includes/coordzoo_footer.php'; ?>
