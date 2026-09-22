<?php
$pageTitle = 'GEMO | Reportes de terreno';
require_once '../../includes/auxterreno_header.php';
require_once '../../models/ReporteTerreno.php';

$modelo = new ReporteTerreno();

$filtros = [
    'id_comuna' => $_GET['id_comuna'] ?? '',
    'id_barrio' => $_GET['id_barrio'] ?? '',
    'id_tipo_deposito' => $_GET['id_tipo_deposito'] ?? '',
    'fecha_desde' => $_GET['fecha_desde'] ?? '',
    'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
];

$comunas = $modelo->obtenerComunas();
$barrios = $modelo->obtenerBarrios();
$tipos = $modelo->obtenerTiposDeposito();

$reporteSitios = $modelo->reporteSitios($filtros);
$reporteActividad = $modelo->reportePorActividad($filtros);
$reporteAuxiliar = $modelo->reportePorAuxiliar($filtros);
$reporteTipoDeposito = $modelo->reportePorTipoDeposito($filtros);

$totalAedes = array_sum(array_column($reporteSitios, 'larvas_aedes'));
$totalPupas = array_sum(array_column($reporteSitios, 'pupas'));
$totalCulex = array_sum(array_column($reporteSitios, 'larvas_culex'));
?>
<div class="mb-4">
    <h3 class="fw-bold mb-1">Reportes de terreno</h3>
    <p class="text-muted mb-0">Los 4 reportes del proceso de Trabajo de Terreno, con filtros por comuna, barrio, tipo de depósito y fechas.</p>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Filtros</h4></div>
    <div class="card-body">
        <form method="get" action="reportes.php" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Comuna</label>
                <select name="id_comuna" id="f-comuna" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($comunas as $c): ?>
                        <option value="<?= (int)$c['id_comuna'] ?>" <?= (string)$filtros['id_comuna'] === (string)$c['id_comuna'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Barrio</label>
                <select name="id_barrio" id="f-barrio" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($barrios as $b): ?>
                        <option value="<?= (int)$b['id_barrio'] ?>" data-comuna="<?= (int)$b['id_comuna'] ?>"
                                <?= (string)$filtros['id_barrio'] === (string)$b['id_barrio'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo de depósito</label>
                <select name="id_tipo_deposito" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($tipos as $t): ?>
                        <option value="<?= (int)$t['id_tipo_deposito'] ?>" <?= (string)$filtros['id_tipo_deposito'] === (string)$t['id_tipo_deposito'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['descripcion']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($filtros['fecha_desde']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($filtros['fecha_hasta']) ?>">
            </div>
            <div class="col-md-9 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-gemo"><i class="fas fa-filter me-1"></i> Aplicar filtros</button>
                <a href="reportes.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-sm-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="numbers"><p class="card-category">Larvas Aedes</p><h4 class="card-title"><?= (int)$totalAedes ?></h4></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="numbers"><p class="card-category">Pupas</p><h4 class="card-title"><?= (int)$totalPupas ?></h4></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="numbers"><p class="card-category">Larvas Culex</p><h4 class="card-title"><?= (int)$totalCulex ?></h4></div>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Reporte 1 · Información de sitios</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th><th>Sitio</th><th>Barrio</th><th>Comuna</th><th>Tipo depósito</th>
                        <th>Actividad</th><th>Aedes</th><th>Pupas</th><th>Culex</th><th>Auxiliar</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reporteSitios)): ?>
                    <tr><td colspan="10" class="text-center text-muted">No hay registros con estos filtros.</td></tr>
                <?php endif; ?>
                <?php foreach ($reporteSitios as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) ?></td>
                        <td><?= htmlspecialchars($r['direccion']) ?></td>
                        <td><?= htmlspecialchars($r['barrios'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['comunas'] ?? '—') ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($r['tipo_deposito']) ?></span></td>
                        <td><?= htmlspecialchars($r['actividad']) ?></td>
                        <td><?= (int)$r['larvas_aedes'] ?></td>
                        <td><?= (int)$r['pupas'] ?></td>
                        <td><?= (int)$r['larvas_culex'] ?></td>
                        <td><?= htmlspecialchars($r['auxiliar']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-round mb-4">
            <div class="card-header"><h4 class="card-title">Reporte 2 · Por tipo de actividad</h4></div>
            <div class="card-body">
                <?php if (empty($reporteActividad)): ?>
                    <p class="text-muted mb-0">No hay datos para graficar con estos filtros.</p>
                <?php else: ?>
                    <canvas id="graficoActividad" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-round mb-4">
            <div class="card-header"><h4 class="card-title">Reporte 4 · Por tipo de depósito</h4></div>
            <div class="card-body">
                <?php if (empty($reporteTipoDeposito)): ?>
                    <p class="text-muted mb-0">No hay datos para graficar con estos filtros.</p>
                <?php else: ?>
                    <canvas id="graficoTipoDeposito" height="220"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Reporte 3 · Actividades por auxiliar</h4></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Auxiliar</th><th>Total registros</th><th>Detalle por actividad</th></tr></thead>
                <tbody>
                <?php if (empty($reporteAuxiliar)): ?>
                    <tr><td colspan="3" class="text-center text-muted">No hay registros con estos filtros.</td></tr>
                <?php endif; ?>
                <?php foreach ($reporteAuxiliar as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['auxiliar']) ?></td>
                        <td><?= (int)$a['total'] ?></td>
                        <td><?= htmlspecialchars(implode(' · ', $a['actividades'])) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var comunaSelect = document.getElementById('f-comuna');
    var barrioSelect = document.getElementById('f-barrio');
    var opcionesBarrio = Array.prototype.slice.call(barrioSelect.options).map(function (o) {
        return { value: o.value, text: o.textContent, comuna: o.dataset.comuna, selected: o.selected };
    });

    function actualizarBarrios() {
        var idComuna = comunaSelect.value;
        var valorPrevio = barrioSelect.value;
        barrioSelect.innerHTML = '';

        var todos = document.createElement('option');
        todos.value = '';
        todos.textContent = 'Todos';
        barrioSelect.appendChild(todos);

        opcionesBarrio.forEach(function (o) {
            if (o.value === '') return;
            if (!idComuna || o.comuna === idComuna) {
                var opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.text;
                if (o.value === valorPrevio) opt.selected = true;
                barrioSelect.appendChild(opt);
            }
        });
    }

    comunaSelect.addEventListener('change', actualizarBarrios);
});
</script>

<script src="../../assets/js/plugin/chart.js/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var datosActividad = <?= json_encode($reporteActividad) ?>;
    var datosTipoDeposito = <?= json_encode($reporteTipoDeposito) ?>;

    var canvasActividad = document.getElementById('graficoActividad');
    if (canvasActividad) {
        new Chart(canvasActividad.getContext('2d'), {
            type: 'bar',
            data: {
                labels: datosActividad.map(function (d) { return d.etiqueta; }),
                datasets: [{ label: 'Registros', data: datosActividad.map(function (d) { return d.total; }), backgroundColor: '#1D63B3' }]
            },
            options: { legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } }
        });
    }

    var canvasTipoDeposito = document.getElementById('graficoTipoDeposito');
    if (canvasTipoDeposito) {
        new Chart(canvasTipoDeposito.getContext('2d'), {
            type: 'bar',
            data: {
                labels: datosTipoDeposito.map(function (d) { return d.etiqueta; }),
                datasets: [{ label: 'Registros', data: datosTipoDeposito.map(function (d) { return d.total; }), backgroundColor: '#153D6B' }]
            },
            options: { legend: { display: false }, scales: { yAxes: [{ ticks: { beginAtZero: true, precision: 0 } }] } }
        });
    }
});
</script>

<?php require_once '../../includes/auxterreno_footer.php'; ?>
