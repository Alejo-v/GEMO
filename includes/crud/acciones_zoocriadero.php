<?php
require_once __DIR__ . '/../../models/ActividadZoocriadero.php';

$modelo = new ActividadZoocriadero();
$acciones = $modelo->obtenerTodas();

$error = $_SESSION['accion_zoo_error'] ?? null; unset($_SESSION['accion_zoo_error']);
$exito = $_SESSION['accion_zoo_exito'] ?? null; unset($_SESSION['accion_zoo_exito']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Acciones del zoocriadero</h3>
        <p class="text-muted mb-0">Catálogo de acciones que se pueden marcar en el registro diario (Limpieza, Aspirado, Ajuste de nivel, etc.).</p>
    </div>
    <button type="button" class="btn btn-gemo" onclick="nuevaAccion()"><i class="fas fa-plus me-1"></i> Nueva acción</button>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>

<div class="card card-round mb-4" id="card-formulario" style="display:none;">
    <div class="card-header"><h4 class="card-title" id="titulo-formulario">Nueva acción</h4></div>
    <div class="card-body">
        <form method="post" action="../../controllers/AccionZoocriaderoController.php" id="form-accion-zoo">
            <input type="hidden" name="accion" id="f-accion" value="crear">
            <input type="hidden" name="id_actividad_zoocriadero" id="f-id_actividad_zoocriadero" value="">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Nombre *</label>
                    <input type="text" name="nombre" id="f-nombre" class="form-control" maxlength="120" required>
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-gemo"><i class="fas fa-save me-1"></i> Guardar</button>
                <button type="button" class="btn btn-secondary" onclick="cerrarFormulario()"><i class="fas fa-times me-1"></i>Cancelar</button>
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
                <?php if (empty($acciones)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No hay acciones registradas.</td></tr>
                <?php endif; ?>
                <?php foreach ($acciones as $a): ?>
                    <tr>
                        <td><?= (int)$a['id_actividad_zoocriadero'] ?></td>
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
                                    onclick='editarAccion(<?= json_encode($a, JSON_HEX_APOS) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="post" action="../../controllers/AccionZoocriaderoController.php" class="d-inline">
                                <input type="hidden" name="id_actividad_zoocriadero" value="<?= (int)$a['id_actividad_zoocriadero'] ?>">
                                <?php if ($a['activo']): ?>
                                    <input type="hidden" name="accion" value="inhabilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Inhabilitar esta acción?')"><i class="fas fa-ban"></i></button>
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
function nuevaAccion() {
    document.getElementById('titulo-formulario').textContent = 'Nueva acción';
    document.getElementById('f-accion').value = 'crear';
    document.getElementById('f-id_actividad_zoocriadero').value = '';
    document.getElementById('f-nombre').value = '';
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function editarAccion(accion) {
    document.getElementById('titulo-formulario').textContent = 'Editar acción #' + accion.id_actividad_zoocriadero;
    document.getElementById('f-accion').value = 'actualizar';
    document.getElementById('f-id_actividad_zoocriadero').value = accion.id_actividad_zoocriadero;
    document.getElementById('f-nombre').value = accion.nombre;
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
