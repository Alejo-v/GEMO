<?php

session_start();
require_once __DIR__ . '/../models/ReporteTerreno.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

$rolesPermitidos = [1, 5]; // 1 = Administrador del Sistema, 5 = Coordinador Terreno

if (!isset($_SESSION['usuario_id']) || !in_array((int) ($_SESSION['usuario_rol_id'] ?? 0), $rolesPermitidos, true)) {
    header('Location: ../login.php');
    exit;
}

$carpetaVista = (int) $_SESSION['usuario_rol_id'] === 5 ? 'coordinador_terreno' : 'admin';

$accion = $_GET['accion'] ?? '';

if ($accion !== 'pdf') {
    header('Location: ../views/' . $carpetaVista . '/reportes.php');
    exit;
}

$filtros = [
    'id_comuna' => $_GET['id_comuna'] ?? '',
    'id_barrio' => $_GET['id_barrio'] ?? '',
    'id_tipo_deposito' => $_GET['id_tipo_deposito'] ?? '',
    'fecha_desde' => $_GET['fecha_desde'] ?? '',
    'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
];

/**
 * FPDF (fuentes core tipo Helvetica) sólo soporta ISO-8859-1, por lo que el
 * texto en español con tildes/ñ debe transliterarse antes de imprimirse.
 */
function pdfTexto(?string $texto): string
{
    $texto = (string) $texto;
    $convertido = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    if ($convertido !== false) {
        return $convertido;
    }
    return @utf8_decode($texto);
}

function pdfCorta(string $texto, int $longitud): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($texto, 0, $longitud);
    }
    return substr($texto, 0, $longitud);
}

try {
    $modelo = new ReporteTerreno();
    $reporteSitios = $modelo->reporteSitios($filtros);
    $reporteActividad = $modelo->reportePorActividad($filtros);
    $reporteAuxiliar = $modelo->reportePorAuxiliar($filtros);
    $reporteTipoDeposito = $modelo->reportePorTipoDeposito($filtros);
} catch (Throwable $e) {
    http_response_code(500);
    die('No fue posible generar el reporte. Revise la configuración de PostgreSQL.');
}

$totalAedes = array_sum(array_column($reporteSitios, 'larvas_aedes'));
$totalPupas = array_sum(array_column($reporteSitios, 'pupas'));
$totalCulex = array_sum(array_column($reporteSitios, 'larvas_culex'));

class ReporteTerrenoPDF extends FPDF
{
    public string $subtitulo = '';

    public function Header(): void
    {
        $this->SetFillColor(20, 83, 45);
        $this->Rect(0, 0, 210, 24, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 6);
        $this->SetFont('Helvetica', 'B', 16);
        $this->Cell(0, 8, 'GEMO - Reporte de Terreno', 0, 1);
        $this->SetX(10);
        $this->SetFont('Helvetica', '', 10);
        $this->Cell(0, 6, $this->subtitulo, 0, 1);
        $this->SetTextColor(0, 0, 0);
        $this->SetY(30);
    }

    public function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 10, pdfTexto('Pagina ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
    }

    public function filaResumen(string $etiqueta, string $valor): void
    {
        $this->SetFont('Helvetica', '', 10);
        $this->Cell(110, 8, pdfTexto($etiqueta), 1);
        $this->SetFont('Helvetica', 'B', 10);
        $this->Cell(40, 8, $valor, 1, 1, 'C');
    }

    public function tituloSeccion(string $texto): void
    {
        $this->SetFont('Helvetica', 'B', 13);
        $this->Cell(0, 9, pdfTexto($texto), 0, 1);
    }
}

$partesFiltro = [];
if (!empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta'])) {
    $partesFiltro[] = 'Fechas: ' . ($filtros['fecha_desde'] ?: '...') . ' al ' . ($filtros['fecha_hasta'] ?: '...');
}
if (!empty($filtros['id_comuna'])) {
    $partesFiltro[] = 'Comuna filtrada';
}
if (!empty($filtros['id_barrio'])) {
    $partesFiltro[] = 'Barrio filtrado';
}
if (!empty($filtros['id_tipo_deposito'])) {
    $partesFiltro[] = 'Tipo de deposito filtrado';
}
$resumenFiltros = $partesFiltro ? implode('  -  ', $partesFiltro) : 'Sin filtros aplicados';

$pdf = new ReporteTerrenoPDF();
$pdf->AliasNbPages();
$pdf->subtitulo = pdfTexto($resumenFiltros . '  -  Generado: ' . date('d/m/Y H:i'));
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$pdf->tituloSeccion('Resumen general');
$pdf->filaResumen('Total de registros', (string) count($reporteSitios));
$pdf->filaResumen('Larvas Aedes encontradas', (string) $totalAedes);
$pdf->filaResumen('Pupas encontradas', (string) $totalPupas);
$pdf->filaResumen('Larvas Culex encontradas', (string) $totalCulex);
$pdf->Ln(8);

// ---- Reporte 2: por tipo de actividad ----
$pdf->tituloSeccion('Reporte 2 - Registros por tipo de actividad');
$pdf->SetFillColor(234, 245, 238);
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(140, 7, pdfTexto('Actividad'), 1, 0, 'C', true);
$pdf->Cell(50, 7, pdfTexto('Total registros'), 1, 1, 'C', true);
$pdf->SetFont('Helvetica', '', 9);
if (empty($reporteActividad)) {
    $pdf->Cell(190, 8, pdfTexto('No hay datos con estos filtros.'), 1, 1, 'C');
}
foreach ($reporteActividad as $a) {
    $pdf->Cell(140, 7, pdfTexto($a['etiqueta']), 1);
    $pdf->Cell(50, 7, (string) $a['total'], 1, 1, 'C');
}
$pdf->Ln(6);

