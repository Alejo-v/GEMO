<?php
$modeloUbicacion = new Ubicacion();
$comunas = $modeloUbicacion->obtenerComunas();
$barrios = $modeloUbicacion->obtenerBarrios();
$error = $_SESSION['ubicacion_error'] ?? null; unset($_SESSION['ubicacion_error']);
$exito = $_SESSION['ubicacion_exito'] ?? null; unset($_SESSION['ubicacion_exito']);
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1"><i class="fas fa-map-marked-alt me-2"></i>Barrios y comunas</h3>
        <p class="text-muted mb-0">Administre los catálogos de ubicación utilizados por los registros de terreno.</p>
    </div>
</div>

<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div><?php endif; ?>
<?php if ($exito): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($exito) ?></div><?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card card-round">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0"><i class="fas fa-city me-2"></i>Comunas</h4>
                <button type="button" class="btn btn-sm btn-gemo" onclick="nuevaComuna()"><i class="fas fa-plus me-1"></i>Nueva</button>
            </div>
            <div class="card-body">
                <form method="post" action="../../controllers/UbicacionController.php" id="form-comuna" class="mb-4">
                    <input type="hidden" name="accion" id="comuna-accion" value="crear_comuna">
                    <input type="hidden" name="id_comuna" id="comuna-id">
                    <label class="form-label">Nombre de la comuna *</label>
                    <input type="text" name="nombre" id="comuna-nombre" class="form-control" maxlength="120" minlength="1" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9 .'-]+" required>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-gemo" type="submit"><i class="fas fa-save me-1"></i>Guardar</button>
                        <button class="btn btn-secondary" type="button" onclick="nuevaComuna()"><i class="fas fa-eraser me-1"></i>Limpiar</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>#</th><th>Comuna</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?php foreach ($comunas as $c): ?>
                            <tr>
                                <td><?= (int)$c['id_comuna'] ?></td>
                                <td><?= htmlspecialchars($c['nombre']) ?></td>
                                <td class="text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="Editar" onclick='editarComuna(<?= json_encode($c, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i></button>
                                    <form method="post" action="../../controllers/UbicacionController.php" class="d-inline">
                                        <input type="hidden" name="accion" value="eliminar_comuna"><input type="hidden" name="id_comuna" value="<?= (int)$c['id_comuna'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('Solo se puede eliminar si la comuna no tiene barrios asociados. ¿Continuar?')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$comunas): ?><tr><td colspan="3" class="text-center text-muted">No hay comunas registradas.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-round">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0"><i class="fas fa-map-marker-alt me-2"></i>Barrios</h4>
                <button type="button" class="btn btn-sm btn-gemo" onclick="nuevoBarrio()"><i class="fas fa-plus me-1"></i>Nuevo</button>
            </div>
            <div class="card-body">
                <form method="post" action="../../controllers/UbicacionController.php" id="form-barrio" class="mb-4">
                    <input type="hidden" name="accion" id="barrio-accion" value="crear_barrio">
                    <input type="hidden" name="id_barrio" id="barrio-id">
                    <div class="row g-3">
                        <div class="col-md-7"><label class="form-label">Nombre del barrio *</label><input type="text" name="nombre" id="barrio-nombre" class="form-control" maxlength="120" minlength="1" pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü0-9 .'-]+" required></div>
                        <div class="col-md-5"><label class="form-label">Comuna *</label><select name="id_comuna" id="barrio-comuna" class="form-select" required><option value="">Seleccione</option><?php foreach($comunas as $c): ?><option value="<?= (int)$c['id_comuna'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?></select></div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-gemo" type="submit"><i class="fas fa-save me-1"></i>Guardar</button>
                        <button class="btn btn-secondary" type="button" onclick="nuevoBarrio()"><i class="fas fa-eraser me-1"></i>Limpiar</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>#</th><th>Barrio</th><th>Comuna</th><th>Acciones</th></tr></thead>
                        <tbody>
                        <?php foreach ($barrios as $b): ?>
                            <tr><td><?= (int)$b['id_barrio'] ?></td><td><?= htmlspecialchars($b['nombre']) ?></td><td><?= htmlspecialchars($b['comuna']) ?></td><td class="text-nowrap"><button type="button" class="btn btn-sm btn-outline-primary" title="Editar" onclick='editarBarrio(<?= json_encode($b, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i></button><form method="post" action="../../controllers/UbicacionController.php" class="d-inline"><input type="hidden" name="accion" value="eliminar_barrio"><input type="hidden" name="id_barrio" value="<?= (int)$b['id_barrio'] ?>"><button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('Si el barrio está siendo usado por un sitio o zoocriadero no se podrá eliminar. ¿Continuar?')"><i class="fas fa-trash"></i></button></form></td></tr>
                        <?php endforeach; ?>
                        <?php if (!$barrios): ?><tr><td colspan="4" class="text-center text-muted">No hay barrios registrados.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function nuevaComuna(){document.getElementById('comuna-accion').value='crear_comuna';document.getElementById('comuna-id').value='';document.getElementById('comuna-nombre').value='';document.getElementById('comuna-nombre').focus();}
function editarComuna(c){document.getElementById('comuna-accion').value='editar_comuna';document.getElementById('comuna-id').value=c.id_comuna;document.getElementById('comuna-nombre').value=c.nombre;document.getElementById('comuna-nombre').focus();}
function nuevoBarrio(){document.getElementById('barrio-accion').value='crear_barrio';document.getElementById('barrio-id').value='';document.getElementById('barrio-nombre').value='';document.getElementById('barrio-comuna').value='';document.getElementById('barrio-nombre').focus();}
function editarBarrio(b){document.getElementById('barrio-accion').value='editar_barrio';document.getElementById('barrio-id').value=b.id_barrio;document.getElementById('barrio-nombre').value=b.nombre;document.getElementById('barrio-comuna').value=b.id_comuna;document.getElementById('barrio-nombre').focus();}
</script>
