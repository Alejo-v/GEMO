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

/* ==== Selects filtrables (comuna / barrio): escribir para buscar en vez de ver la lista completa ==== */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('select.gemo-select-filtrable').forEach(function (select) {
        if (select.dataset.gemoEnhanced) return;
        select.dataset.gemoEnhanced = '1';

        var wrapper = document.createElement('div');
        wrapper.className = 'gemo-select-filtrable-wrap';
        select.parentNode.insertBefore(wrapper, select);

        var input = document.createElement('input');
        input.type = 'text';
        input.className = select.className.replace('gemo-select-filtrable', '').trim() || 'form-select';
        input.setAttribute('autocomplete', 'off');
        input.placeholder = select.dataset.placeholder || 'Escriba para buscar…';

        var lista = document.createElement('div');
        lista.className = 'gemo-select-filtrable-lista';
        lista.hidden = true;

        wrapper.appendChild(input);
        wrapper.appendChild(lista);
        wrapper.appendChild(select);
        select.classList.add('gemo-select-filtrable-oculto');

        var opciones = Array.prototype.slice.call(select.options).filter(function (o) { return o.value !== ''; });

        function sincronizarTexto() {
            var actual = select.options[select.selectedIndex];
            input.value = actual && actual.value !== '' ? actual.textContent.trim() : '';
        }

        function renderLista(filtro) {
            var f = (filtro || '').trim().toLowerCase();
            lista.innerHTML = '';
            var visibles = opciones.filter(function (o) {
                return !f || o.textContent.toLowerCase().indexOf(f) !== -1;
            });
            if (!visibles.length) {
                var vacio = document.createElement('div');
                vacio.className = 'gemo-select-filtrable-vacio';
                vacio.textContent = 'Sin coincidencias';
                lista.appendChild(vacio);
            }
            visibles.forEach(function (o) {
                var item = document.createElement('div');
                item.className = 'gemo-select-filtrable-item';
                item.textContent = o.textContent.trim();
                item.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    select.value = o.value;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    sincronizarTexto();
                    lista.hidden = true;
                });
                lista.appendChild(item);
            });
            lista.hidden = false;
        }

        input.addEventListener('focus', function () { renderLista(''); });
        input.addEventListener('input', function () { renderLista(input.value); });
        input.addEventListener('blur', function () {
            setTimeout(function () {
                lista.hidden = true;
                sincronizarTexto();
            }, 150);
        });

        sincronizarTexto();
    });
});

/* ==== Filtro de texto para listas de checkboxes (ej. barrios que abarca un sitio) ==== */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-checklist-filtro]').forEach(function (input) {
        var contenedor = document.getElementById(input.getAttribute('data-checklist-filtro'));
        if (!contenedor) return;
        input.addEventListener('input', function () {
            var f = input.value.trim().toLowerCase();
            contenedor.querySelectorAll('[data-checklist-item]').forEach(function (item) {
                var texto = item.textContent.toLowerCase();
                item.style.display = (!f || texto.indexOf(f) !== -1) ? '' : 'none';
            });
        });
    });
});
