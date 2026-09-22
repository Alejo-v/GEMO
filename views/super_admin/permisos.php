<?php
$pageTitle = 'GEMO | Permisos';
require_once __DIR__ . '/../../includes/superadmin_header.php';
require_once __DIR__ . '/../../models/Permiso.php';

$modelo = new Permiso();
$rolesConPermisos = $modelo->obtenerAgrupadoPorRol();

$exito = $_SESSION['permiso_exito'] ?? null; unset($_SESSION['permiso_exito']);
$error = $_SESSION['permiso_error'] ?? null; unset($_SESSION['permiso_error']);
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h3 class="fw-bold mb-1">Gestión de permisos</h3>
        <p class="text-muted mb-0">Habilita o deshabilita el acceso de cada rol a las páginas del sistema.</p>
    </div>
</div>

<?php if ($exito): ?><div class="alert alert-success"><?= htmlspecialchars($exito) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<?php if (empty($rolesConPermisos)): ?>
    <div class="alert alert-warning">
        Aún no hay datos de permisos en la base de datos. Ejecuta <code>sql/alter_permisos.sql</code> para crear
        y cargar la tabla <code>permiso_rol</code>.
    </div>
<?php endif; ?>

<div class="row">
<?php foreach ($rolesConPermisos as $rol): ?>
    <div class="col-md-6 mb-4">
        <div class="card card-round h-100">
            <div class="card-body">
                <h4 class="card-title mb-3">
                    <i class="fas fa-user-shield me-1 text-muted"></i>
                    <?= htmlspecialchars($rol['nombre_rol']) ?>
                </h4>
                <ul class="list-group list-group-flush">
                    <?php foreach ($rol['permisos'] as $permiso): ?>
                        <li class="list-group-item d-flex align-items-center justify-content-between px-0">
                            <span><?= htmlspecialchars($permiso['etiqueta']) ?></span>
                            <form method="post" action="../../controllers/PermisoController.php" class="permiso-form">
                                <input type="hidden" name="accion" value="cambiar_estado">
                                <input type="hidden" name="id_permiso" value="<?= (int) $permiso['id_permiso'] ?>">
                                <input type="hidden" name="activo" value="<?= $permiso['activo'] ? '0' : '1' ?>">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input permiso-switch" type="checkbox" role="switch"
                                           <?= $permiso['activo'] ? 'checked' : '' ?>
                                           title="<?= $permiso['activo'] ? 'Deshabilitar' : 'Habilitar' ?> esta página para <?= htmlspecialchars($rol['nombre_rol'], ENT_QUOTES) ?>">
                                </div>
                            </form>
                        </li>
                    <?php endforeach; ?>
                    <?php if (empty($rol['permisos'])): ?>
                        <li class="list-group-item px-0 text-muted">Este rol no tiene páginas registradas.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
(function () {
  document.querySelectorAll('.permiso-switch').forEach(function (switchInput) {
    switchInput.addEventListener('change', function () {
      var form = switchInput.closest('.permiso-form');
      var vaAHabilitar = form.querySelector('[name="activo"]').value === '1';
      if (!vaAHabilitar) {
        var confirmado = confirm('¿Quitar el acceso a esta página para este rol?');
        if (!confirmado) {
          switchInput.checked = true;
          return;
        }
      }
      form.submit();
    });
  });
})();
</script>

<?php require_once __DIR__ . '/../../includes/superadmin_footer.php'; ?>
