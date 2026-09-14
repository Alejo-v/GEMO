document.addEventListener('DOMContentLoaded', function () {
  const form = document.querySelector('form[action*="UsuarioController"]');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    const password = form.querySelector('[name="password"]');
    const confirm = form.querySelector('[name="confirmar_password"]');
    if (password && confirm && password.value !== confirm.value) {
      e.preventDefault();
      alert('Las contraseñas no coinciden.');
      confirm.focus();
    }
  });
});
