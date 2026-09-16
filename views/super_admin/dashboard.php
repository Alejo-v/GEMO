<?php
$pageTitle='GEMO | Super Administrador';
require_once '../../includes/superadmin_header.php';
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h3 class="fw-bold mb-1">Super Administrador</h3>
    <p class="text-muted mb-0">Consulta del historial de registros realizados en terreno.</p>
  </div>
  <a href="auditoria_terreno.php" class="btn btn-gemo"><i class="fas fa-history me-1"></i> Ver auditoría</a>
</div>
<div class="row">
  <div class="col-md-6">
    <div class="card card-stats card-round"><div class="card-body">
      <div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-history"></i></div></div>
      <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Trazabilidad</p><h4 class="card-title">Auditoría de terreno</h4><p class="card-category mb-0">Quién registró, qué actividad realizó y cuándo.</p></div></div></div>
    </div></div>
  </div>
  <div class="col-md-6">
    <div class="card card-stats card-round"><div class="card-body">
      <div class="row align-items-center"><div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-user-check"></i></div></div>
      <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Seguimiento</p><h4 class="card-title">Datos del responsable</h4><p class="card-category mb-0">Correo y teléfono disponibles desde cada registro.</p></div></div></div>
    </div></div>
  </div>
</div>
<div class="card card-round mt-3"><div class="card-body">
<h4 class="card-title">Control de registros</h4>
<p class="card-text">La auditoría se basa en <strong>seguimiento_terreno</strong>, tabla que relaciona cada registro con el usuario, la fecha y la actividad de terreno. Desde la consulta puedes filtrar por rango de fechas, usuario y actividad.</p>
<a href="auditoria_terreno.php" class="btn btn-outline-success">Abrir historial</a>
</div></div>
<?php require_once '../../includes/superadmin_footer.php'; ?>