// ---- Reporte 4: por tipo de depósito ----
if ($pdf->GetY() > 240) {
    $pdf->AddPage();
}
$pdf->tituloSeccion('Reporte 4 - Registros por tipo de deposito');
$pdf->SetFillColor(234, 245, 238);
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(140, 7, pdfTexto('Tipo de deposito'), 1, 0, 'C', true);
$pdf->Cell(50, 7, pdfTexto('Total registros'), 1, 1, 'C', true);
$pdf->SetFont('Helvetica', '', 9);
if (empty($reporteTipoDeposito)) {
    $pdf->Cell(190, 8, pdfTexto('No hay datos con estos filtros.'), 1, 1, 'C');
}
foreach ($reporteTipoDeposito as $t) {
    $pdf->Cell(140, 7, pdfTexto($t['etiqueta']), 1);
    $pdf->Cell(50, 7, (string) $t['total'], 1, 1, 'C');
}
$pdf->Ln(6);

// ---- Reporte 3: por auxiliar ----
if ($pdf->GetY() > 220) {
    $pdf->AddPage();
}
$pdf->tituloSeccion('Reporte 3 - Actividades por auxiliar');
$pdf->SetFillColor(234, 245, 238);
$pdf->SetFont('Helvetica', 'B', 9);
$pdf->Cell(60, 7, pdfTexto('Auxiliar'), 1, 0, 'C', true);
$pdf->Cell(25, 7, pdfTexto('Total'), 1, 0, 'C', true);
$pdf->Cell(105, 7, pdfTexto('Detalle por actividad'), 1, 1, 'C', true);
$pdf->SetFont('Helvetica', '', 8);
if (empty($reporteAuxiliar)) {
    $pdf->Cell(190, 8, pdfTexto('No hay datos con estos filtros.'), 1, 1, 'C');
}
foreach ($reporteAuxiliar as $aux) {
    if ($pdf->GetY() > 270) {
        $pdf->AddPage();
    }
    $pdf->Cell(60, 7, pdfTexto(pdfCorta($aux['auxiliar'], 34)), 1);
    $pdf->Cell(25, 7, (string) $aux['total'], 1, 0, 'C');
    $pdf->Cell(105, 7, pdfTexto(pdfCorta(implode(' | ', $aux['actividades']), 60)), 1, 1);
}
$pdf->Ln(6);

// ---- Reporte 1: detalle de sitios ----
$pdf->AddPage();
$pdf->tituloSeccion('Reporte 1 - Detalle de sitios');

$anchos = [16, 30, 20, 18, 22, 22, 12, 12, 12, 26];
$encabezados = ['Fecha', 'Sitio', 'Barrio', 'Comuna', 'Tipo', 'Actividad', 'Aedes', 'Pupas', 'Culex', 'Auxiliar'];

$pdf->SetFillColor(234, 245, 238);
$pdf->SetFont('Helvetica', 'B', 7);
foreach ($encabezados as $i => $h) {
    $pdf->Cell($anchos[$i], 7, pdfTexto($h), 1, 0, 'C', true);
}
$pdf->Ln();

$pdf->SetFont('Helvetica', '', 7);
if (empty($reporteSitios)) {
    $pdf->Cell(array_sum($anchos), 8, pdfTexto('No hay registros con estos filtros.'), 1, 1, 'C');
}

foreach ($reporteSitios as $r) {
    if ($pdf->GetY() > 270) {
        $pdf->AddPage();
        $pdf->SetFillColor(234, 245, 238);
        $pdf->SetFont('Helvetica', 'B', 7);
        foreach ($encabezados as $i => $h) {
            $pdf->Cell($anchos[$i], 7, pdfTexto($h), 1, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 7);
    }

    $pdf->Cell($anchos[0], 6, date('d/m/Y', strtotime($r['fecha'])), 1);
    $pdf->Cell($anchos[1], 6, pdfTexto(pdfCorta((string) $r['direccion'], 18)), 1);
    $pdf->Cell($anchos[2], 6, pdfTexto(pdfCorta((string) ($r['barrios'] ?? '-'), 12)), 1);
    $pdf->Cell($anchos[3], 6, pdfTexto(pdfCorta((string) ($r['comunas'] ?? '-'), 10)), 1);
    $pdf->Cell($anchos[4], 6, pdfTexto(pdfCorta((string) $r['tipo_deposito'], 14)), 1);
    $pdf->Cell($anchos[5], 6, pdfTexto(pdfCorta((string) $r['actividad'], 14)), 1);
    $pdf->Cell($anchos[6], 6, (string) $r['larvas_aedes'], 1, 0, 'C');
    $pdf->Cell($anchos[7], 6, (string) $r['pupas'], 1, 0, 'C');
    $pdf->Cell($anchos[8], 6, (string) $r['larvas_culex'], 1, 0, 'C');
    $pdf->Cell($anchos[9], 6, pdfTexto(pdfCorta((string) $r['auxiliar'], 16)), 1);
    $pdf->Ln();
}

$nombreArchivo = 'reporte_terreno_gemo_' . date('Y-m-d') . '.pdf';
$pdf->Output('D', $nombreArchivo);
exit;
