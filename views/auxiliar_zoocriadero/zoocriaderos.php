<?php
$pageTitle = 'GEMO | Zoocriaderos';
require_once '../../includes/auxzoo_header.php';
require_once '../../models/Zoocriadero.php';

$modelo = new Zoocriadero();
$zoocriaderos = $modelo->obtenerTodos();
$barrios = $modelo->obtenerBarrios();
$encargados = $modelo->obtenerEncargados();

$error = $_SESSION['zoocriadero_error'] ?? null; unset($_SESSION['zoocriadero_error']);
$exito = $_SESSION['zoocriadero_exito'] ?? null; unset($_SESSION['zoocriadero_exito']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Zoocriaderos</h3>
        <p class="text-muted mb-0">Sedes donde se crían los peces guppies (Control Biológico).</p>
    </div>
    <button type="button" class="btn btn-gemo" onclick="nuevoZoocriadero()"><i class="fas fa-plus me-1"></i> Nuevo zoocriadero</button>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>

<div class="card card-round mb-4" id="card-formulario" style="display:none;">
    <div class="card-header"><h4 class="card-title" id="titulo-formulario">Nuevo zoocriadero</h4></div>
    <div class="card-body">
        <form method="post" action="../../controllers/ZoocriaderoController.php" id="form-zoocriadero">
            <input type="hidden" name="accion" id="f-accion" value="crear">
            <input type="hidden" name="id_zoocriadero" id="f-id_zoocriadero" value="">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Dirección *</label>
                    <input type="text" name="direccion" id="f-direccion" class="form-control" maxlength="50" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Barrio *</label>
                    <select name="id_barrio" id="f-id_barrio" class="form-select" required>
                        <option value="">Seleccione un barrio</option>
                        <?php foreach ($barrios as $b): ?>
                            <option value="<?= (int)$b['id_barrio'] ?>">
                                <?= htmlspecialchars($b['nombre']) ?> (<?= htmlspecialchars($b['comuna']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Encargado *</label>
                    <select name="id_usuario" id="f-id_usuario" class="form-select" required>
                        <option value="">Seleccione un encargado</option>
                        <?php foreach ($encargados as $u): ?>
                            <option value="<?= (int)$u['id_usuario'] ?>"><?= htmlspecialchars($u['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
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
                    <tr><th>#</th><th>Dirección</th><th>Barrio</th><th>Encargado</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php if (empty($zoocriaderos)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No hay zoocriaderos registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($zoocriaderos as $z): ?>
                    <tr>
                        <td><?= (int)$z['id_zoocriadero'] ?></td>
                        <td><?= htmlspecialchars($z['direccion']) ?></td>
                        <td><?= htmlspecialchars($z['barrio']) ?></td>
                        <td><?= htmlspecialchars($z['encargado']) ?></td>
                        <td>
                            <?php if ($z['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inhabilitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='editarZoocriadero(<?= json_encode($z, JSON_HEX_APOS) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="post" action="../../controllers/ZoocriaderoController.php" class="d-inline">
                                <input type="hidden" name="id_zoocriadero" value="<?= (int)$z['id_zoocriadero'] ?>">
                                <?php if ($z['activo']): ?>
                                    <input type="hidden" name="accion" value="inhabilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Inhabilitar este zoocriadero?')"><i class="fas fa-ban"></i></button>
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
function nuevoZoocriadero() {
    document.getElementById('titulo-formulario').textContent = 'Nuevo zoocriadero';
    document.getElementById('f-accion').value = 'crear';
    document.getElementById('f-id_zoocriadero').value = '';
    document.getElementById('f-direccion').value = '';
    document.getElementById('f-id_barrio').value = '';
    document.getElementById('f-id_usuario').value = '';
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function editarZoocriadero(zoocriadero) {
    document.getElementById('titulo-formulario').textContent = 'Editar zoocriadero #' + zoocriadero.id_zoocriadero;
    document.getElementById('f-accion').value = 'actualizar';
    document.getElementById('f-id_zoocriadero').value = zoocriadero.id_zoocriadero;
    document.getElementById('f-direccion').value = zoocriadero.direccion;
    document.getElementById('f-id_barrio').value = zoocriadero.id_barrio;
    document.getElementById('f-id_usuario').value = zoocriadero.id_usuario;
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

<?php require_once '../../includes/auxzoo_footer.php'; ?>
