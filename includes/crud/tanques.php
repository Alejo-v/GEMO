<?php
require_once __DIR__ . '/../../models/Tanque.php';
require_once __DIR__ . '/../../models/Zoocriadero.php';
require_once __DIR__ . '/../../models/TipoTanque.php';

$modelo = new Tanque();
$tanques = $modelo->obtenerTodos();
$zoocriaderos = (new Zoocriadero())->obtenerActivos();
$tipos = (new TipoTanque())->obtenerActivos();

$error = $_SESSION['tanque_error'] ?? null; unset($_SESSION['tanque_error']);
$exito = $_SESSION['tanque_exito'] ?? null; unset($_SESSION['tanque_exito']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Tanques</h3>
        <p class="text-muted mb-0">Tanques físicos dentro de cada zoocriadero, usados en el registro diario de seguimiento.</p>
    </div>
    <button type="button" class="btn btn-gemo" onclick="nuevoTanque()"><i class="fas fa-plus me-1"></i> Nuevo tanque</button>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
<?php if (empty($zoocriaderos)): ?>
    <div class="alert alert-warning">Primero debes <a href="zoocriaderos.php">registrar un zoocriadero activo</a> antes de crear tanques.</div>
<?php endif; ?>

<div class="card card-round mb-4" id="card-formulario" style="display:none;">
    <div class="card-header"><h4 class="card-title" id="titulo-formulario">Nuevo tanque</h4></div>
    <div class="card-body">
        <form method="post" action="../../controllers/TanqueController.php" id="form-tanque">
            <input type="hidden" name="accion" id="f-accion" value="crear">
            <input type="hidden" name="id_tanque" id="f-id_tanque" value="">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Zoocriadero *</label>
                    <select name="id_zoocriadero" id="f-id_zoocriadero" class="form-select" required>
                        <option value="">Seleccione un zoocriadero</option>
                        <?php foreach ($zoocriaderos as $z): ?>
                            <option value="<?= (int)$z['id_zoocriadero'] ?>">
                                #<?= (int)$z['id_zoocriadero'] ?> — <?= htmlspecialchars($z['direccion']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo de tanque *</label>
                    <select name="id_tipo_tanque" id="f-id_tipo_tanque" class="form-select" required>
                        <option value="">Seleccione un tipo</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= (int)$t['id_tipo_tanque'] ?>"><?= htmlspecialchars($t['descripcion']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado operativo *</label>
                    <select name="estado" id="f-estado" class="form-select" required>
                        <option value="">Seleccione un estado</option>
                        <?php foreach (Tanque::ESTADOS as $estado): ?>
                            <option value="<?= htmlspecialchars($estado) ?>"><?= htmlspecialchars($estado) ?></option>
                        <?php endforeach; ?>
                    </select>
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
                    <tr><th>#</th><th>Zoocriadero</th><th>Tipo</th><th>Estado operativo</th><th>Habilitado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php if (empty($tanques)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No hay tanques registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($tanques as $t): ?>
                    <tr>
                        <td><?= (int)$t['id_tanque'] ?></td>
                        <td><?= htmlspecialchars($t['zoocriadero']) ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($t['tipo_tanque']) ?></span></td>
                        <td><?= htmlspecialchars($t['estado']) ?></td>
                        <td>
                            <?php if ($t['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inhabilitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='editarTanque(<?= json_encode($t, JSON_HEX_APOS) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="post" action="../../controllers/TanqueController.php" class="d-inline">
                                <input type="hidden" name="id_tanque" value="<?= (int)$t['id_tanque'] ?>">
                                <?php if ($t['activo']): ?>
                                    <input type="hidden" name="accion" value="inhabilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Inhabilitar este tanque?')"><i class="fas fa-ban"></i></button>
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
function nuevoTanque() {
    document.getElementById('titulo-formulario').textContent = 'Nuevo tanque';
    document.getElementById('f-accion').value = 'crear';
    document.getElementById('f-id_tanque').value = '';
    document.getElementById('f-id_zoocriadero').value = '';
    document.getElementById('f-id_tipo_tanque').value = '';
    document.getElementById('f-estado').value = '';
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function editarTanque(tanque) {
    document.getElementById('titulo-formulario').textContent = 'Editar tanque #' + tanque.id_tanque;
    document.getElementById('f-accion').value = 'actualizar';
    document.getElementById('f-id_tanque').value = tanque.id_tanque;
    document.getElementById('f-id_zoocriadero').value = tanque.id_zoocriadero;
    document.getElementById('f-id_tipo_tanque').value = tanque.id_tipo_tanque;
    document.getElementById('f-estado').value = tanque.estado;
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
