<?php
$pageTitle = 'GEMO | Inicio';
require_once '../../includes/auxzoo_header.php';

$nombreCompleto = trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellido'] ?? ''));
$hora = (int) date('G');
$saludo = $hora < 12 ? 'Buenos días' : ($hora < 19 ? 'Buenas tardes' : 'Buenas noches');
?>
<div class="gemo-welcome-card mb-4">
    <div class="gemo-welcome-text">
        <p class="mb-1 text-uppercase gemo-welcome-eyebrow"><?= htmlspecialchars($saludo) ?></p>
        <h2 class="fw-bold mb-2"><?= htmlspecialchars($nombreCompleto !== '' ? $nombreCompleto : 'Bienvenido') ?></h2>
        <p class="mb-0 opacity-75">Has ingresado como <strong>Auxiliar de zoocriadero</strong> al Sistema de Gestión y Control del Dengue.</p>
    </div>
    <div class="gemo-welcome-icon"><i class="fas fa-fish"></i></div>
</div>

<div class="row">
    <div class="col-sm-6 col-md-6">
        <a href="registrar_seguimiento.php" class="text-decoration-none">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-clipboard-list"></i></div></div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Registrar</p>
                                <h4 class="card-title">Registro diario</h4>
                                <p class="card-category mb-0">Diligencie el seguimiento de hoy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-md-6">
        <a href="mis_registros.php" class="text-decoration-none">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-history"></i></div></div>
                        <div class="col col-stats ms-3 ms-sm-0">
                            <div class="numbers">
                                <p class="card-category">Consultar</p>
                                <h4 class="card-title">Mis registros</h4>
                                <p class="card-category mb-0">Historial de sus registros</p>
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
        <p class="card-text">Desde aquí puede registrar el seguimiento diario del zoocriadero (peces vivos, muertos y condiciones de los tanques) y consultar sus registros anteriores.</p>
        <a href="registrar_seguimiento.php" class="btn btn-outline-success me-2"><i class="fas fa-clipboard-list me-1"></i> Ir a registrar</a>
        <a href="mis_registros.php" class="btn btn-outline-success"><i class="fas fa-history me-1"></i> Ver mis registros</a>
    </div>
</div>
<?php require_once '../../includes/auxzoo_footer.php'; ?>
