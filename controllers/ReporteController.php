<?php

session_start();
require_once __DIR__ . '/../models/SeguimientoZoocriadero.php';
require_once __DIR__ . '/../models/Tanque.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

$rolesPermitidos = [1, 2]; 

if (!isset($_SESSION['usuario_id']) || !in_array((int) ($_SESSION['usuario_rol_id'] ?? 0), $rolesPermitidos, true)) {
    header('Location: ../login.php');
    exit;
}

$esAdmin = (int) $_SESSION['usuario_rol_id'] === 1;


$paginaRetorno = $esAdmin
    ? '../views/admin/reportes_zoocriadero.php'
    : '../views/coordinador_zoocriadero/reportes.php';

$accion = $_GET['accion'] ?? '';

if ($accion !== 'pdf') {
    header('Location: ' . $paginaRetorno);
    exit;
}








$reporte = (string) ($_GET['reporte'] ?? 'todos');
if (!in_array($reporte, ['1', '2', '3', 'todos'], true)) {
    $reporte = 'todos';
}

$hoy = date('Y-m-d');
$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$fechaFin = $_GET['fecha_fin'] ?? $hoy;

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
    http_response_code(400);
    die('El rango de fechas no es válido.');
}

if ($fechaInicio > $hoy || $fechaFin > $hoy || $fechaInicio > $fechaFin) {
    http_response_code(400);
    die('No hay reportes en ese rango de fechas.');
}

$filtros = [
    'fecha_inicio' => $fechaInicio,
    'fecha_fin' => $fechaFin,
    'id_zoocriadero' => $_GET['id_zoocriadero'] ?? '',
    'id_actividad' => $_GET['id_actividad'] ?? '',
];





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

$imprimeReporte1 = ($reporte === '1' || $reporte === 'todos');
$imprimeReporte2 = ($reporte === '2' || $reporte === 'todos');
$imprimeReporte3 = ($reporte === '3' || $reporte === 'todos');

try {
    $modelo = new SeguimientoZoocriadero();
    $modeloTanque = new Tanque();

    
    $resumen = $modelo->obtenerResumenFiltrado($filtros);
    $registros = $imprimeReporte1 ? $modelo->obtenerRegistrosFiltrados($filtros) : [];
    $resumenPorTanque = $imprimeReporte2 ? $modelo->obtenerResumenPorTanque($filtros) : [];
    $reporteTanquesZoo = $imprimeReporte3
        ? $modeloTanque->obtenerReportePorZoocriadero($filtros['id_zoocriadero'])
        : [];

    $zoocriaderos = $modelo->obtenerZoocriaderos();
    $actividadesCatalogo = $modelo->obtenerActividades();
} catch (Throwable $e) {
    http_response_code(500);
    die('No fue posible generar el reporte. Revise la configuración de PostgreSQL.');
}

$totalVivos = (int) $resumen['total_vivos'];
$totalMuertos = (int) $resumen['total_muertos'];
$totalGeneral = $totalVivos + $totalMuertos;
$tasaMortalidad = $totalGeneral > 0 ? round(($totalMuertos / $totalGeneral) * 100, 1) : 0.0;


$nombreZoocriadero = '';
if (!empty($filtros['id_zoocriadero'])) {
    foreach ($zoocriaderos as $z) {
        if ((int) $z['id_zoocriadero'] === (int) $filtros['id_zoocriadero']) {
            $nombreZoocriadero = $z['direccion'];
            break;
        }
    }
}
$nombreActividad = '';
if (!empty($filtros['id_actividad'])) {
    foreach ($actividadesCatalogo as $a) {
        if ((int) $a['id_actividad_zoocriadero'] === (int) $filtros['id_actividad']) {
            $nombreActividad = $a['nombre'];
            break;
        }
    }
}


$titulosReporte = [
    '1' => 'Reporte 1 - Seguimiento de actividades',
    '2' => 'Reporte 2 - Nacidos y muertos por tanque',
    '3' => 'Reporte 3 - Tanques por zoocriadero',
    'todos' => 'Reportes del Zoocriadero',
];
$archivosReporte = [
    '1' => 'zoocriadero_r1_seguimiento_actividades',
    '2' => 'zoocriadero_r2_nacidos_muertos_por_tanque',
    '3' => 'zoocriadero_r3_tanques_por_zoocriadero',
    'todos' => 'zoocriadero_reportes_completos',
];

class ReportePDF extends FPDF
{
    public string $subtitulo = '';
    public string $titulo = 'GEMO - Reporte del Zoocriadero';

