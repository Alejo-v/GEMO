<?php
$pageTitle = 'GEMO | Mis registros';
require_once '../../includes/auxterreno_header.php';
require_once '../../models/SeguimientoTerreno.php';

$modelo = new SeguimientoTerreno();
$registros = $modelo->obtenerRegistrosPorUsuario((int) $_SESSION['usuario_id']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Mis registros</h3>
        <p class="text-muted mb-0">Historial de visitas de terreno que has registrado.</p>
    </div>
    <a href="registrar_seguimiento.php" class="btn btn-gemo"><i class="fas fa-plus me-1"></i> Nuevo registro</a>
</div>

<div class="card card-round">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Sitio</th>
                        <th>Depósito</th>
                        <th>Actividad</th>
                        <th>pH</th>
                        <th>Temp.</th>
                        <th>Larvas Aedes</th>
                        <th>Pupas</th>
                        <th>Larvas Culex</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="9" class="text-center text-muted">Aún no tienes registros.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) ?></td>
                        <td><?= htmlspecialchars($r['sitio_direccion']) ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($r['tipo_deposito']) ?></span></td>
                        <td><?= htmlspecialchars($r['actividad']) ?></td>
                        <td><?= htmlspecialchars((string) $r['ph']) ?></td>
                        <td><?= htmlspecialchars((string) $r['temperatura']) ?></td>
                        <td><?= (int)$r['larvas_aedes'] ?></td>
                        <td><?= (int)$r['pupas'] ?></td>
                        <td><?= (int)$r['larvas_culex'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../../includes/auxterreno_footer.php'; ?>
