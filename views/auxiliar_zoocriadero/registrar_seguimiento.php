<?php
$pageTitle = 'GEMO | Registro diario zoocriadero';
require_once '../../includes/auxzoo_header.php';
require_once '../../models/SeguimientoZoocriadero.php';

$modelo = new SeguimientoZoocriadero();
$tanques = $modelo->obtenerTanques();
$actividades = $modelo->obtenerActividades();

$error = $_SESSION['seguimiento_error'] ?? null; unset($_SESSION['seguimiento_error']);
$exito = $_SESSION['seguimiento_exito'] ?? null; unset($_SESSION['seguimiento_exito']);
?>
<div class="mb-4">
    <h3 class="fw-bold mb-1">Registro diario del zoocriadero</h3>
    <p class="text-muted mb-0">Diligencie la información correspondiente al día de hoy.</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>

<form method="post" action="../../controllers/SeguimientoZoocriaderoController.php" novalidate id="form-seguimiento-zoo">
<input type="hidden" name="accion" value="registrar_seguimiento">

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Información general</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Fecha</label>
                <input type="text" class="form-control" value="<?= date('d/m/Y') ?>" disabled>
                <small class="text-muted">Se registra automáticamente con la fecha actual.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Registrado por</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars(trim(($_SESSION['usuario_nombre'] ?? '') . ' ' . ($_SESSION['usuario_apellido'] ?? ''))) ?>" disabled>
                <small class="text-muted">Se toma automáticamente de la cuenta con la que inició sesión.</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Rol</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($_SESSION['usuario_rol'] ?? '') ?>" disabled>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Tanque</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Número de tanque *</label>
                <select name="id_tanque" id="id_tanque" class="form-select" required>
                    <option value="">Seleccione un tanque</option>
                    <?php foreach ($tanques as $t): ?>
                        <option value="<?= (int)$t['id_tanque'] ?>" data-tipo="<?= htmlspecialchars($t['tipo_tanque']) ?>">
                            Tanque #<?= (int)$t['id_tanque'] ?> — <?= htmlspecialchars($t['estado']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($tanques)): ?><small class="text-danger">No hay tanques registrados todavía en el sistema.</small><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo de tanque</label>
                <input type="text" id="tipo_tanque" class="form-control" placeholder="Se completa al elegir el tanque" disabled>
                <small class="text-muted">Siembra, reproducción, alevines o reserva, según el tanque elegido.</small>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Parámetros fisicoquímicos</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">pH *</label>
                <input type="number" step="0.1" min="0" max="14" name="ph" class="form-control" required>
                <small class="text-muted">Valor mínimo: 0 — Valor máximo: 14</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Temperatura (°C) *</label>
                <input type="number" step="0.1" min="0" max="50" name="temperatura" class="form-control" required>
                <small class="text-muted">Valor mínimo: 0 °C — Valor máximo: 50 °C</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cloro (mg/L) *</label>
                <input type="number" step="0.01" min="0" max="20" name="cloro" class="form-control" required>
                <small class="text-muted">Valor mínimo: 0 mg/L — Valor máximo: 20 mg/L</small>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Alevines y mortalidad</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Número de alevines nacimiento *</label>
                <input type="number" step="1" min="0" name="alevines_nacidos" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Número de muertos (macho) *</label>
                <input type="number" step="1" min="0" name="muertos_macho" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Número de muertos (hembra) *</label>
                <input type="number" step="1" min="0" name="muertos_hembra" class="form-control" required>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Actividades realizadas</h4></div>
    <div class="card-body">
        <?php if (empty($actividades)): ?>
            <p class="text-muted mb-0">No hay actividades configuradas en el sistema.</p>
        <?php endif; ?>
        <?php foreach ($actividades as $a): ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="actividades[]"
                       value="<?= (int)$a['id_actividad_zoocriadero'] ?>" id="act_<?= (int)$a['id_actividad_zoocriadero'] ?>">
                <label class="form-check-label" for="act_<?= (int)$a['id_actividad_zoocriadero'] ?>">
                    <?= htmlspecialchars($a['nombre']) ?>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Observaciones</h4></div>
    <div class="card-body">
        <textarea name="observaciones" class="form-control" rows="4" maxlength="1000" placeholder="Observaciones adicionales del día (opcional)"></textarea>
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-gemo" type="submit"><i class="fas fa-save me-1"></i> Guardar registro</button>
    <a href="mis_registros.php" class="btn btn-secondary">Ver mis registros</a>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('id_tanque');
    var tipoInput = document.getElementById('tipo_tanque');
    function actualizarTipo() {
        var opcion = select.options[select.selectedIndex];
        tipoInput.value = (opcion && opcion.dataset.tipo) ? opcion.dataset.tipo : '';
    }
    if (select) {
        select.addEventListener('change', actualizarTipo);
        actualizarTipo();
    }
});
</script>

<?php require_once '../../includes/auxzoo_footer.php'; ?>
