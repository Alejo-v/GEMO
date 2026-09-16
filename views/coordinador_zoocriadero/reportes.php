<?php
$pageTitle = 'GEMO | Reportes';
require_once '../../includes/coordzoo_header.php';
require_once '../../models/SeguimientoZoocriadero.php';

$hoy = date('Y-m-d');
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fechaFin = $_GET['fecha_fin'] ?? $hoy;

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
    $fechaInicio = date('Y-m-d', strtotime('-30 days'));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
    $fechaFin = $hoy;
}
if ($fechaInicio > $fechaFin) {
    [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
}

$modelo = new SeguimientoZoocriadero();
$resumen = $modelo->obtenerResumenPorRango($fechaInicio, $fechaFin);
$serie = $modelo->obtenerSerieDiariaPorRango($fechaInicio, $fechaFin);
$registros = $modelo->obtenerRegistrosPorRango($fechaInicio, $fechaFin);

$totalVivos = (int) $resumen['total_vivos'];
$totalMuertos = (int) $resumen['total_muertos'];
$totalGeneral = $totalVivos + $totalMuertos;
$tasaMortalidad = $totalGeneral > 0 ? round(($totalMuertos / $totalGeneral) * 100, 1) : 0.0;

$etiquetas = array_map(static fn($f) => date('d/m', strtotime($f['fecha'])), $serie);
$datosVivos = array_map(static fn($f) => (int) $f['vivos'], $serie);
$datosMuertos = array_map(static fn($f) => (int) $f['muertos'], $serie);

$urlPdf = '../../controllers/ReporteController.php?accion=pdf'
    . '&fecha_inicio=' . urlencode($fechaInicio)
    . '&fecha_fin=' . urlencode($fechaFin);
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Reportes del zoocriadero</h3>
        <p class="text-muted mb-0">Consulta y descarga en PDF la producción y mortalidad de peces por rango de fechas.</p>
    </div>
    <a href="<?= htmlspecialchars($urlPdf) ?>" class="btn btn-gemo" target="_blank">
        <i class="fas fa-file-pdf me-1"></i> Descargar PDF
    </a>
</div>

<div class="card card-round mb-4">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($fechaInicio) ?>" max="<?= htmlspecialchars($hoy) ?>">
            </div>
            <div class="col-sm-4 col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($fechaFin) ?>" max="<?= htmlspecialchars($hoy) ?>">
            </div>
            <div class="col-sm-4 col-md-3">
                <button type="submit" class="btn btn-outline-success w-100"><i class="fas fa-filter me-1"></i> Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-clipboard-list"></i></div></div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Periodo</p>
                            <h4 class="card-title"><?= (int) $resumen['total_registros'] ?></h4>
                            <p class="card-category mb-0">Registros</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-fish"></i></div></div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Producción</p>
                            <h4 class="card-title"><?= $totalVivos ?></h4>
                            <p class="card-category mb-0">Peces vivos (alevines)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-danger bubble-shadow-small"><i class="fas fa-skull"></i></div></div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Mortalidad</p>
                            <h4 class="card-title"><?= $totalMuertos ?></h4>
                            <p class="card-category mb-0">Peces muertos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-percentage"></i></div></div>
                    <div class="col col-stats ms-3 ms-sm-0">
                        <div class="numbers">
                            <p class="card-category">Tasa</p>
                            <h4 class="card-title"><?= $tasaMortalidad ?>%</h4>
                            <p class="card-category mb-0">Mortalidad sobre el total</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card card-round">
            <div class="card-header"><h4 class="card-title">Peces vivos vs. muertos por día</h4></div>
            <div class="card-body">
                <?php if (empty($serie)): ?>
                    <p class="text-muted mb-0">No hay datos para el rango de fechas seleccionado.</p>
                <?php else: ?>
                    <canvas id="chartSerieZoo" height="120"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-round">
            <div class="card-header"><h4 class="card-title">Totales del periodo</h4></div>
            <div class="card-body">
                <?php if ($totalGeneral === 0): ?>
                    <p class="text-muted mb-0">No hay datos para el rango de fechas seleccionado.</p>
                <?php else: ?>
                    <canvas id="chartTotalesZoo" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mt-3">
    <div class="card-header"><h4 class="card-title">Detalle de seguimientos</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tanque</th>
                        <th>Tipo</th>
                        <th>pH</th>
                        <th>Temp.</th>
                        <th>Cloro</th>
                        <th>Vivos</th>
                        <th>Muertos M/H</th>
                        <th>Registrado por</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="10" class="text-center text-muted">No hay registros en el rango de fechas seleccionado.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) ?></td>
                        <td>#<?= (int) $r['id_tanque'] ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($r['tipo_tanque']) ?></span></td>
                        <td><?= htmlspecialchars((string) $r['ph']) ?></td>
                        <td><?= htmlspecialchars((string) $r['temperatura']) ?></td>
                        <td><?= htmlspecialchars((string) $r['cloro']) ?></td>
                        <td><?= (int) $r['alevines_nacidos'] ?></td>
                        <td><?= (int) $r['muertos_macho'] ?> / <?= (int) $r['muertos_hembra'] ?></td>
                        <td><?= htmlspecialchars($r['registrado_por']) ?></td>
                        <td><?= htmlspecialchars($r['observaciones'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($serie) || $totalGeneral > 0): ?>
<script src="../../assets/js/plugin/chart.js/chart.min.js"></script>
<script>
(function () {
    <?php if (!empty($serie)): ?>
    var ctxSerie = document.getElementById('chartSerieZoo');
    if (ctxSerie) {
        new Chart(ctxSerie, {
            type: 'bar',
            data: {
                labels: <?= json_encode($etiquetas, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [
                    {
                        label: 'Peces vivos (alevines)',
                        backgroundColor: '#2f855a',
                        data: <?= json_encode($datosVivos) ?>
                    },
                    {
                        label: 'Peces muertos',
                        backgroundColor: '#f25961',
                        data: <?= json_encode($datosMuertos) ?>
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }]
                }
            }
        });
    }
    <?php endif; ?>

    <?php if ($totalGeneral > 0): ?>
    var ctxTotales = document.getElementById('chartTotalesZoo');
    if (ctxTotales) {
        new Chart(ctxTotales, {
            type: 'doughnut',
            data: {
                labels: ['Peces vivos', 'Peces muertos'],
                datasets: [{
                    data: [<?= $totalVivos ?>, <?= $totalMuertos ?>],
                    backgroundColor: ['#2f855a', '#f25961']
                }]
            },
            options: {
                responsive: true,
                legend: { position: 'bottom' }
            }
        });
    }
    <?php endif; ?>
})();
</script>
<?php endif; ?>

<?php require_once '../../includes/coordzoo_footer.php'; ?>
