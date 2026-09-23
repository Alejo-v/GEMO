<?php
$pageTitle = 'GEMO | Reportes del zoocriadero';
require_once '../../includes/admin_header.php';
require_once '../../models/SeguimientoZoocriadero.php';
require_once '../../models/Tanque.php';

$hoy = date('Y-m-d');
$filtros = [
    'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days')),
    'fecha_fin' => $_GET['fecha_fin'] ?? $hoy,
    'id_zoocriadero' => $_GET['id_zoocriadero'] ?? '',
    'id_actividad' => $_GET['id_actividad'] ?? '',
];

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtros['fecha_inicio'])) {
    $filtros['fecha_inicio'] = date('Y-m-d', strtotime('-30 days'));
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtros['fecha_fin'])) {
    $filtros['fecha_fin'] = $hoy;
}
if ($filtros['fecha_inicio'] > $filtros['fecha_fin']) {
    [$filtros['fecha_inicio'], $filtros['fecha_fin']] = [$filtros['fecha_fin'], $filtros['fecha_inicio']];
}

$modelo = new SeguimientoZoocriadero();
$modeloTanque = new Tanque();

$zoocriaderos = $modelo->obtenerZoocriaderos();
$actividadesCatalogo = $modelo->obtenerActividades();

$resumen = $modelo->obtenerResumenFiltrado($filtros);
$serie = $modelo->obtenerSerieDiariaFiltrada($filtros);
$registros = $modelo->obtenerRegistrosFiltrados($filtros);
$resumenPorTanque = $modelo->obtenerResumenPorTanque($filtros);
$reporteTanquesZoo = $modeloTanque->obtenerReportePorZoocriadero($filtros['id_zoocriadero']);

$totalVivos = (int) $resumen['total_vivos'];
$totalMuertos = (int) $resumen['total_muertos'];
$totalGeneral = $totalVivos + $totalMuertos;
$tasaMortalidad = $totalGeneral > 0 ? round(($totalMuertos / $totalGeneral) * 100, 1) : 0.0;

$etiquetas = array_map(static fn($f) => date('d/m', strtotime($f['fecha'])), $serie);
$datosVivos = array_map(static fn($f) => (int) $f['vivos'], $serie);
$datosMuertos = array_map(static fn($f) => (int) $f['muertos'], $serie);

$etiquetasTanque = array_map(static fn($t) => '#' . (int) $t['id_tanque'], $resumenPorTanque);
$vivosTanque = array_map(static fn($t) => (int) $t['total_vivos'], $resumenPorTanque);
$muertosTanque = array_map(static fn($t) => (int) $t['total_muertos'], $resumenPorTanque);





