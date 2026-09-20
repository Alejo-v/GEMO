<?php
/**
 * Contenido de la página "Acerca de GEMO".
 * Es compartido por los 6 roles: cada vista solo incluye su header, este archivo y su footer.
 * Para cambiar nombres o agregar personas, edite únicamente los arreglos de abajo.
 */
$acercaDeEquipo = [
    'Maria Elena Guzman Perez',
    'Luna Alejandra Valencia Cossio',
    'Nicolas Bonilla Grueso',
    'Kevin Santiago Morales Cardenas',
    'Jhon Alejandro Vanegas Morcillo',
];

$acercaDeAgradecimientos = [
    ['nombre' => 'Paula Andrea Martínez Espinosa', 'rol' => 'Instructora'],
    ['nombre' => 'Nicolas Riascos',                'rol' => 'Instructor'],
    ['nombre' => 'José Fredy Caicedo',             'rol' => 'Instructor'],
    ['nombre' => 'Andrés Sanchéz',                 'rol' => 'Instructor'],
];
?>
<div class="gemo-about-badge">Información general</div>

<div class="gemo-about-timeline">

    <div class="gemo-about-item">
        <span class="gemo-about-icon" aria-hidden="true"><i class="fas fa-code"></i></span>
        <div class="card card-round">
            <div class="card-header"><h4 class="card-title">Análisis, Diseño y Desarrollo de Software</h4></div>
            <div class="card-body">
                <ul class="gemo-about-list">
                    <?php foreach ($acercaDeEquipo as $persona): ?>
                        <li><?= htmlspecialchars($persona) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="gemo-about-item">
        <span class="gemo-about-icon" aria-hidden="true"><i class="fas fa-users"></i></span>
        <div class="card card-round">
            <div class="card-header"><h4 class="card-title">Agradecimientos</h4></div>
            <div class="card-body">
                <ul class="gemo-about-list">
                    <?php foreach ($acercaDeAgradecimientos as $persona): ?>
                        <li><?= htmlspecialchars($persona['nombre']) ?> <span class="gemo-about-role">(<?= htmlspecialchars($persona['rol']) ?>)</span></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>

</div>
