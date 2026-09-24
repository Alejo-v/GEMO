<?php
$guiaArchivoPdf = $guiaArchivoPdf ?? '';
$guiaNombreDescarga = $guiaNombreDescarga ?? 'Manual_de_usuario.pdf';
$guiaRolNombre = $_SESSION['usuario_rol'] ?? '';
$guiaFechaActualizacion = '23 sep 2026';
?>
<div class="gemo-about-badge">Guía de usuario</div>

<div class="card card-round gemo-guia-card">
    <div class="card-body">

        <h4 class="gemo-guia-titulo">Guía completa:
            <span class="gemo-guia-subtitulo">Funciones y herramientas del sistema</span>
        </h4>

        <p class="gemo-guia-intro">
            Este documento centraliza todo lo que necesitas saber para operar el sistema con tu perfil asignado.
            Aquí encontrarás el recorrido completo por tus módulos de trabajo, los procedimientos cotidianos,
            las mejores prácticas y los flujos de tareas diseñados para optimizar tus actividades diarias en
            la plataforma de forma ágil y segura.
        </p>

        <div class="gemo-guia-doc-wrap" style="width:100%;max-width:100%;">
            <iframe src="<?= htmlspecialchars($guiaArchivoPdf) ?>#toolbar=1"
                    title="<?= htmlspecialchars($guiaNombreDescarga) ?>"
                    style="width:100%;height:100%;min-height:780px;border:0;display:block;"></iframe>
        </div>

        <div class="gemo-guia-meta">
            <div class="gemo-guia-meta-item">
                <i class="fas fa-user-tag" aria-hidden="true"></i>
                <span>Dirigido a: <strong><?= htmlspecialchars($guiaRolNombre) ?></strong></span>
            </div>
            <div class="gemo-guia-meta-item">
                <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                <span>Última actualización: <strong><?= htmlspecialchars($guiaFechaActualizacion) ?></strong></span>
            </div>
            <a class="btn btn-gemo gemo-guia-descarga" href="<?= htmlspecialchars($guiaArchivoPdf) ?>" download="<?= htmlspecialchars($guiaNombreDescarga) ?>">
                <i class="fas fa-download" aria-hidden="true"></i> Descargar
            </a>
        </div>

    </div>
</div>
