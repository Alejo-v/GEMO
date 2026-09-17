







(function () {
    'use strict';

    var CLAVE_FS = 'gemo_a11y_fontsize';
    var CLAVE_CONTRASTE = 'gemo_a11y_contraste';
    var CLAVE_SUBRAYADO = 'gemo_a11y_subrayado';

    var html = document.documentElement;

    function aplicarTamano(tamano) {
        html.classList.remove('gemo-a11y-fs-lg', 'gemo-a11y-fs-xl');
        if (tamano === 'lg') html.classList.add('gemo-a11y-fs-lg');
        if (tamano === 'xl') html.classList.add('gemo-a11y-fs-xl');
    }

    function aplicarContraste(activo) {
        html.classList.toggle('gemo-a11y-contraste', activo);
    }

    function aplicarSubrayado(activo) {
        html.classList.toggle('gemo-a11y-subrayado', activo);
    }

    
    var tamanoGuardado = localStorage.getItem(CLAVE_FS) || 'md';
    var contrasteGuardado = localStorage.getItem(CLAVE_CONTRASTE) === '1';
    var subrayadoGuardado = localStorage.getItem(CLAVE_SUBRAYADO) === '1';
    aplicarTamano(tamanoGuardado);
    aplicarContraste(contrasteGuardado);
    aplicarSubrayado(subrayadoGuardado);

    document.addEventListener('DOMContentLoaded', function () {
        var toggle = document.getElementById('gemoA11yToggle');
        var panel = document.getElementById('gemoA11yPanel');
        if (!toggle || !panel) return;

        var botonesTamano = panel.querySelectorAll('[data-a11y-fs]');
        var checkContraste = document.getElementById('gemoA11yContraste');
        var checkSubrayado = document.getElementById('gemoA11ySubrayado');
        var botonLeer = document.getElementById('gemoA11yLeer');
        var botonReset = document.getElementById('gemoA11yReset');

        function marcarBotonActivo() {
            botonesTamano.forEach(function (b) {
                b.classList.toggle('active', b.dataset.a11yFs === (localStorage.getItem(CLAVE_FS) || 'md'));
            });
        }
        marcarBotonActivo();
        if (checkContraste) checkContraste.checked = contrasteGuardado;
        if (checkSubrayado) checkSubrayado.checked = subrayadoGuardado;

        toggle.addEventListener('click', function () {
            var abierto = !panel.hasAttribute('hidden');
            if (abierto) {
                panel.setAttribute('hidden', '');
                toggle.setAttribute('aria-expanded', 'false');
            } else {
                panel.removeAttribute('hidden');
                toggle.setAttribute('aria-expanded', 'true');
            }
        });

        document.addEventListener('click', function (e) {
            if (!panel.contains(e.target) && e.target !== toggle && !toggle.contains(e.target)) {
                panel.setAttribute('hidden', '');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                panel.setAttribute('hidden', '');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        botonesTamano.forEach(function (boton) {
            boton.addEventListener('click', function () {
                var tamano = boton.dataset.a11yFs;
                aplicarTamano(tamano);
                localStorage.setItem(CLAVE_FS, tamano);
                marcarBotonActivo();
            });
        });

        if (checkContraste) {
            checkContraste.addEventListener('change', function () {
                aplicarContraste(checkContraste.checked);
                localStorage.setItem(CLAVE_CONTRASTE, checkContraste.checked ? '1' : '0');
            });
        }

        if (checkSubrayado) {
            checkSubrayado.addEventListener('change', function () {
                aplicarSubrayado(checkSubrayado.checked);
                localStorage.setItem(CLAVE_SUBRAYADO, checkSubrayado.checked ? '1' : '0');
            });
        }

        if (botonReset) {
            botonReset.addEventListener('click', function () {
                localStorage.removeItem(CLAVE_FS);
                localStorage.removeItem(CLAVE_CONTRASTE);
                localStorage.removeItem(CLAVE_SUBRAYADO);
                aplicarTamano('md');
                aplicarContraste(false);
                aplicarSubrayado(false);
                marcarBotonActivo();
                if (checkContraste) checkContraste.checked = false;
                if (checkSubrayado) checkSubrayado.checked = false;
            });
        }

        
        if (botonLeer && 'speechSynthesis' in window) {
            var leyendo = false;
            var textoOriginalBoton = botonLeer.innerHTML;

            function detener() {
                window.speechSynthesis.cancel();
                leyendo = false;
                botonLeer.innerHTML = textoOriginalBoton;
            }

            botonLeer.addEventListener('click', function () {
                if (leyendo) {
                    detener();
                    return;
                }
                var contenedor = document.querySelector('.page-inner') || document.querySelector('.login-card') || document.body;
                var texto = (contenedor.innerText || contenedor.textContent || '').trim();
                if (!texto) return;

                var utterance = new SpeechSynthesisUtterance(texto);
                utterance.lang = 'es-CO';
                utterance.rate = 0.95;
                utterance.onend = detener;
                utterance.onerror = detener;

                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
                leyendo = true;
                botonLeer.innerHTML = '<i class="fas fa-stop me-1"></i> Detener lectura';
            });
        } else if (botonLeer) {
            botonLeer.disabled = true;
            botonLeer.title = 'Tu navegador no permite leer la página en voz alta';
        }
    });
})();
