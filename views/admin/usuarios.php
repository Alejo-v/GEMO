<?php
$pageTitle='GEMO | Usuarios';
require_once '../../includes/admin_header.php';
require_once '../../models/Usuario.php';
$usuarioModel = new Usuario();
$usuarios = $usuarioModel->obtenerUsuarios();
$roles = $usuarioModel->obtenerRoles();
$exito=$_SESSION['usuario_exito']??null; unset($_SESSION['usuario_exito']);
$error=$_SESSION['usuario_error']??null; unset($_SESSION['usuario_error']);
?>
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h3 class="fw-bold mb-1">Usuarios</h3>
        <p class="text-muted mb-0">Usuarios registrados en GEMO.</p>
    </div>
    <a href="registrar_usuario.php" class="btn btn-gemo"><i class="fas fa-user-plus me-1"></i> Registrar usuario</a>
</div>
<?php if($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card card-round">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label small">Buscar por número de documento</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="filtro-documento" class="form-control" placeholder="Ej: 1006123456" inputmode="numeric" autocomplete="off">
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tabla-usuarios">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Documento</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($usuarios as $u): ?>
                    <tr data-documento="<?= htmlspecialchars($u['documento']) ?>">
                        <td><?= (int)$u['id_usuario'] ?></td>
                        <td><strong><?= htmlspecialchars($u['nombres'].' '.$u['apellidos']) ?></strong></td>
                        <td><?= htmlspecialchars($u['documento']) ?></td>
                        <td><?= htmlspecialchars($u['correo']) ?></td>
                        <td><?= htmlspecialchars($u['telefono']) ?></td>
                        <td><span class="badge badge-success"><?= htmlspecialchars($u['nombre_rol']) ?></span></td>
                        <td>
                            <?php if ($u['activo']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inhabilitado</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end">
                            <button type="button"
                                    class="btn btn-sm btn-outline-success"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditarUsuario"
                                    data-id="<?= (int)$u['id_usuario'] ?>"
                                    data-nombre="<?= htmlspecialchars($u['nombres'].' '.$u['apellidos'], ENT_QUOTES) ?>"
                                    data-apellidos="<?= htmlspecialchars($u['apellidos'], ENT_QUOTES) ?>"
                                    data-correo="<?= htmlspecialchars($u['correo'], ENT_QUOTES) ?>"
                                    data-rol="<?= (int)$u['id_rol'] ?>">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <?php if ((int)$u['id_usuario'] === (int) ($_SESSION['usuario_id'] ?? 0)): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                        title="No puede inhabilitar su propio usuario">
                                    <?= $u['activo'] ? '<i class="fas fa-ban"></i> Inhabilitar' : '<i class="fas fa-check"></i> Habilitar' ?>
                                </button>
                            <?php else: ?>
                                <form method="post" action="../../controllers/UsuarioController.php" class="d-inline"
                                      onsubmit="return confirm('<?= $u['activo'] ? '¿Inhabilitar' : '¿Habilitar' ?> a <?= htmlspecialchars($u['nombres'].' '.$u['apellidos'], ENT_QUOTES) ?>?');">
                                    <input type="hidden" name="accion" value="cambiar_estado">
                                    <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                    <input type="hidden" name="activo" value="<?= $u['activo'] ? '0' : '1' ?>">
                                    <?php if ($u['activo']): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-ban"></i> Inhabilitar
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check"></i> Habilitar
                                        </button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($usuarios)): ?>
                    <tr><td colspan="8" class="text-center text-muted">Aún no hay usuarios registrados.</td></tr>
                <?php endif; ?>
                <tr id="fila-sin-resultados" style="display:none;"><td colspan="8" class="text-center text-muted">Ningún usuario coincide con ese número de documento.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal editar usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="../../controllers/UsuarioController.php" id="formEditarUsuario" novalidate>
        <input type="hidden" name="accion" value="editar">
        <input type="hidden" name="id_usuario" id="edit_id_usuario">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEditarUsuarioLabel">Editar usuario — <span id="edit_nombre_usuario"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small">El nombre, documento, fecha de nacimiento y teléfono no se modifican desde aquí.</p>
          <div class="mb-3">
            <label class="form-label">Apellido *</label>
            <input class="form-control" name="apellidos" id="edit_apellidos" maxlength="50" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Correo electrónico *</label>
            <input type="email" class="form-control" name="correo" id="edit_correo" maxlength="150" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Rol *</label>
            <select class="form-select" name="id_rol" id="edit_id_rol" required>
              <?php foreach($roles as $r): ?>
                <option value="<?= (int)$r['id_rol'] ?>"><?= htmlspecialchars($r['nombre_rol']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <hr>
          <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input type="password" class="form-control" name="password" minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}" autocomplete="new-password">
            <small class="text-muted">Deje ambos campos en blanco para no cambiar la contraseña. Si cambia la contraseña: mínimo 8 caracteres, con mayúscula, minúscula, número y carácter especial.</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Confirmar nueva contraseña</label>
            <input type="password" class="form-control" name="confirmar_password" minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}" autocomplete="new-password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-gemo"><i class="fas fa-save me-1"></i> Guardar cambios</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function () {
  var modal = document.getElementById('modalEditarUsuario');
  if (!modal) return;
  modal.addEventListener('show.bs.modal', function (event) {
    var btn = event.relatedTarget;
    if (!btn) return;
    document.getElementById('edit_id_usuario').value = btn.getAttribute('data-id') || '';
    document.getElementById('edit_apellidos').value = btn.getAttribute('data-apellidos') || '';
    document.getElementById('edit_correo').value = btn.getAttribute('data-correo') || '';
    document.getElementById('edit_id_rol').value = btn.getAttribute('data-rol') || '';
    document.getElementById('edit_nombre_usuario').textContent = btn.getAttribute('data-nombre') || '';
    var form = document.getElementById('formEditarUsuario');
    form.querySelector('[name="password"]').value = '';
    form.querySelector('[name="confirmar_password"]').value = '';
  });

  var form = document.getElementById('formEditarUsuario');
  form.addEventListener('submit', function (e) {
    var nueva = form.querySelector('[name="password"]');
    var confirmar = form.querySelector('[name="confirmar_password"]');
    if (nueva.value === '' && confirmar.value === '') return;
    var patron = /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}$/;
    if (!patron.test(nueva.value)) {
      e.preventDefault();
      alert('La contraseña debe tener mínimo 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.');
      nueva.focus();
      return;
    }
    if (nueva.value !== confirmar.value) {
      e.preventDefault();
      alert('Las contraseñas nuevas no coinciden.');
      confirmar.focus();
    }
  });
})();

(function () {
  var input = document.getElementById('filtro-documento');
  if (!input) return;
  var filas = document.querySelectorAll('#tabla-usuarios tbody tr[data-documento]');
  var filaSinResultados = document.getElementById('fila-sin-resultados');
  input.addEventListener('input', function () {
    var termino = this.value.trim();
    var coincidencias = 0;
    filas.forEach(function (fila) {
      var coincide = termino === '' || fila.getAttribute('data-documento').indexOf(termino) !== -1;
      fila.style.display = coincide ? '' : 'none';
      if (coincide) coincidencias++;
    });
    filaSinResultados.style.display = (termino !== '' && coincidencias === 0) ? '' : 'none';
  });
})();
</script>
<?php require_once '../../includes/admin_footer.php'; ?>
