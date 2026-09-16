<?php

session_start();
require_once __DIR__ . '/../models/SeguimientoZoocriadero.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

$rolesPermitidos = [1, 2]; // 1 = Administrador del Sistema, 2 = Coordinador Zoocriadero

if (!isset($_SESSION['usuario_id']) || !in_array((int) ($_SESSION['usuario_rol_id'] ?? 0), $rolesPermitidos, true)) {
    header('Location: ../login.php');
    exit;
}

$carpetaVista = (int) $_SESSION['usuario_rol_id'] === 2 ? 'coordinador_zoocriadero' : 'admin';

$accion = $_GET['accion'] ?? '';

if ($accion !== 'pdf') {
    header('Location: ../views/' . $carpetaVista . '/reportes.php');
    exit;
}

$hoy = date('Y-m-d');
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fechaFin = $_GET['fecha_fin'] ?? $hoy;

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
    http_response_code(400);
    die('El rango de fechas no es válido.');
}

if ($fechaInicio > $fechaFin) {
    [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
}

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

/**
 * Trunca una cadena a $longitud caracteres. Usa mb_substr si la extensión
 * mbstring está disponible; si no, hace un recorte simple (no crítico aquí
 * porque sólo se usa para no desbordar columnas angostas de la tabla).
 */
function pdfCorta(string $texto, int $longitud): string
{
    if (function_exists('mb_substr')) {
        return mb_substr($texto, 0, $longitud);
    }
    return substr($texto, 0, $longitud);
}

try {
    $modelo = new SeguimientoZoocriadero();
    $resumen = $modelo->obtenerResumenPorRango($fechaInicio, $fechaFin);
    $registros = $modelo->obtenerRegistrosPorRango($fechaInicio, $fechaFin);
} catch (Throwable $e) {
    http_response_code(500);
    die('No fue posible generar el reporte. Revise la configuración de PostgreSQL.');
}

$totalVivos = (int) $resumen['total_vivos'];
$totalMuertos = (int) $resumen['total_muertos'];
$totalGeneral = $totalVivos + $totalMuertos;
$tasaMortalidad = $totalGeneral > 0 ? round(($totalMuertos / $totalGeneral) * 100, 1) : 0.0;

class ReportePDF extends FPDF
{
    public string $subtitulo = '';

    public function Header(): void
    {
        $this->SetFillColor(20, 83, 45);
        $this->Rect(0, 0, 210, 24, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 6);
        $this->SetFont('Helvetica', 'B', 16);
        $this->Cell(0, 8, 'GEMO - Reporte del Zoocriadero', 0, 1);
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
}

$pdf = new ReportePDF();
$pdf->AliasNbPages();
$pdf->subtitulo = pdfTexto(
    'Periodo: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))
    . '  -  Generado: ' . date('d/m/Y H:i')
);
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(0, 9, pdfTexto('Resumen general'), 0, 1);
$pdf->filaResumen('Registros en el periodo', (string) $resumen['total_registros']);
$pdf->filaResumen('Peces vivos (alevines nacidos)', (string) $totalVivos);
$pdf->filaResumen('Peces muertos (macho)', (string) $resumen['total_muertos_macho']);
$pdf->filaResumen('Peces muertos (hembra)', (string) $resumen['total_muertos_hembra']);
$pdf->filaResumen('Total peces muertos', (string) $totalMuertos);
$pdf->filaResumen('Tasa de mortalidad', $tasaMortalidad . ' %');
$pdf->Ln(8);

$pdf->SetFont('Helvetica', 'B', 13);
$pdf->Cell(0, 9, pdfTexto('Detalle de seguimientos'), 0, 1);

$anchos = [20, 16, 26, 12, 14, 14, 14, 22, 42];
$encabezados = ['Fecha', 'Tanque', 'Tipo', 'pH', 'Temp', 'Cloro', 'Vivos', 'Muertos M/H', 'Registrado por'];

$pdf->SetFillColor(234, 245, 238);
$pdf->SetFont('Helvetica', 'B', 8);
foreach ($encabezados as $i => $h) {
    $pdf->Cell($anchos[$i], 7, pdfTexto($h), 1, 0, 'C', true);
}
$pdf->Ln();

$pdf->SetFont('Helvetica', '', 8);
if (empty($registros)) {
    $pdf->Cell(array_sum($anchos), 8, pdfTexto('No hay registros en el periodo seleccionado.'), 1, 1, 'C');
}

foreach ($registros as $r) {
    if ($pdf->GetY() > 270) {
        $pdf->AddPage();
        $pdf->SetFillColor(234, 245, 238);
        $pdf->SetFont('Helvetica', 'B', 8);
        foreach ($encabezados as $i => $h) {
            $pdf->Cell($anchos[$i], 7, pdfTexto($h), 1, 0, 'C', true);
        }
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 8);
    }

    $pdf->Cell($anchos[0], 6, date('d/m/Y', strtotime($r['fecha'])), 1);
    $pdf->Cell($anchos[1], 6, '#' . (int) $r['id_tanque'], 1, 0, 'C');
    $pdf->Cell($anchos[2], 6, pdfTexto(pdfCorta((string) $r['tipo_tanque'], 16)), 1);
    $pdf->Cell($anchos[3], 6, (string) $r['ph'], 1, 0, 'C');
    $pdf->Cell($anchos[4], 6, (string) $r['temperatura'], 1, 0, 'C');
    $pdf->Cell($anchos[5], 6, (string) $r['cloro'], 1, 0, 'C');
    $pdf->Cell($anchos[6], 6, (string) $r['alevines_nacidos'], 1, 0, 'C');
    $pdf->Cell($anchos[7], 6, $r['muertos_macho'] . ' / ' . $r['muertos_hembra'], 1, 0, 'C');
    $pdf->Cell($anchos[8], 6, pdfTexto(pdfCorta((string) $r['registrado_por'], 26)), 1);
    $pdf->Ln();
}

$nombreArchivo = 'reporte_gemo_' . $fechaInicio . '_a_' . $fechaFin . '.pdf';
$pdf->Output('D', $nombreArchivo);
exit;
