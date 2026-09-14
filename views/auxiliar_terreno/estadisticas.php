<?php
$pageTitle = 'GEMO | Estadísticas de terreno';
require_once '../../includes/auxterreno_header.php';
require_once '../../models/SeguimientoTerreno.php';

$modelo = new SeguimientoTerreno();
$idUsuario = (int) $_SESSION['usuario_id'];

$porTipoDeposito = $modelo->obtenerConteoPorTipoDeposito($idUsuario);
$porActividad = $modelo->obtenerConteoPorActividad($idUsuario);

$totalRegistros = array_sum(array_column($porActividad, 'total'));
?>
<div class="mb-4">
    <h3 class="fw-bold mb-1">Mis estadísticas de terreno</h3>
    <p class="text-muted mb-0">Resumen de las visitas que has registrado, agrupadas por tipo de depósito y por actividad.</p>
</div>

<div class="row">
    <div class="col-sm-6 col-md-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-clipboard-check"></i></div>
                    </div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Total</p>
                            <h4 class="card-title"><?= (int) $totalRegistros ?></h4>
                            <p class="card-category mb-0">Registros realizados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-round mb-4">
            <div class="card-header"><h4 class="card-title">Registros por tipo de depósito</h4></div>
            <div class="card-body">
                <?php if (empty($porTipoDeposito)): ?>
                    <p class="text-muted mb-0">Aún no hay registros para graficar.</p>
                <?php else: ?>
                    <canvas id="graficoTipoDeposito" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-round mb-4">
            <div class="card-header"><h4 class="card-title">Registros por actividad</h4></div>
            <div class="card-body">
                <?php if (empty($porActividad)): ?>
                    <p class="text-muted mb-0">Aún no hay registros para graficar.</p>
                <?php else: ?>
                    <canvas id="graficoActividad" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="../../assets/js/plugin/chart.js/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var datosTipoDeposito = <?= json_encode($porTipoDeposito) ?>;
    var datosActividad = <?= json_encode($porActividad) ?>;

    var canvasTipo = document.getElementById('graficoTipoDeposito');
    if (canvasTipo) {
        new Chart(canvasTipo.getContext('2d'), {
            type: 'bar',
            data: {
                labels: datosTipoDeposito.map(function (d) { return d.etiqueta; }),
                datasets: [{
                    label: 'Registros',
                    data: datosTipoDeposito.map(function (d) { return d.total; }),
                    backgroundColor: '#2f855a'
                }]
            },
            options: {
                legend: { display: false },
                scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] }
            }
        });
    }

    var canvasActividad = document.getElementById('graficoActividad');
    if (canvasActividad) {
        new Chart(canvasActividad.getContext('2d'), {
            type: 'bar',
            data: {
                labels: datosActividad.map(function (d) { return d.etiqueta; }),
                datasets: [{
                    label: 'Registros',
                    data: datosActividad.map(function (d) { return d.total; }),
                    backgroundColor: '#14532d'
                }]
            },
            options: {
                legend: { display: false },
                scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] }
            }
        });
    }
});
</script>

<?php require_once '../../includes/auxterreno_footer.php'; ?>