function urlPdfZoo(array $filtros, string $reporte, bool $previsualizar = false): string
{
    return '../../controllers/ReporteController.php?accion=pdf'
        . '&reporte=' . urlencode($reporte)
        . '&fecha_inicio=' . urlencode($filtros['fecha_inicio'])
        . '&fecha_fin=' . urlencode($filtros['fecha_fin'])
        . '&id_zoocriadero=' . urlencode($filtros['id_zoocriadero'])
        . '&id_actividad=' . urlencode($filtros['id_actividad'])
        . ($previsualizar ? '&salida=ver' : '');
}
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Reportes del zoocriadero</h3>
        <p class="text-muted mb-0">
            Solo el proceso de Zoocriadero. Los reportes de terreno están en
            <a href="reportes_terreno.php">su propia pantalla</a>.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= htmlspecialchars(urlPdfZoo($filtros, 'todos', true)) ?>" class="btn btn-outline-secondary" target="_blank">
            <i class="fas fa-eye me-1"></i> Previsualizar los 3
        </a>
        <a href="<?= htmlspecialchars(urlPdfZoo($filtros, 'todos')) ?>" class="btn btn-outline-success" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> Descargar los 3 en un PDF
        </a>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Filtros</h4></div>
    <div class="card-body">
        <form method="get" action="reportes_zoocriadero.php" class="row g-3 align-items-end">
            <div class="col-sm-6 col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= htmlspecialchars($filtros['fecha_inicio']) ?>" max="<?= htmlspecialchars($hoy) ?>">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= htmlspecialchars($filtros['fecha_fin']) ?>" max="<?= htmlspecialchars($hoy) ?>">
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label">Zoocriadero</label>
                <select name="id_zoocriadero" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($zoocriaderos as $z): ?>
                        <option value="<?= (int) $z['id_zoocriadero'] ?>" <?= (string) $filtros['id_zoocriadero'] === (string) $z['id_zoocriadero'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($z['direccion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-6 col-md-3">
                <label class="form-label">Actividad</label>
                <select name="id_actividad" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($actividadesCatalogo as $a): ?>
                        <option value="<?= (int) $a['id_actividad_zoocriadero'] ?>" <?= (string) $filtros['id_actividad'] === (string) $a['id_actividad_zoocriadero'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 d-flex gap-2">
                <button type="submit" class="btn btn-gemo"><i class="fas fa-filter me-1"></i> Filtrar</button>
                <a href="reportes_zoocriadero.php" class="btn btn-secondary">Limpiar</a>
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
                    <p class="text-muted mb-0">No hay datos con estos filtros.</p>
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
                    <p class="text-muted mb-0">No hay datos con estos filtros.</p>
                <?php else: ?>
                    <canvas id="chartTotalesZoo" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= Reporte 1 ================= -->
<div class="card card-round mt-3 mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="card-title mb-0">Reporte 1 — Seguimiento de actividades</h4>
            <small class="text-muted">Filtrado por fechas, zoocriadero y actividad</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars(urlPdfZoo($filtros, '1', true)) ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="fas fa-eye me-1"></i> Previsualizar
            </a>
            <a href="<?= htmlspecialchars(urlPdfZoo($filtros, '1')) ?>" class="btn btn-gemo btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Descargar reporte 1
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Zoocriadero</th>
                        <th>Tanque</th>
                        <th>Tipo</th>
                        <th>pH</th>
                        <th>Temp.</th>
                        <th>Cloro</th>
                        <th>Vivos</th>
                        <th>Muertos M/H</th>
                        <th>Actividades</th>
                        <th>Registrado por</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="12" class="text-center text-muted">No hay registros con estos filtros.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) ?></td>
                        <td><?= htmlspecialchars($r['zoocriadero']) ?></td>
                        <td>#<?= (int) $r['id_tanque'] ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($r['tipo_tanque']) ?></span></td>
                        <td><?= htmlspecialchars((string) $r['ph']) ?></td>
                        <td><?= htmlspecialchars((string) $r['temperatura']) ?></td>
                        <td><?= htmlspecialchars((string) $r['cloro']) ?></td>
                        <td><?= (int) $r['alevines_nacidos'] ?></td>
                        <td><?= (int) $r['muertos_macho'] ?> / <?= (int) $r['muertos_hembra'] ?></td>
                        <td><?= htmlspecialchars($r['actividades']) ?></td>
                        <td><?= htmlspecialchars($r['registrado_por']) ?></td>
                        <td><?= htmlspecialchars($r['observaciones'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ================= Reporte 2 ================= -->
<div class="row">
    <div class="col-md-7">
        <div class="card card-round mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h4 class="card-title mb-0">Reporte 2 — Nacidos y muertos por tanque</h4>
                    <small class="text-muted">Cuantificado tanque por tanque</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?= htmlspecialchars(urlPdfZoo($filtros, '2', true)) ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                        <i class="fas fa-eye me-1"></i> Previsualizar
                    </a>
                    <a href="<?= htmlspecialchars(urlPdfZoo($filtros, '2')) ?>" class="btn btn-gemo btn-sm" target="_blank">
                        <i class="fas fa-file-pdf me-1"></i> Descargar reporte 2
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tanque</th>
                                <th>Zoocriadero</th>
                                <th>Tipo</th>
                                <th>Registros</th>
                                <th>Vivos</th>
                                <th>Muertos M/H</th>
                                <th>Total muertos</th>
                                <th>Tasa mortalidad</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($resumenPorTanque)): ?>
                            <tr><td colspan="8" class="text-center text-muted">No hay datos con estos filtros.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($resumenPorTanque as $t):
                            $totalTanque = (int) $t['total_vivos'] + (int) $t['total_muertos'];
                            $tasaTanque = $totalTanque > 0 ? round(((int) $t['total_muertos'] / $totalTanque) * 100, 1) : 0.0;
                        ?>
                            <tr>
                                <td>#<?= (int) $t['id_tanque'] ?></td>
                                <td><?= htmlspecialchars($t['zoocriadero']) ?></td>
                                <td><span class="badge badge-success"><?= htmlspecialchars($t['tipo_tanque']) ?></span></td>
                                <td><?= (int) $t['total_registros'] ?></td>
                                <td><?= (int) $t['total_vivos'] ?></td>
                                <td><?= (int) $t['total_muertos_macho'] ?> / <?= (int) $t['total_muertos_hembra'] ?></td>
                                <td><?= (int) $t['total_muertos'] ?></td>
                                <td><?= $tasaTanque ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card card-round mb-4">
            <div class="card-header"><h4 class="card-title">Vivos vs. muertos por tanque</h4></div>
            <div class="card-body">
                <?php if (empty($resumenPorTanque)): ?>
                    <p class="text-muted mb-0">No hay datos para graficar con estos filtros.</p>
                <?php else: ?>
                    <canvas id="chartPorTanque" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ================= Reporte 3 ================= -->
<div class="card card-round mb-4">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h4 class="card-title mb-0">Reporte 3 — Tanques por zoocriadero</h4>
            <small class="text-muted">Cantidad de tanques, tipo de tanque y encargado</small>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= htmlspecialchars(urlPdfZoo($filtros, '3', true)) ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                <i class="fas fa-eye me-1"></i> Previsualizar
            </a>
            <a href="<?= htmlspecialchars(urlPdfZoo($filtros, '3')) ?>" class="btn btn-gemo btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Descargar reporte 3
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Zoocriadero</th>
                        <th>Encargado</th>
                        <th>Total tanques</th>
                        <th>Activos</th>
                        <th>Mantenimiento</th>
                        <th>Fuera de servicio</th>
                        <th>Detalle por tipo de tanque</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reporteTanquesZoo)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No hay zoocriaderos registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($reporteTanquesZoo as $zoo): ?>
                    <tr>
                        <td><?= htmlspecialchars($zoo['direccion']) ?></td>
                        <td><?= htmlspecialchars($zoo['encargado']) ?></td>
                        <td><?= (int) $zoo['total_tanques'] ?></td>
                        <td><?= (int) $zoo['tanques_activos'] ?></td>
                        <td><?= (int) $zoo['tanques_mantenimiento'] ?></td>
                        <td><?= (int) $zoo['tanques_fuera_servicio'] ?></td>
                        <td>
                            <?php if (empty($zoo['detalle_tipos'])): ?>
                                <span class="text-muted">Sin tanques registrados</span>
                            <?php else: ?>
                                <?php foreach ($zoo['detalle_tipos'] as $detalle): ?>
                                    <span class="badge badge-success me-1"><?= htmlspecialchars($detalle) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if (!empty($serie) || $totalGeneral > 0 || !empty($resumenPorTanque)): ?>
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
                        backgroundColor: '#1D63B3',
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
                    backgroundColor: ['#1D63B3', '#f25961']
                }]
            },
            options: {
                responsive: true,
                legend: { position: 'bottom' }
            }
        });
    }
    <?php endif; ?>

    <?php if (!empty($resumenPorTanque)): ?>
    var ctxTanque = document.getElementById('chartPorTanque');
    if (ctxTanque) {
        new Chart(ctxTanque, {
            type: 'bar',
            data: {
                labels: <?= json_encode($etiquetasTanque, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [
                    {
                        label: 'Vivos',
                        backgroundColor: '#1D63B3',
                        data: <?= json_encode($vivosTanque) ?>
                    },
                    {
                        label: 'Muertos',
                        backgroundColor: '#f25961',
                        data: <?= json_encode($muertosTanque) ?>
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
})();
</script>
<?php endif; ?>

<?php require_once '../../includes/admin_footer.php'; ?>
