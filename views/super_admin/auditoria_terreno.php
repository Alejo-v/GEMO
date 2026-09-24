<?php
$pageTitle='GEMO | Auditoría de terreno';
require_once __DIR__ . '/../../includes/superadmin_header.php';
require_once __DIR__ . '/../../models/SeguimientoTerreno.php';

$modelo = new SeguimientoTerreno();

$fechaDesde = trim($_GET['fecha_desde'] ?? '');
$fechaHasta = trim($_GET['fecha_hasta'] ?? '');
$idUsuario = (int)($_GET['id_usuario'] ?? 0);
$idActividad = (int)($_GET['id_actividad'] ?? 0);

$usuarios = $modelo->obtenerUsuariosConRegistrosTerreno();
$actividades = $modelo->obtenerActividades();
$registros = $modelo->obtenerAuditoriaTerreno(
    $fechaDesde !== '' ? $fechaDesde : null,
    $fechaHasta !== '' ? $fechaHasta : null,
    $idUsuario > 0 ? $idUsuario : null,
    $idActividad > 0 ? $idActividad : null
);
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h3 class="fw-bold mb-1">Auditoría de terreno</h3>
    <p class="text-muted mb-0">Historial de quién realizó cada registro, qué actividad realizó y en qué fecha.</p>
  </div>
</div>

<div class="card card-round mb-4">
<div class="card-body">
<form method="get" class="row g-3 align-items-end">
  <div class="col-md-3">
    <label class="form-label">Desde</label>
    <input type="date" class="form-control" name="fecha_desde" value="<?= htmlspecialchars($fechaDesde) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Hasta</label>
    <input type="date" class="form-control" name="fecha_hasta" value="<?= htmlspecialchars($fechaHasta) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Usuario</label>
    <select class="form-select" name="id_usuario">
      <option value="0">Todos</option>
      <?php foreach ($usuarios as $u): ?>
        <option value="<?= (int)$u['id_usuario'] ?>" <?= $idUsuario === (int)$u['id_usuario'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($u['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Actividad</label>
    <select class="form-select" name="id_actividad">
      <option value="0">Todas</option>
      <?php foreach ($actividades as $a): ?>
        <option value="<?= (int)$a['id_actividad_terreno'] ?>" <?= $idActividad === (int)$a['id_actividad_terreno'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($a['nombre']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12 d-flex gap-2">
    <button class="btn btn-gemo" type="submit"><i class="fas fa-filter me-1"></i> Consultar</button>
    <a class="btn btn-outline-secondary" href="auditoria_terreno.php">Limpiar</a>
  </div>
</form>
</div>
</div>

<div class="card card-round">
<div class="card-body">
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="card-title mb-0">Registros encontrados: <?= count($registros) ?></h4>
  <span class="text-muted small">Los filtros se aplican sobre el historial de terreno.</span>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle">
<thead><tr>
<th>Fecha</th><th>Responsable</th><th>Rol</th><th>Actividad</th><th class="text-end">Detalle</th>
</tr></thead>
<tbody>
<?php foreach ($registros as $r): ?>
<tr>
<td><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) ?></td>
<td><strong><?= htmlspecialchars($r['usuario']) ?></strong></td>
<td><span class="badge badge-success"><?= htmlspecialchars($r['nombre_rol']) ?></span></td>
<td><?= htmlspecialchars($r['actividad']) ?></td>
<td class="text-end">
  <button type="button"
          class="btn btn-sm btn-outline-success"
          data-bs-toggle="modal"
          data-bs-target="#modalDetalleAuditoria"
          data-fecha="<?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha'])), ENT_QUOTES) ?>"
          data-usuario="<?= htmlspecialchars($r['usuario'], ENT_QUOTES) ?>"
          data-rol="<?= htmlspecialchars($r['nombre_rol'], ENT_QUOTES) ?>"
          data-actividad="<?= htmlspecialchars($r['actividad'], ENT_QUOTES) ?>"
          data-sitio="<?= htmlspecialchars($r['sitio'], ENT_QUOTES) ?>"
          data-deposito="<?= htmlspecialchars($r['tipo_deposito'], ENT_QUOTES) ?>"
          data-ph="<?= htmlspecialchars((string)$r['ph'], ENT_QUOTES) ?>"
          data-temperatura="<?= htmlspecialchars((string)$r['temperatura'], ENT_QUOTES) ?>"
          data-aedes="<?= (int)$r['larvas_aedes'] ?>"
          data-pupas="<?= (int)$r['pupas'] ?>"
          data-culex="<?= (int)$r['larvas_culex'] ?>"
          data-correo="<?= htmlspecialchars($r['correo'], ENT_QUOTES) ?>"
          data-telefono="<?= htmlspecialchars($r['telefono'], ENT_QUOTES) ?>">
    <i class="fas fa-eye"></i> Ver más
  </button>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($registros)): ?>
<tr><td colspan="5" class="text-center text-muted py-4">No hay registros con los filtros seleccionados.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>

<!-- Modal detalle de auditoría -->
<div class="modal fade" id="modalDetalleAuditoria" tabindex="-1" aria-labelledby="modalDetalleAuditoriaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetalleAuditoriaLabel">Detalle del registro</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label text-muted small mb-0">Fecha</label><p class="mb-0" id="det_fecha"></p></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0">Responsable</label><p class="mb-0" id="det_usuario"></p></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0">Rol</label><p class="mb-0" id="det_rol"></p></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0">Actividad</label><p class="mb-0" id="det_actividad"></p></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0">Sitio</label><p class="mb-0" id="det_sitio"></p></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0">Depósito</label><p class="mb-0" id="det_deposito"></p></div>
          <div class="col-12"><hr class="my-1"></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0">pH / Temperatura</label><p class="mb-0" id="det_ph_temp"></p></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0">Aedes / Pupas / Culex</label><p class="mb-0" id="det_conteos"></p></div>
          <div class="col-12"><hr class="my-1"></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0"><i class="fas fa-envelope me-1"></i>Correo</label><p class="mb-0" id="det_correo"></p></div>
          <div class="col-md-6"><label class="form-label text-muted small mb-0"><i class="fas fa-phone me-1"></i>Teléfono</label><p class="mb-0" id="det_telefono"></p></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  var modal = document.getElementById('modalDetalleAuditoria');
  if (!modal) return;
  modal.addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    if (!btn) return;
    document.getElementById('det_fecha').textContent = btn.getAttribute('data-fecha') || '';
    document.getElementById('det_usuario').textContent = btn.getAttribute('data-usuario') || '';
    document.getElementById('det_rol').textContent = btn.getAttribute('data-rol') || '';
    document.getElementById('det_actividad').textContent = btn.getAttribute('data-actividad') || '';
    document.getElementById('det_sitio').textContent = btn.getAttribute('data-sitio') || '';
    document.getElementById('det_deposito').textContent = btn.getAttribute('data-deposito') || '';
    document.getElementById('det_ph_temp').textContent = 'pH: ' + (btn.getAttribute('data-ph') || '—') + '  ·  Temp: ' + (btn.getAttribute('data-temperatura') || '—') + '°';
    document.getElementById('det_conteos').textContent = (btn.getAttribute('data-aedes') || '0') + ' / ' + (btn.getAttribute('data-pupas') || '0') + ' / ' + (btn.getAttribute('data-culex') || '0');
    document.getElementById('det_correo').textContent = btn.getAttribute('data-correo') || '';
    document.getElementById('det_telefono').textContent = btn.getAttribute('data-telefono') || '';
  });
})();
</script>

<?php require_once __DIR__ . '/../../includes/superadmin_footer.php'; ?>