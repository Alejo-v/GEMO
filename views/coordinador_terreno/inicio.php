<?php
$pageTitle = 'GEMO | Inicio';
require_once '../../includes/coordterreno_header.php';

$nombreCompleto = trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellido'] ?? ''));
?>
<div class="gemo-welcome-card mb-4">
    <div class="gemo-welcome-text">
        <p class="mb-1 text-uppercase gemo-welcome-eyebrow">Bienvenido a GEMO</p>
        <h2 class="fw-bold mb-2"><?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'Bienvenido') ?></h2>
        <p class="mb-0 opacity-75">Has ingresado como <strong>Coordinador de terreno</strong> al Sistema de Gestión y Control del Dengue.</p>
    </div>
    <div class="gemo-welcome-icon"><i class="fas fa-map-marker-alt"></i></div>
</div>

<div class="row">
    <div class="col-sm-12 col-md-12">
        <a href="reportes.php" class="text-decoration-none">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-chart-bar"></i></div></div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Consultar</p>
                                <h4 class="card-title">Reportes y gráficos</h4>
                                <p class="card-category mb-0">Sitios, actividades, tipos de depósito y descarga de PDF con filtros</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="row mt-2">
    <div class="col-sm-6 col-md-3">
        <a href="sitios.php" class="text-decoration-none">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-map"></i></div></div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Administrar</p>
                                <h4 class="card-title">Sitios</h4>
                                <p class="card-category mb-0">Lugares de control biológico</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="depositos.php" class="text-decoration-none">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-water"></i></div></div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Administrar</p>
                                <h4 class="card-title">Depósitos</h4>
                                <p class="card-category mb-0">Depósitos de agua por sitio</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="tipos_deposito.php" class="text-decoration-none">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-tags"></i></div></div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Administrar</p>
                                <h4 class="card-title">Tipos de depósito</h4>
                                <p class="card-category mb-0">Catálogo de tipos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-3">
        <a href="actividades.php" class="text-decoration-none">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-secondary bubble-shadow-small"><i class="fas fa-tasks"></i></div></div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Administrar</p>
                                <h4 class="card-title">Actividades</h4>
                                <p class="card-category mb-0">Pasos del protocolo</p>
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
        <p class="card-text">Desde aquí puede administrar los catálogos de terreno (sitios, depósitos, tipos de depósito y actividades) y consultar los reportes del trabajo realizado por los auxiliares: sitios y depósitos intervenidos, actividades realizadas, producción por auxiliar y distribución por tipo de depósito. Puede filtrar por comuna, barrio, tipo de depósito y fechas, y descargar el reporte en PDF.</p>
        <a href="reportes.php" class="btn btn-outline-success me-2"><i class="fas fa-chart-bar me-1"></i> Ver reportes</a>
        <a href="sitios.php" class="btn btn-outline-success me-2"><i class="fas fa-map me-1"></i> Sitios</a><a href="ubicacion.php" class="btn btn-outline-success me-2"><i class="fas fa-map-marked-alt me-1"></i> Barrios y comunas</a>
        <a href="depositos.php" class="btn btn-outline-success"><i class="fas fa-water me-1"></i> Depósitos</a>
    </div>
</div>
<?php require_once '../../includes/coordterreno_footer.php'; ?>
