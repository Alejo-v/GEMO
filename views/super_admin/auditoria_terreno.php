<?php
$pageTitle='GEMO | Auditoría de terreno';
require_once '../../includes/superadmin_header.php';
require_once '../../models/SeguimientoTerreno.php';

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
    <button class="btn btn-gemo" type="submit"><i class="fas fa-filter me-1"></i> Filtrar</button>
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
<th>Fecha</th><th>Responsable</th><th>Rol</th><th>Actividad</th><th>Sitio</th><th>Depósito</th><th>Datos registrados</th><th>Contacto</th>
</tr></thead>
<tbody>
<?php foreach ($registros as $r): ?>
<tr>
<td><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) ?></td>
<td><strong><?= htmlspecialchars($r['usuario']) ?></strong><br><small class="text-muted">ID <?= (int)$r['id_usuario'] ?></small></td>
<td><span class="badge badge-success"><?= htmlspecialchars($r['nombre_rol']) ?></span></td>
<td><?= htmlspecialchars($r['actividad']) ?></td>
<td><?= htmlspecialchars($r['sitio']) ?></td>
<td><?= htmlspecialchars($r['tipo_deposito']) ?></td>
<td>
  <small>pH: <?= htmlspecialchars((string)$r['ph']) ?> · Temp: <?= htmlspecialchars((string)$r['temperatura']) ?>°</small><br>
  <small>Aedes: <?= (int)$r['larvas_aedes'] ?> · Pupas: <?= (int)$r['pupas'] ?> · Culex: <?= (int)$r['larvas_culex'] ?></small>
</td>
<td>
  <small><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($r['correo']) ?></small><br>
  <small><i class="fas fa-phone me-1"></i><?= htmlspecialchars($r['telefono']) ?></small>
</td>
</tr>
<?php endforeach; ?>
<?php if (empty($registros)): ?>
<tr><td colspan="8" class="text-center text-muted py-4">No hay registros con los filtros seleccionados.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
</div>
<?php require_once '../../includes/superadmin_footer.php'; ?>
