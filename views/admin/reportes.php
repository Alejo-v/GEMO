<?php
$pageTitle = 'GEMO | Centro de reportes';
require_once '../../includes/admin_header.php';
?>
<div class="mb-4">
    <h3 class="fw-bold mb-1">Centro de reportes</h3>
    <p class="text-muted mb-0">
        Los reportes están separados por proceso. Cada módulo tiene sus propios filtros
        y cada reporte se descarga en un PDF independiente.
    </p>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card card-round h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-big text-center icon-success bubble-shadow-small me-3">
                        <i class="fas fa-fish"></i>
                    </div>
                    <div>
                        <h4 class="card-title mb-0">Proceso Zoocriadero</h4>
                        <p class="text-muted mb-0">3 reportes</p>
                    </div>
                </div>
                <ul class="mb-4">
                    <li>Reporte 1 — Seguimiento de actividades (fechas, zoocriadero, actividad)</li>
                    <li>Reporte 2 — Nacidos y muertos por tanque</li>
                    <li>Reporte 3 — Tanques por zoocriadero (cantidad, tipo, encargado)</li>
                </ul>
                <a href="reportes_zoocriadero.php" class="btn btn-gemo mt-auto">
                    <i class="fas fa-arrow-right me-1"></i> Abrir reportes del zoocriadero
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card card-round h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-big text-center icon-info bubble-shadow-small me-3">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <div>
                        <h4 class="card-title mb-0">Proceso Terreno</h4>
                        <p class="text-muted mb-0">4 reportes</p>
                    </div>
                </div>
                <ul class="mb-4">
                    <li>Reporte 1 — Detalle de sitios visitados</li>
                    <li>Reporte 2 — Registros por tipo de actividad</li>
                    <li>Reporte 3 — Actividades por auxiliar</li>
                    <li>Reporte 4 — Registros por tipo de depósito</li>
                </ul>
                <a href="reportes_terreno.php" class="btn btn-gemo mt-auto">
                    <i class="fas fa-arrow-right me-1"></i> Abrir reportes de terreno
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/admin_footer.php'; ?>
