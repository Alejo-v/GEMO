<?php
$manualVideoSrc    = $manualVideoSrc ?? '';
$manualVideoTitulo = $manualVideoTitulo ?? 'Vídeo manual';
$manualRolNombre   = $_SESSION['usuario_rol'] ?? '';
$manualFechaActualizacion = '20 sep 2026';
?>
<div class="gemo-about-badge">Manual de usuario</div>

<div class="card card-round gemo-manual-card">
    <div class="card-body">

        <h4 class="gemo-manual-titulo">Vídeo manual:
            <span class="gemo-manual-subtitulo">Primeros pasos y navegación general del sistema</span>
        </h4>

        <p class="gemo-manual-intro">
            Descubre cómo funciona el sistema de forma rápida y sencilla. En este video te mostramos
            un recorrido general por la interfaz, las herramientas principales y los accesos directos
            disponibles para facilitar tu día a día en la plataforma, sin importar tu rol o nivel de experiencia.
        </p>

        <div class="gemo-manual-video-wrap">
            <iframe id="gemoManualIframe"
                    src="<?= htmlspecialchars($manualVideoSrc) ?>"
                    frameborder="0"
                    allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
                    referrerpolicy="strict-origin-when-cross-origin"
                    title="<?= htmlspecialchars($manualVideoTitulo) ?>"></iframe>
        </div>
        <script src="https://player.vimeo.com/api/player.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var iframe = document.getElementById('gemoManualIframe');
                var destino = document.getElementById('gemoManualDuracion');
                if (!iframe || !destino || typeof Vimeo === 'undefined') { return; }
                try {
                    var player = new Vimeo.Player(iframe);
                    player.getDuration().then(function (segundos) {
                        var min = Math.floor(segundos / 60);
                        var seg = Math.floor(segundos % 60);
                        destino.textContent = min + ':' + (seg < 10 ? '0' : '') + seg + ' min';
                    }).catch(function () {
                        destino.textContent = 'No disponible';
                    });
                } catch (e) {
                    destino.textContent = 'No disponible';
                }
            });
        </script>

        <div class="gemo-manual-meta">
            <div class="gemo-manual-meta-item">
                <i class="fas fa-clock" aria-hidden="true"></i>
                <span>Duración: <strong id="gemoManualDuracion">Cargando…</strong></span>
            </div>
            <div class="gemo-manual-meta-item">
                <i class="fas fa-user-tag" aria-hidden="true"></i>
                <span>Dirigido a: <strong><?= htmlspecialchars($manualRolNombre) ?></strong></span>
            </div>
            <div class="gemo-manual-meta-item">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <span>Última actualización: <strong><?= htmlspecialchars($manualFechaActualizacion) ?></strong></span>
            </div>
        </div>

    </div>
</div>
