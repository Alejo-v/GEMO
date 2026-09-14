<?php
$pageTitle = 'GEMO | Depósitos';
require_once '../../includes/auxterreno_header.php';
require_once '../../models/Deposito.php';
require_once '../../models/Sitio.php';

$modelo = new Deposito();
$depositos = $modelo->obtenerTodos();
$sitios = (new Sitio())->obtenerActivos();
$tipos = $modelo->obtenerTiposDeposito();

$error = $_SESSION['deposito_error'] ?? null; unset($_SESSION['deposito_error']);
$exito = $_SESSION['deposito_exito'] ?? null; unset($_SESSION['deposito_exito']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Depósitos</h3>
        <p class="text-muted mb-0">Depósitos de agua identificados dentro de cada sitio.</p>
    </div>
    <button type="button" class="btn btn-gemo" onclick="nuevoDeposito()"><i class="fas fa-plus me-1"></i> Nuevo depósito</button>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
<?php if (empty($sitios)): ?>
    <div class="alert alert-warning">Primero debes <a href="sitios.php">registrar un sitio activo</a> antes de crear depósitos.</div>
<?php endif; ?>

<div class="card card-round mb-4" id="card-formulario" style="display:none;">
    <div class="card-header"><h4 class="card-title" id="titulo-formulario">Nuevo depósito</h4></div>
    <div class="card-body">
        <form method="post" action="../../controllers/DepositoController.php" id="form-deposito">
            <input type="hidden" name="accion" id="f-accion" value="crear">
            <input type="hidden" name="id_deposito" id="f-id_deposito" value="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Sitio *</label>
                    <select name="id_sitio" id="f-id_sitio" class="form-select" required>
                        <option value="">Seleccione un sitio</option>
                        <?php foreach ($sitios as $s): ?>
                            <option value="<?= (int)$s['id_sitio'] ?>">
                                #<?= (int)$s['id_sitio'] ?> — <?= htmlspecialchars($s['direccion']) ?> (<?= htmlspecialchars($s['barrios']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tipo de depósito *</label>
                    <select name="id_tipo_deposito" id="f-id_tipo_deposito" class="form-select" required>
                        <option value="">Seleccione un tipo</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= (int)$t['id_tipo_deposito'] ?>"><?= htmlspecialchars($t['descripcion']) ?></option>
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
                    <tr><th>#</th><th>Sitio</th><th>Tipo</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php if (empty($depositos)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No hay depósitos registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($depositos as $d): ?>
                    <tr>
                        <td><?= (int)$d['id_deposito'] ?></td>
                        <td><?= htmlspecialchars($d['sitio_direccion']) ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($d['tipo_deposito']) ?></span></td>
                        <td>
                            <?php if ($d['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inhabilitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='editarDeposito(<?= json_encode($d, JSON_HEX_APOS) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="post" action="../../controllers/DepositoController.php" class="d-inline">
                                <input type="hidden" name="id_deposito" value="<?= (int)$d['id_deposito'] ?>">
                                <?php if ($d['activo']): ?>
                                    <input type="hidden" name="accion" value="inhabilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Inhabilitar este depósito?')"><i class="fas fa-ban"></i></button>
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
function nuevoDeposito() {
    document.getElementById('titulo-formulario').textContent = 'Nuevo depósito';
    document.getElementById('f-accion').value = 'crear';
    document.getElementById('f-id_deposito').value = '';
    document.getElementById('f-id_sitio').value = '';
    document.getElementById('f-id_tipo_deposito').value = '';
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function editarDeposito(deposito) {
    document.getElementById('titulo-formulario').textContent = 'Editar depósito #' + deposito.id_deposito;
    document.getElementById('f-accion').value = 'actualizar';
    document.getElementById('f-id_deposito').value = deposito.id_deposito;
    document.getElementById('f-id_sitio').value = deposito.id_sitio;
    document.getElementById('f-id_tipo_deposito').value = deposito.id_tipo_deposito;
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
