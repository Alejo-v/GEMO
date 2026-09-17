<?php
$pageTitle = 'GEMO | Tipos de depósito';
require_once '../../includes/auxterreno_header.php';
require_once '../../models/TipoDeposito.php';

$modelo = new TipoDeposito();
$tipos = $modelo->obtenerTodos();

$error = $_SESSION['tipo_deposito_error'] ?? null; unset($_SESSION['tipo_deposito_error']);
$exito = $_SESSION['tipo_deposito_exito'] ?? null; unset($_SESSION['tipo_deposito_exito']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Tipos de depósito</h3>
        <p class="text-muted mb-0">Catálogo de tipos de depósito (Piscina abandonada, Aguas estancadas, Fuente o pila de agua, Construcción abandonada, etc.).</p>
    </div>
    <button type="button" class="btn btn-gemo" onclick="nuevoTipo()"><i class="fas fa-plus me-1"></i> Nuevo tipo</button>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>

<div class="card card-round mb-4" id="card-formulario" style="display:none;">
    <div class="card-header"><h4 class="card-title" id="titulo-formulario">Nuevo tipo de depósito</h4></div>
    <div class="card-body">
        <form method="post" action="../../controllers/TipoDepositoController.php" id="form-tipo-deposito">
            <input type="hidden" name="accion" id="f-accion" value="crear">
            <input type="hidden" name="id_tipo_deposito" id="f-id_tipo_deposito" value="">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">Descripción *</label>
                    <input type="text" name="descripcion" id="f-descripcion" class="form-control" maxlength="50" required>
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
                    <tr><th>#</th><th>Descripción</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                <?php if (empty($tipos)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No hay tipos de depósito registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($tipos as $t): ?>
                    <tr>
                        <td><?= (int)$t['id_tipo_deposito'] ?></td>
                        <td><?= htmlspecialchars($t['descripcion']) ?></td>
                        <td>
                            <?php if ($t['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inhabilitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='editarTipo(<?= json_encode($t, JSON_HEX_APOS) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="post" action="../../controllers/TipoDepositoController.php" class="d-inline">
                                <input type="hidden" name="id_tipo_deposito" value="<?= (int)$t['id_tipo_deposito'] ?>">
                                <?php if ($t['activo']): ?>
                                    <input type="hidden" name="accion" value="inhabilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Inhabilitar este tipo de depósito?')"><i class="fas fa-ban"></i></button>
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
function nuevoTipo() {
    document.getElementById('titulo-formulario').textContent = 'Nuevo tipo de depósito';
    document.getElementById('f-accion').value = 'crear';
    document.getElementById('f-id_tipo_deposito').value = '';
    document.getElementById('f-descripcion').value = '';
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function editarTipo(tipo) {
    document.getElementById('titulo-formulario').textContent = 'Editar tipo de depósito #' + tipo.id_tipo_deposito;
    document.getElementById('f-accion').value = 'actualizar';
    document.getElementById('f-id_tipo_deposito').value = tipo.id_tipo_deposito;
    document.getElementById('f-descripcion').value = tipo.descripcion;
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
