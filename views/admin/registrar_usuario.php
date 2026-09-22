<?php
$pageTitle = 'GEMO | Registrar usuario';
require_once '../../includes/admin_header.php';
require_once '../../models/Usuario.php';
$roles = (new Usuario())->obtenerRoles();
$error = $_SESSION['usuario_error'] ?? null;
unset($_SESSION['usuario_error']);
?>

<div class="mb-4">
    <h3 class="fw-bold mb-1">Registrar usuario</h3>
    <p class="text-muted mb-0">Complete la información del nuevo usuario.</p>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="../../controllers/UsuarioController.php">
    <input type="hidden" name="accion" value="registrar">

    <div class="card card-round mb-4">
        <div class="card-header">
            <h4 class="card-title">Información personal</h4>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre *</label>
                    <input name="nombres" class="form-control" maxlength="50"
                           pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü .'-]{2,50}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Apellido *</label>
                    <input name="apellidos" class="form-control" maxlength="50"
                           pattern="[A-Za-zÁÉÍÓÚáéíóúÑñÜü .'-]{2,50}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Número de cédula *</label>
                    <input name="documento" class="form-control" maxlength="50"
                           pattern="[0-9A-Za-z.-]{5,50}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Fecha de nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" class="form-control"
                           max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                           min="<?= date('Y-m-d', strtotime('-80 years')) ?>"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Número de teléfono *</label>
                    <input name="telefono" class="form-control" maxlength="20"
                           pattern="[0-9+() .-]{7,20}" inputmode="tel" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-round mb-4">
        <div class="card-header">
            <h4 class="card-title">Información de acceso</h4>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Correo electrónico *</label>
                    <input type="email" name="correo" class="form-control" maxlength="150" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" name="password" class="form-control" minlength="8"
                           pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,}" required>
                    <small class="text-muted">Mínimo 8 caracteres, con mayúscula, minúscula, número y carácter especial.</small>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Confirmar contraseña *</label>
                    <input type="password" name="confirmar_password" class="form-control" minlength="8" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-round mb-4">
        <div class="card-header">
            <h4 class="card-title">Información del sistema</h4>
        </div>
        <div class="card-body">
            <label class="form-label">Rol *</label>
            <select name="id_rol" class="form-select" required>
                <option value="">Seleccione un rol</option>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= (int) $r['id_rol'] ?>"><?= htmlspecialchars($r['nombre_rol']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-gemo" type="submit">
            <i class="fas fa-save me-1"></i> Registrar usuario
        </button>
        <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php require_once '../../includes/admin_footer.php'; ?>