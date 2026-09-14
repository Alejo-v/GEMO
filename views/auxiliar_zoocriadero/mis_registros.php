<?php
$pageTitle = 'GEMO | Mis registros';
require_once '../../includes/auxzoo_header.php';
require_once '../../models/SeguimientoZoocriadero.php';

$modelo = new SeguimientoZoocriadero();
$registros = $modelo->obtenerRegistrosPorUsuario((int) $_SESSION['usuario_id']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Mis registros</h3>
        <p class="text-muted mb-0">Historial de seguimientos que has registrado en el zoocriadero.</p>
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
                        <th>Tanque</th>
                        <th>Tipo</th>
                        <th>pH</th>
                        <th>Temp.</th>
                        <th>Cloro</th>
                        <th>Alevines</th>
                        <th>Muertos M/H</th>
                        <th>Actividades</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($registros)): ?>
                    <tr><td colspan="10" class="text-center text-muted">Aún no tienes registros.</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($r['fecha']))) ?></td>
                        <td>#<?= (int)$r['id_tanque'] ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($r['tipo_tanque']) ?></span></td>
                        <td><?= htmlspecialchars((string) $r['ph']) ?></td>
                        <td><?= htmlspecialchars((string) $r['temperatura']) ?></td>
                        <td><?= htmlspecialchars((string) $r['cloro']) ?></td>
                        <td><?= (int)$r['alevines_nacidos'] ?></td>
                        <td><?= (int)$r['muertos_macho'] ?> / <?= (int)$r['muertos_hembra'] ?></td>
                        <td><?= htmlspecialchars($r['actividades']) ?></td>
                        <td><?= htmlspecialchars($r['observaciones'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once '../../includes/auxzoo_footer.php'; ?>