    public function Header(): void
    {
        $this->SetFillColor(21, 61, 107);
        $this->Rect(0, 0, 210, 24, 'F');
        $logoAlcaldia = __DIR__ . '/../assets/img/branding/alcaldia-cali.jpg';
        if (is_readable($logoAlcaldia)) {
            $this->Image($logoAlcaldia, 188, 4, 16, 16, 'JPG');
        }
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 6);
        $this->SetFont('Helvetica', 'B', 15);
        $this->Cell(0, 8, pdfTexto($this->titulo), 0, 1);
        $this->SetX(10);
        $this->SetFont('Helvetica', '', 9);
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
$partesFiltro[] = 'Fechas: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin));
if ($nombreZoocriadero !== '') {
    $partesFiltro[] = 'Zoocriadero: ' . $nombreZoocriadero;
}
if ($nombreActividad !== '') {
    $partesFiltro[] = 'Actividad: ' . $nombreActividad;
}
$resumenFiltros = implode('  -  ', $partesFiltro);

$pdf = new ReportePDF();
$pdf->AliasNbPages();
$pdf->titulo = 'GEMO - ' . ($reporte === 'todos' ? 'Reportes del Zoocriadero' : $titulosReporte[$reporte]);
$pdf->subtitulo = pdfTexto($resumenFiltros . '  -  Generado: ' . date('d/m/Y H:i'));
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();



if ($imprimeReporte1 || $imprimeReporte2) {
    $pdf->tituloSeccion('Resumen general');
    $pdf->filaResumen('Registros en el periodo', (string) $resumen['total_registros']);
    $pdf->filaResumen('Peces vivos (alevines nacidos)', (string) $totalVivos);
    $pdf->filaResumen('Peces muertos (macho)', (string) $resumen['total_muertos_macho']);
    $pdf->filaResumen('Peces muertos (hembra)', (string) $resumen['total_muertos_hembra']);
    $pdf->filaResumen('Total peces muertos', (string) $totalMuertos);
    $pdf->filaResumen('Tasa de mortalidad', $tasaMortalidad . ' %');
    $pdf->Ln(8);
}


if ($imprimeReporte1) {
    $pdf->tituloSeccion('Reporte 1 - Seguimiento de actividades');

    $anchos = [18, 22, 14, 12, 14, 20, 30, 28, 32];
    $encabezados = ['Fecha', 'Zoocriadero', 'Tanque', 'Vivos', 'Muertos', 'Tipo tanque', 'Actividades', 'Registrado por', 'Observaciones'];

    $pdf->SetFillColor(230, 236, 242);
    $pdf->SetFont('Helvetica', 'B', 7);
    foreach ($encabezados as $i => $h) {
        $pdf->Cell($anchos[$i], 7, pdfTexto($h), 1, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 7);
    if (empty($registros)) {
        $pdf->Cell(array_sum($anchos), 8, pdfTexto('No hay registros con estos filtros.'), 1, 1, 'C');
    }

    foreach ($registros as $r) {
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
            $pdf->SetFillColor(230, 236, 242);
            $pdf->SetFont('Helvetica', 'B', 7);
            foreach ($encabezados as $i => $h) {
                $pdf->Cell($anchos[$i], 7, pdfTexto($h), 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetFont('Helvetica', '', 7);
        }

        $pdf->Cell($anchos[0], 6, date('d/m/Y', strtotime($r['fecha'])), 1);
        $pdf->Cell($anchos[1], 6, pdfTexto(pdfCorta((string) $r['zoocriadero'], 16)), 1);
        $pdf->Cell($anchos[2], 6, '#' . (int) $r['id_tanque'], 1, 0, 'C');
        $pdf->Cell($anchos[3], 6, (string) $r['alevines_nacidos'], 1, 0, 'C');
        $pdf->Cell($anchos[4], 6, (string) $r['muertos_total'], 1, 0, 'C');
        $pdf->Cell($anchos[5], 6, pdfTexto(pdfCorta((string) $r['tipo_tanque'], 14)), 1);
        $pdf->Cell($anchos[6], 6, pdfTexto(pdfCorta((string) $r['actividades'], 20)), 1);
        $pdf->Cell($anchos[7], 6, pdfTexto(pdfCorta((string) $r['registrado_por'], 20)), 1);
        $pdf->Cell($anchos[8], 6, pdfTexto(pdfCorta((string) ($r['observaciones'] ?? ''), 24)), 1);
        $pdf->Ln();
    }
    $pdf->Ln(6);
}


if ($imprimeReporte2) {
    if ($reporte === 'todos' && $pdf->GetY() > 220) {
        $pdf->AddPage();
    }
    $pdf->tituloSeccion('Reporte 2 - Nacidos y muertos por tanque');
    $anchos2 = [18, 40, 34, 22, 24, 26, 26];
    $encabezados2 = ['Tanque', 'Zoocriadero', 'Tipo tanque', 'Registros', 'Vivos', 'Muertos M/H', 'Total muertos'];

    $pdf->SetFillColor(230, 236, 242);
    $pdf->SetFont('Helvetica', 'B', 8);
    foreach ($encabezados2 as $i => $h) {
        $pdf->Cell($anchos2[$i], 7, pdfTexto($h), 1, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 8);
    if (empty($resumenPorTanque)) {
        $pdf->Cell(array_sum($anchos2), 8, pdfTexto('No hay datos con estos filtros.'), 1, 1, 'C');
    }
    foreach ($resumenPorTanque as $t) {
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
            $pdf->SetFillColor(230, 236, 242);
            $pdf->SetFont('Helvetica', 'B', 8);
            foreach ($encabezados2 as $i => $h) {
                $pdf->Cell($anchos2[$i], 7, pdfTexto($h), 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetFont('Helvetica', '', 8);
        }
        $pdf->Cell($anchos2[0], 7, '#' . (int) $t['id_tanque'], 1, 0, 'C');
        $pdf->Cell($anchos2[1], 7, pdfTexto(pdfCorta((string) $t['zoocriadero'], 24)), 1);
        $pdf->Cell($anchos2[2], 7, pdfTexto(pdfCorta((string) $t['tipo_tanque'], 20)), 1);
        $pdf->Cell($anchos2[3], 7, (string) $t['total_registros'], 1, 0, 'C');
        $pdf->Cell($anchos2[4], 7, (string) $t['total_vivos'], 1, 0, 'C');
        $pdf->Cell($anchos2[5], 7, $t['total_muertos_macho'] . ' / ' . $t['total_muertos_hembra'], 1, 0, 'C');
        $pdf->Cell($anchos2[6], 7, (string) $t['total_muertos'], 1, 0, 'C');
        $pdf->Ln();
    }
    $pdf->Ln(6);
}


if ($imprimeReporte3) {
    if ($reporte === 'todos') {
        $pdf->AddPage();
    }
    $pdf->tituloSeccion('Reporte 3 - Tanques por zoocriadero');
    $anchos3 = [44, 40, 20, 20, 66];
    $encabezados3 = ['Zoocriadero', 'Encargado', 'Total tanques', 'Activos', 'Detalle por tipo de tanque'];

    $pdf->SetFillColor(230, 236, 242);
    $pdf->SetFont('Helvetica', 'B', 8);
    foreach ($encabezados3 as $i => $h) {
        $pdf->Cell($anchos3[$i], 7, pdfTexto($h), 1, 0, 'C', true);
    }
    $pdf->Ln();

    $pdf->SetFont('Helvetica', '', 8);
    if (empty($reporteTanquesZoo)) {
        $pdf->Cell(array_sum($anchos3), 8, pdfTexto('No hay zoocriaderos registrados.'), 1, 1, 'C');
    }
    foreach ($reporteTanquesZoo as $zoo) {
        if ($pdf->GetY() > 270) {
            $pdf->AddPage();
            $pdf->SetFillColor(230, 236, 242);
            $pdf->SetFont('Helvetica', 'B', 8);
            foreach ($encabezados3 as $i => $h) {
                $pdf->Cell($anchos3[$i], 7, pdfTexto($h), 1, 0, 'C', true);
            }
            $pdf->Ln();
            $pdf->SetFont('Helvetica', '', 8);
        }
        $detalle = $zoo['detalle_tipos'] ? implode(' | ', $zoo['detalle_tipos']) : 'Sin tanques registrados';
        $pdf->Cell($anchos3[0], 7, pdfTexto(pdfCorta((string) $zoo['direccion'], 26)), 1);
        $pdf->Cell($anchos3[1], 7, pdfTexto(pdfCorta((string) $zoo['encargado'], 24)), 1);
        $pdf->Cell($anchos3[2], 7, (string) $zoo['total_tanques'], 1, 0, 'C');
        $pdf->Cell($anchos3[3], 7, (string) $zoo['tanques_activos'], 1, 0, 'C');
        $pdf->Cell($anchos3[4], 7, pdfTexto(pdfCorta($detalle, 42)), 1);
        $pdf->Ln();
    }
}

$nombreArchivo = $archivosReporte[$reporte] . '_' . $fechaInicio . '_a_' . $fechaFin . '.pdf';
$modoSalidaPdf = (isset($_GET['descargar']) && $_GET['descargar'] == '1') ? 'D' : 'I';
$pdf->Output($modoSalidaPdf, $nombreArchivo);
exit;
