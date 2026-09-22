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

/* ==== Menú lateral: fix definitivo hamburguesa (móvil) + minimize (desktop) ====
 *
 * Problema: kaiadmin.min.js al cargar hace:
 *   $('.main-header .logo-header').html( $('.sidebar .logo-header').html() );
 * y borra el botón .gemo-mobile-toggler / .navbar-toggler del header.
 * Además enlaza .sidenav-toggler con un handler que a veces no coincide
 * con el botón visible tras el overwrite.
 *
 * Solución:
 * 1) Tras kaiadmin, reconstruimos un hamburguesa claro en el main-header.
 * 2) Quitamos los handlers de click de kaiadmin sobre .sidenav-toggler.
 * 3) Un solo handler (delegado) controla html.nav_open.
 * 4) No tocamos .toggle-sidebar (minimize en desktop lo sigue manejando kaiadmin).
 */
(function () {
  var NAV_OPEN = 'nav_open';

  function isMobile() {
    return window.matchMedia('(max-width: 991.5px)').matches;
  }

  function setNavOpen(open) {
    var html = document.documentElement;
    if (open) {
      html.classList.add(NAV_OPEN);
    } else {
      html.classList.remove(NAV_OPEN);
    }
    document.querySelectorAll('.sidenav-toggler, .gemo-mobile-toggler').forEach(function (btn) {
      if (open) {
        btn.classList.add('toggled');
      } else {
        btn.classList.remove('toggled');
      }
    });
  }

  function toggleNavOpen() {
    setNavOpen(!document.documentElement.classList.contains(NAV_OPEN));
  }

  /** Botón hamburguesa estándar que KaiAdmin espera en móvil */
  function buildHamburgerButton() {
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'navbar-toggler sidenav-toggler gemo-mobile-toggler';
    btn.setAttribute('aria-label', 'Abrir o cerrar menú');
    btn.setAttribute('aria-expanded', 'false');
    btn.innerHTML =
      '<span class="navbar-toggler-icon">' +
      '<i class="fas fa-bars" aria-hidden="true"></i>' +
      '</span>';
    return btn;
  }

  /**
   * Reconstruye el logo del main-header para móvil:
   * [hamburguesa] [logo centrado]
   * Sin depender del HTML copiado por kaiadmin.
   */
  function restoreMainHeaderToggler() {
    var logoHeader = document.querySelector('.main-header .logo-header');
    if (!logoHeader) return;

    // URL del logo (si ya hay un <a class="logo"> lo reutilizamos)
    var existingLogo = logoHeader.querySelector('a.logo');
    var logoHtml = existingLogo
      ? existingLogo.outerHTML
      : '<a href="#" class="logo"><img src="../../assets/img/branding/gemo-logo-white.png" alt="GEMO" class="gemo-top-brand-img"></a>';

    // Si el logo del sidebar tenía un href distinto, preferir el del sidebar
    var sidebarLogo = document.querySelector('.sidebar .logo-header a.logo');
    if (sidebarLogo && sidebarLogo.getAttribute('href')) {
      logoHtml =
        '<a href="' +
        sidebarLogo.getAttribute('href') +
        '" class="logo">' +
        (sidebarLogo.querySelector('img')
          ? sidebarLogo.querySelector('img').outerHTML.replace('gemo-brand-img', 'gemo-top-brand-img')
          : sidebarLogo.innerHTML) +
        '</a>';
    }

    // Limpiar y dejar solo hamburguesa + logo (estructura que el CSS móvil de KaiAdmin entiende)
    logoHeader.innerHTML = '';
    logoHeader.appendChild(buildHamburgerButton());
    var wrap = document.createElement('div');
    wrap.innerHTML = logoHtml;
    while (wrap.firstChild) {
      logoHeader.appendChild(wrap.firstChild);
    }
  }

  /** Quitar handlers de jQuery/kaiadmin sobre sidenav-toggler para evitar doble toggle */
  function unbindKaiadminSidenav() {
    if (typeof jQuery === 'undefined') return;
    try {
      jQuery('.sidenav-toggler').off('click');
      // Por si el handler quedó en document o en el collection original
      jQuery(document).off('click', '.sidenav-toggler');
    } catch (e) {
      /* ignore */
    }
  }

  function bindOurToggle() {
    // Delegación: funciona aunque se reemplace el HTML del logo
    document.addEventListener(
      'click',
      function (e) {
        var btn = e.target.closest('.sidenav-toggler, .gemo-mobile-toggler');
        if (!btn) return;

        // Solo en móvil el hamburguesa abre/cierra off-canvas.
        // En desktop el sidenav-toggler no debe pelearse con el minimize.
        if (!isMobile()) return;

        e.preventDefault();
        e.stopPropagation();
        if (typeof e.stopImmediatePropagation === 'function') {
          e.stopImmediatePropagation();
        }
        toggleNavOpen();
        btn.setAttribute(
          'aria-expanded',
          document.documentElement.classList.contains(NAV_OPEN) ? 'true' : 'false'
        );
      },
      true // capture: nos adelantamos a cualquier otro listener
    );

    // Cerrar al hacer click fuera del sidebar (móvil)
    document.addEventListener('click', function (e) {
      if (!isMobile()) return;
      if (!document.documentElement.classList.contains(NAV_OPEN)) return;
      var sidebar = document.querySelector('.sidebar');
      var btn = e.target.closest('.sidenav-toggler, .gemo-mobile-toggler');
      if (btn) return;
      if (sidebar && sidebar.contains(e.target)) return;
      setNavOpen(false);
    });

    // Al pasar a desktop, quitar nav_open para no dejar el layout desplazado
    window.addEventListener('resize', function () {
      if (!isMobile() && document.documentElement.classList.contains(NAV_OPEN)) {
        setNavOpen(false);
      }
    });
  }

  function init() {
    // kaiadmin ya corrió (script anterior en el footer). Deshacer el daño del overwrite.
    unbindKaiadminSidenav();
    restoreMainHeaderToggler();
    bindOurToggle();

    // Por si kaiadmin vuelve a tocar el DOM un tick después
    setTimeout(function () {
      unbindKaiadminSidenav();
      var header = document.querySelector('.main-header .logo-header');
      if (header && !header.querySelector('.gemo-mobile-toggler')) {
        restoreMainHeaderToggler();
      }
    }, 100);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
