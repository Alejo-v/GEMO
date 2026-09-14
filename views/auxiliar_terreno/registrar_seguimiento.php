<?php
$pageTitle = 'GEMO | Registro de campo';
require_once '../../includes/auxterreno_header.php';
require_once '../../models/SeguimientoTerreno.php';

$modelo = new SeguimientoTerreno();
$sitios = $modelo->obtenerSitios();
$depositos = $modelo->obtenerDepositos();
$actividades = $modelo->obtenerActividades();

$error = $_SESSION['seguimiento_terreno_error'] ?? null; unset($_SESSION['seguimiento_terreno_error']);
$exito = $_SESSION['seguimiento_terreno_exito'] ?? null; unset($_SESSION['seguimiento_terreno_exito']);
?>
<div class="mb-4">
    <h3 class="fw-bold mb-1">Registro de trabajo de terreno</h3>
    <p class="text-muted mb-0">Diligencie la información de la visita realizada al sitio/depósito.</p>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>

<form method="post" action="../../controllers/SeguimientoTerrenoController.php" novalidate id="form-seguimiento-terreno">
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
    <div class="card-header"><h4 class="card-title">Sitio y depósito</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Sitio *</label>
                <!-- Se agregó name="id_sitio" que faltaba -->
                <select name="id_sitio" id="id_sitio" class="form-select" required>
                    <option value="">Seleccione un sitio</option>
                    <?php foreach ($sitios as $s): ?>
                        <option value="<?= (int)$s['id_sitio'] ?>">
                            #<?= (int)$s['id_sitio'] ?> — <?= htmlspecialchars($s['direccion']) ?> (<?= htmlspecialchars($s['barrios']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($sitios)): ?><small class="text-danger">No hay sitios registrados todavía en el sistema.</small><?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">Depósito *</label>
                <select name="id_deposito" id="id_deposito" class="form-select" required disabled>
                    <option value="">Seleccione primero un sitio</option>
                    <?php foreach ($depositos as $d): ?>
                        <option value="<?= (int)$d['id_deposito'] ?>" data-sitio="<?= (int)($d['id_sitio'] ?? 0) ?>">
                            #<?= (int)$d['id_deposito'] ?> — <?= htmlspecialchars($d['tipo_deposito']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Piscina abandonada, aguas estancadas, fuente/pila de agua o construcción abandonada.</small>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Actividad realizada</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Actividad *</label>
                <select name="id_actividad_terreno" class="form-select" required>
                    <option value="">Seleccione una actividad</option>
                    <?php foreach ($actividades as $a): ?>
                        <option value="<?= (int)$a['id_actividad_terreno'] ?>">
                            <?= htmlspecialchars($a['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Inspección, siembra, seguimiento o resiembra.</small>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Parámetros fisicoquímicos</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">pH *</label>
                <input type="number" step="0.1" min="0" max="14" name="ph" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Temperatura (°C) *</label>
                <input type="number" step="0.1" min="0" max="50" name="temperatura" class="form-control" required>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Presencia de larvas/pupas</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Larvas de Aedes *</label>
                <input type="number" step="1" min="0" name="larvas_aedes" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Pupas *</label>
                <input type="number" step="1" min="0" name="pupas" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Larvas de Culex *</label>
                <input type="number" step="1" min="0" name="larvas_culex" class="form-control" required>
            </div>
        </div>
        <small class="text-muted">Si no se observó ninguna, registre 0.</small>
    </div>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-gemo" type="submit"><i class="fas fa-save me-1"></i> Guardar registro</button>
    <a href="mis_registros.php" class="btn btn-secondary">Ver mis registros</a>
</div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var sitioSelect = document.getElementById('id_sitio');
    var depositoSelect = document.getElementById('id_deposito');
    
    // Guardar las opciones originales de depósitos omitiendo placeholders vacíos
    var opcionesDeposito = Array.prototype.slice.call(depositoSelect.options).filter(function (op) {
        return op.value !== '';
    });

    function actualizarDepositos() {
        var idSitio = String(sitioSelect.value).trim();
        depositoSelect.innerHTML = '';

        if (!idSitio) {
            depositoSelect.disabled = true;
            var opcionVacia = document.createElement('option');
            opcionVacia.value = '';
            opcionVacia.textContent = 'Seleccione primero un sitio';
            depositoSelect.appendChild(opcionVacia);
            return;
        }

        var opcionPlaceholder = document.createElement('option');
        opcionPlaceholder.value = '';
        opcionPlaceholder.textContent = 'Seleccione un depósito';
        depositoSelect.appendChild(opcionPlaceholder);

        var encontrados = 0;
        opcionesDeposito.forEach(function (opcion) {
            var sitioDeposito = String(opcion.getAttribute('data-sitio') || '').trim();
            if (sitioDeposito === idSitio) {
                depositoSelect.appendChild(opcion.cloneNode(true));
                encontrados++;
            }
        });

        depositoSelect.disabled = (encontrados === 0);
    }

    sitioSelect.addEventListener('change', actualizarDepositos);
    actualizarDepositos();
});
</script>

<?php require_once '../../includes/auxterreno_footer.php'; ?>
