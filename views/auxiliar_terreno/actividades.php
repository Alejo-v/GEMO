<?php
$pageTitle = 'GEMO | Actividades de terreno';
require_once '../../includes/auxterreno_header.php';
require_once '../../models/ActividadTerreno.php';

$modelo = new ActividadTerreno();
$actividades = $modelo->obtenerTodas();

$error = $_SESSION['actividad_terreno_error'] ?? null; unset($_SESSION['actividad_terreno_error']);
$exito = $_SESSION['actividad_terreno_exito'] ?? null; unset($_SESSION['actividad_terreno_exito']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Actividades de terreno</h3>
        <p class="text-muted mb-0">Pasos del protocolo de control biológico: inspección, siembra, seguimiento y resiembra.</p>
    </div>
    <button type="button" class="btn btn-gemo" onclick="nuevaActividad()"><i class="fas fa-plus me-1"></i> Nueva actividad</button>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>

<div class="card card-round mb-4" id="card-formulario" style="display:none;">
    <div class="card-header"><h4 class="card-title" id="titulo-formulario">Nueva actividad</h4></div>
    <div class="card-body">
        <form method="post" action="../../controllers/ActividadTerrenoController.php" id="form-actividad">
            <input type="hidden" name="accion" id="f-accion" value="crear">
            <input type="hidden" name="id_actividad_terreno" id="f-id" value="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" id="f-nombre" class="form-control" maxlength="120" required>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-gemo"><i class="fas fa-save me-1"></i> Guardar</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarFormulario()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div class="card card-round">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr><th>#</th><th>Nombre</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php if (empty($actividades)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No hay actividades registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($actividades as $a): ?>
                    <tr>
                        <td><?= (int)$a['id_actividad_terreno'] ?></td>
                        <td><?= htmlspecialchars($a['nombre']) ?></td>
                        <td>
                            <?php if ($a['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inhabilitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='editarActividad(<?= json_encode($a, JSON_HEX_APOS) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="post" action="../../controllers/ActividadTerrenoController.php" class="d-inline">
                                <input type="hidden" name="id_actividad_terreno" value="<?= (int)$a['id_actividad_terreno'] ?>">
                                <?php if ($a['activo']): ?>
                                    <input type="hidden" name="accion" value="inhabilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Inhabilitar esta actividad?')"><i class="fas fa-ban"></i></button>
                                <?php else: ?>
                                    <input type="hidden" name="accion" value="habilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function nuevaActividad() {
    document.getElementById('titulo-formulario').textContent = 'Nueva actividad';
    document.getElementById('f-accion').value = 'crear';
    document.getElementById('f-id').value = '';
    document.getElementById('f-nombre').value = '';
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function editarActividad(actividad) {
    document.getElementById('titulo-formulario').textContent = 'Editar actividad #' + actividad.id_actividad_terreno;
    document.getElementById('f-accion').value = 'actualizar';
    document.getElementById('f-id').value = actividad.id_actividad_terreno;
    document.getElementById('f-nombre').value = actividad.nombre;
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function cerrarFormulario() {
    document.getElementById('card-formulario').style.display = 'none';
}

<?php if ($error || $exito): ?>
document.getElementById('card-formulario').style.display = <?= $error ? "''" : "'none'" ?>;
<?php endif; ?>
</script>

<?php require_once '../../includes/auxterreno_footer.php'; ?>
