<?php
$pageTitle = 'GEMO | Información personal';
require_once '../../includes/coordzoo_header.php';
require_once '../../models/Usuario.php';

$usuario = (new Usuario())->buscarPorId((int) $_SESSION['usuario_id']);

$exito = $_SESSION['perfil_exito'] ?? null; unset($_SESSION['perfil_exito']);
$error = $_SESSION['perfil_error'] ?? null; unset($_SESSION['perfil_error']);
?>
<div class="mb-4">
    <h3 class="fw-bold mb-1">Información personal</h3>
    <p class="text-muted mb-0">Consulte sus datos y actualice su teléfono o contraseña.</p>
</div>

<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if (!$usuario): ?>
    <div class="alert alert-warning">No fue posible cargar su información.</div>
<?php else: ?>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Mis datos</h4></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input class="form-control" value="<?= htmlspecialchars($usuario['nombres']) ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Apellido</label>
                <input class="form-control" value="<?= htmlspecialchars($usuario['apellidos']) ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Documento</label>
                <input class="form-control" value="<?= htmlspecialchars($usuario['documento']) ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Correo electrónico</label>
                <input class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label">Rol</label>
                <input class="form-control" value="<?= htmlspecialchars($usuario['nombre_rol']) ?>" disabled>
            </div>
        </div>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header"><h4 class="card-title">Teléfono</h4></div>
    <div class="card-body">
        <form method="post" action="../../controllers/PerfilController.php" class="row g-3 align-items-end" novalidate>
            <input type="hidden" name="accion" value="actualizar_telefono">
            <div class="col-md-6">
                <label class="form-label">Número de teléfono *</label>
                <input class="form-control" type="tel" name="telefono" maxlength="20" required
                       value="<?= htmlspecialchars($usuario['telefono']) ?>">
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-gemo"><i class="fas fa-save me-1"></i> Guardar teléfono</button>
            </div>
        </form>
    </div>
</div>

<div class="card card-round mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">Contraseña</h4>
        <button type="button" class="btn btn-outline-success btn-sm" id="btnCambiarPassword">
            <i class="fas fa-key me-1"></i> Cambiar contraseña
        </button>
    </div>
    <div class="card-body" id="formCambiarPasswordWrap" style="display:none;">
        <form method="post" action="../../controllers/PerfilController.php" id="formCambiarPassword" novalidate>
            <input type="hidden" name="accion" value="actualizar_password">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Contraseña actual *</label>
                    <input class="form-control" type="password" name="password_actual" required autocomplete="current-password">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nueva contraseña *</label>
                    <input class="form-control" type="password" name="password_nueva" minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}" required autocomplete="new-password">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Confirmar nueva contraseña *</label>
                    <input class="form-control" type="password" name="password_confirmar" minlength="8" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}" required autocomplete="new-password">
                </div>
            </div>
            <small class="text-muted d-block mt-2">Mínimo 8 caracteres, con mayúscula, minúscula, número y carácter especial. Las dos contraseñas nuevas deben coincidir.</small>
            <button type="submit" class="btn btn-gemo mt-3"><i class="fas fa-save me-1"></i> Guardar contraseña</button>
        </form>
    </div>
</div>

<script>
(function () {
    var btn = document.getElementById('btnCambiarPassword');
    var wrap = document.getElementById('formCambiarPasswordWrap');
    if (!btn || !wrap) return;
    btn.addEventListener('click', function () {
        wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
    });

    var form = document.getElementById('formCambiarPassword');
    form.addEventListener('submit', function (e) {
        var nueva = form.querySelector('[name="password_nueva"]');
        var confirmar = form.querySelector('[name="password_confirmar"]');
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
</script>

<?php endif; ?>
<?php require_once '../../includes/coordzoo_footer.php'; ?>
