<?php
$pageTitle = 'GEMO | Sitios';
require_once '../../includes/auxterreno_header.php';
require_once '../../models/Sitio.php';

$modelo = new Sitio();
$sitios = $modelo->obtenerTodos();
$barrios = $modelo->obtenerBarrios();

$error = $_SESSION['sitio_error'] ?? null; unset($_SESSION['sitio_error']);
$exito = $_SESSION['sitio_exito'] ?? null; unset($_SESSION['sitio_exito']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Sitios</h3>
        <p class="text-muted mb-0">Lugares donde se hace control biológico (piscinas, aguas estancadas, etc.).</p>
    </div>
    <button type="button" class="btn btn-gemo" onclick="nuevoSitio()"><i class="fas fa-plus me-1"></i> Nuevo sitio</button>
</div>

<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>

<div class="card card-round mb-4" id="card-formulario" style="display:none;">
    <div class="card-header"><h4 class="card-title" id="titulo-formulario">Nuevo sitio</h4></div>
    <div class="card-body">
        <form method="post" action="../../controllers/SitioController.php" id="form-sitio">
            <input type="hidden" name="accion" id="f-accion" value="crear">
            <input type="hidden" name="id_sitio" id="f-id_sitio" value="">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Dirección *</label>
                    <input type="text" name="direccion" id="f-direccion" class="form-control" maxlength="50" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Latitud</label>
                    <input type="number" step="0.000001" min="-90" max="90" name="latitud" id="f-latitud" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Longitud</label>
                    <input type="number" step="0.000001" min="-180" max="180" name="longitud" id="f-longitud" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Barrios que abarca *</label>
                    <div class="row">
                        <?php foreach ($barrios as $b): ?>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input barrio-checkbox" type="checkbox" name="barrios[]"
                                           value="<?= (int)$b['id_barrio'] ?>" id="barrio-<?= (int)$b['id_barrio'] ?>">
                                    <label class="form-check-label" for="barrio-<?= (int)$b['id_barrio'] ?>">
                                        <?= htmlspecialchars($b['nombre']) ?> <small class="text-muted">(<?= htmlspecialchars($b['comuna']) ?>)</small>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($barrios)): ?>
                            <p class="text-danger mb-0">No hay barrios en el sistema todavía.</p>
                        <?php endif; ?>
                    </div>
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
                    <tr>
                        <th>#</th><th>Dirección</th><th>Barrios</th><th>Latitud</th><th>Longitud</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($sitios)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No hay sitios registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($sitios as $s): ?>
                    <tr>
                        <td><?= (int)$s['id_sitio'] ?></td>
                        <td><?= htmlspecialchars($s['direccion']) ?></td>
                        <td><?= htmlspecialchars($s['barrios']) ?></td>
                        <td><?= htmlspecialchars($s['latitud'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['longitud'] ?? '—') ?></td>
                        <td>
                            <?php if ($s['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inhabilitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    onclick='editarSitio(<?= json_encode($s, JSON_HEX_APOS) ?>, <?= json_encode($s['ids_barrios']) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="post" action="../../controllers/SitioController.php" class="d-inline">
                                <input type="hidden" name="id_sitio" value="<?= (int)$s['id_sitio'] ?>">
                                <?php if ($s['activo']): ?>
                                    <input type="hidden" name="accion" value="inhabilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Inhabilitar este sitio?')"><i class="fas fa-ban"></i></button>
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
function nuevoSitio() {
    document.getElementById('titulo-formulario').textContent = 'Nuevo sitio';
    document.getElementById('f-accion').value = 'crear';
    document.getElementById('f-id_sitio').value = '';
    document.getElementById('f-direccion').value = '';
    document.getElementById('f-latitud').value = '';
    document.getElementById('f-longitud').value = '';
    document.querySelectorAll('.barrio-checkbox').forEach(function (c) { c.checked = false; });
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function editarSitio(sitio, idsBarrios) {
    document.getElementById('titulo-formulario').textContent = 'Editar sitio #' + sitio.id_sitio;
    document.getElementById('f-accion').value = 'actualizar';
    document.getElementById('f-id_sitio').value = sitio.id_sitio;
    document.getElementById('f-direccion').value = sitio.direccion;
    document.getElementById('f-latitud').value = sitio.latitud ?? '';
    document.getElementById('f-longitud').value = sitio.longitud ?? '';
    document.querySelectorAll('.barrio-checkbox').forEach(function (c) {
        c.checked = idsBarrios.map(String).includes(c.value);
    });
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
