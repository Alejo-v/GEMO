<?php
require_once __DIR__ . '/../../models/Sitio.php';

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
                <div class="col-12">
                    <label class="form-label"><i class="fas fa-road me-1"></i>Dirección estructurada *</label>
                    <div class="row g-2">
                        <div class="col-md-3"><label class="form-label small">Tipo de vía *</label><select name="tipo_via" id="f-tipo_via" class="form-select" required><option value="">Seleccione</option><option>Calle</option><option>Carrera</option><option>Avenida</option><option>Diagonal</option><option>Transversal</option><option>Circular</option><option>Autopista</option><option>Vía</option><option>Kilómetro</option></select></div>
                        <div class="col-md-2"><label class="form-label small">Número *</label><input type="text" name="numero_via" id="f-numero_via" class="form-control" maxlength="4" pattern="[0-9]{1,4}" inputmode="numeric" required></div>
                        <div class="col-md-1"><label class="form-label small">Letra</label><input type="text" name="letra_via" id="f-letra_via" class="form-control" maxlength="2" pattern="[A-Za-z]{1,2}"></div>
                        <div class="col-md-2"><label class="form-label small">Orientación</label><select name="orientacion" id="f-orientacion" class="form-select"><option value="">Sin orientación</option><option>Norte</option><option>Sur</option><option>Este</option><option>Oeste</option></select></div>
                        <div class="col-md-4">
                            <label class="form-label small">Nomenclatura *</label>
                            <input type="text" id="f-placa-metros" class="form-control" maxlength="10" placeholder="Ej: 79A-10" pattern="[0-9]{1,4}[A-Za-z]{0,2}-[0-9]{1,4}" required>
                            <input type="hidden" name="numero_placa" id="f-numero_placa">
                            <input type="hidden" name="letra_placa" id="f-letra_placa">
                            <input type="hidden" name="numero_metros" id="f-numero_metros">
                        </div>
                        <div class="col-12"><label class="form-label small">Complemento / apartamento / torre (opcional)</label><input type="text" name="complemento" id="f-complemento" class="form-control" maxlength="20" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9 .#\-/]+"></div>
                    </div>
                    <input type="hidden" name="direccion" id="f-direccion">
                    <small class="text-muted">Nomenclatura: escríbalos como en el ejemplo (número, letra opcional, guion y metros). Ejemplo completo: Calle 1A Oeste # 79A-10</small>
                </div>
                <div class="col-12">
                    <label class="form-label">Barrios que abarca *</label>
                    <input type="text" class="form-control mb-2" placeholder="Escriba para filtrar barrios o comunas…" data-checklist-filtro="lista-barrios-sitio">
                    <div class="row gemo-checklist-scroll" id="lista-barrios-sitio">
                        <?php foreach ($barrios as $b): ?>
                            <div class="col-md-4" data-checklist-item>
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
                    <tr>
                        <th>#</th><th>Dirección</th><th>Barrios</th><th>Estado</th><th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($sitios)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No hay sitios registrados.</td></tr>
                <?php endif; ?>
                <?php foreach ($sitios as $s): ?>
                    <tr>
                        <td><?= (int)$s['id_sitio'] ?></td>
                        <td><?= htmlspecialchars($s['direccion']) ?></td>
                        <td><?= htmlspecialchars($s['barrios']) ?></td>
                        <td>
                            <?php if ($s['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inhabilitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-outline-primary" title="Editar"
                                    onclick='editarSitio(<?= json_encode($s, JSON_HEX_APOS) ?>, <?= json_encode($s['ids_barrios']) ?>)'>
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="post" action="../../controllers/SitioController.php" class="d-inline">
                                <input type="hidden" name="id_sitio" value="<?= (int)$s['id_sitio'] ?>">
                                <?php if ($s['activo']): ?>
                                    <input type="hidden" name="accion" value="inhabilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Inhabilitar" onclick="return confirm('¿Inhabilitar este sitio?')"><i class="fas fa-ban"></i></button>
                                <?php else: ?>
                                    <input type="hidden" name="accion" value="habilitar">
                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Habilitar"><i class="fas fa-check"></i></button>
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
    document.getElementById('f-tipo_via').value = '';
    document.getElementById('f-numero_via').value = '';
    document.getElementById('f-letra_via').value = '';
    document.getElementById('f-orientacion').value = '';
    document.getElementById('f-placa-metros').value = '';
    document.getElementById('f-numero_placa').value = '';
    document.getElementById('f-letra_placa').value = '';
    document.getElementById('f-numero_metros').value = '';
    document.getElementById('f-complemento').value = '';
    document.getElementById('f-direccion').value = '';
    document.querySelectorAll('.barrio-checkbox').forEach(function (c) { c.checked = false; });
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function editarSitio(sitio, idsBarrios) {
    document.getElementById('titulo-formulario').textContent = 'Editar sitio #' + sitio.id_sitio;
    document.getElementById('f-accion').value = 'actualizar';
    document.getElementById('f-id_sitio').value = sitio.id_sitio;
    cargarDireccion(sitio.direccion);
    document.querySelectorAll('.barrio-checkbox').forEach(function (c) {
        c.checked = idsBarrios.map(String).includes(c.value);
    });
    document.getElementById('card-formulario').style.display = '';
    document.getElementById('card-formulario').scrollIntoView({ behavior: 'smooth' });
}

function cargarDireccion(direccion) {
    var d = String(direccion || '').trim();
    var m = d.match(/^(Calle|Carrera|Avenida|Diagonal|Transversal|Circular|Autopista|Vía|Via|Kilómetro)\s+([0-9]{1,4})([A-Za-z]{1,2})?(?:\s+(Norte|Sur|Este|Oeste))?\s*#\s*([0-9]{1,4})([A-Za-z]{1,2})?\s*-\s*([0-9]{1,4})(?:\s+(.*))?$/i);
    if (!m) {
        alert('La dirección existente no tiene el formato estructurado esperado. Revísela antes de guardar.');
        ['f-tipo_via','f-numero_via','f-letra_via','f-orientacion','f-complemento'].forEach(function(id){ document.getElementById(id).value=''; });
        document.getElementById('f-placa-metros').value = '';
        return;
    }
    document.getElementById('f-tipo_via').value = m[1].charAt(0).toUpperCase() + m[1].slice(1).toLowerCase();
    document.getElementById('f-numero_via').value = m[2];
    document.getElementById('f-letra_via').value = m[3] || '';
    document.getElementById('f-orientacion').value = m[4] ? m[4].charAt(0).toUpperCase() + m[4].slice(1).toLowerCase() : '';
    document.getElementById('f-placa-metros').value = m[5] + (m[6] || '') + '-' + m[7];
    document.getElementById('f-complemento').value = m[8] || '';
}

function separarPlacaMetros() {
    var valor = document.getElementById('f-placa-metros').value.trim().toUpperCase();
    var m = valor.match(/^([0-9]{1,4})([A-Za-z]{0,2})-([0-9]{1,4})$/);
    if (!m) {
        return null;
    }
    document.getElementById('f-numero_placa').value = m[1];
    document.getElementById('f-letra_placa').value = m[2] || '';
    document.getElementById('f-numero_metros').value = m[3];
    return { placa: m[1], letra: m[2] || '', metros: m[3] };
}

function construirDireccion() {
    var partes = separarPlacaMetros();
    var tipo = document.getElementById('f-tipo_via').value.trim();
    var numero = document.getElementById('f-numero_via').value.trim();
    var letra = document.getElementById('f-letra_via').value.trim().toUpperCase();
    var orientacion = document.getElementById('f-orientacion').value.trim();
    var complemento = document.getElementById('f-complemento').value.trim();
    if (!partes) {
        document.getElementById('f-direccion').value = '';
        return '';
    }
    var direccion = tipo + ' ' + numero + letra + (orientacion ? ' ' + orientacion : '') + ' # ' + partes.placa + partes.letra + '-' + partes.metros;
    if (complemento) direccion += ' ' + complemento;
    document.getElementById('f-direccion').value = direccion;
    return direccion;
}

document.getElementById('form-sitio').addEventListener('submit', function(e) {
    var partes = separarPlacaMetros();
    if (!partes) {
        e.preventDefault();
        alert('Escriba la nomenclatura con el formato correcto, por ejemplo: 79A-10');
        document.getElementById('f-placa-metros').focus();
        return;
    }
    var direccion = construirDireccion();
    if (direccion.length > 50) {
        e.preventDefault();
        alert('La dirección completa no puede superar 50 caracteres.');
    }
});

function cerrarFormulario() {
    document.getElementById('card-formulario').style.display = 'none';
}

<?php if ($error || $exito): ?>
document.getElementById('card-formulario').style.display = <?= $error ? "''" : "'none'" ?>;
<?php endif; ?>
</script>
