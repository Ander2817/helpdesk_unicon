<?php
/**
 * includes/ReportePDF.php
 * Reporte de gestión en PDF (A4) con la identidad de Unicon HelpDesk.
 * Usa FPDF (libs/fpdf). FPDF trabaja en Windows-1252, por eso todo texto pasa por t().
 */
require_once __DIR__ . '/../libs/fpdf/fpdf.php';

class ReportePDF extends FPDF
{
    // Paleta del sistema
    const AZUL    = [26, 42, 74];
    const NARANJA = [232, 119, 34];
    const GRIS    = [100, 116, 139];
    const BORDE   = [226, 232, 240];
    const FONDO   = [248, 250, 252];
    const VERDE   = [22, 163, 74];
    const AMBAR   = [217, 119, 6];
    const ROJO    = [220, 38, 38];

    private string $generado_por;
    private string $generado_en;

    public function __construct(string $generado_por)
    {
        parent::__construct('P', 'mm', 'A4');
        $this->generado_por = $generado_por;
        $this->generado_en  = date('d/m/Y H:i');
        $this->SetMargins(15, 24, 15);
        $this->SetAutoPageBreak(true, 18);
        $this->AliasNbPages();
        $this->SetTitle('Reporte de gestión de tickets — Unicon HelpDesk', true);
        $this->SetAuthor('Unicon HelpDesk', true);
        $this->SetCreator('Unicon HelpDesk', true);
    }

    /** UTF-8 -> Windows-1252 sin depender de extensiones (iconv/mbstring pueden no estar). */
    public static function t(string $s): string
    {
        static $especiales = [0x20AC=>128, 0x201A=>130, 0x2026=>133, 0x2018=>145, 0x2019=>146,
                              0x201C=>147, 0x201D=>148, 0x2022=>149, 0x2013=>150, 0x2014=>151, 0x2122=>153];
        if ($s === '' || !preg_match('/[\x80-\xFF]/', $s)) return $s;
        $out = '';
        preg_match_all('/./su', $s, $m);
        foreach ($m[0] as $ch) {
            $n = strlen($ch);
            if ($n === 1) { $out .= $ch; continue; }
            $b = array_values(unpack('C*', $ch));
            $cp = ($n === 2) ? ((($b[0] & 0x1F) << 6) | ($b[1] & 0x3F))
                : (($n === 3) ? ((($b[0] & 0x0F) << 12) | (($b[1] & 0x3F) << 6) | ($b[2] & 0x3F)) : 0);
            if (isset($especiales[$cp])) $out .= chr($especiales[$cp]);
            elseif ($cp >= 160 && $cp <= 255) $out .= chr($cp);
            else $out .= '?';
        }
        return $out;
    }

    /** 20.7 -> "20,7" */
    private function num($v): string
    {
        return str_replace('.', ',', (string)$v);
    }

    private function color(array $c, string $tipo = 'text'): void
    {
        if ($tipo === 'fill') $this->SetFillColor($c[0], $c[1], $c[2]);
        elseif ($tipo === 'draw') $this->SetDrawColor($c[0], $c[1], $c[2]);
        else $this->SetTextColor($c[0], $c[1], $c[2]);
    }

    /** Cell con conversión de texto incluida. */
    private function celda($w, $h, string $txt, $borde = 0, $ln = 0, string $alinea = '', bool $relleno = false): void
    {
        $this->Cell($w, $h, self::t($txt), $borde, $ln, $alinea, $relleno);
    }

    /** Recorta el texto con «…» para que quepa en $ancho mm con la fuente actual. */
    private function recortar(string $txt, float $ancho): string
    {
        if ($this->GetStringWidth(self::t($txt)) <= $ancho) return $txt;
        while ($txt !== '' && $this->GetStringWidth(self::t($txt . '…')) > $ancho) {
            $txt = mb_substr_compat($txt, 0, -1);
        }
        return $txt . '…';
    }

    private function colorPorcentaje(?int $pct): array
    {
        if ($pct === null) return self::GRIS;
        return $pct >= 80 ? self::VERDE : ($pct >= 50 ? self::AMBAR : self::ROJO);
    }

    // ===================== Encabezado y pie de cada página =====================
    public function Header(): void
    {
        $this->color(self::AZUL, 'fill');
        $this->Rect(0, 0, 210, 15, 'F');
        $this->color(self::NARANJA, 'fill');
        $this->Rect(0, 15, 210, 1.2, 'F');

        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetXY(15, 4.5);
        $this->celda(100, 6, 'Industrias Unicon C.A.');
        $this->SetFont('Helvetica', '', 9);
        $this->SetXY(95, 4.5);
        $this->celda(100, 6, 'Unicon HelpDesk — Reporte de gestión', 0, 0, 'R');
        $this->SetY(24);
    }

    public function Footer(): void
    {
        $this->SetY(-12);
        $this->color(self::BORDE, 'draw');
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetFont('Helvetica', '', 8);
        $this->color(self::GRIS);
        $this->SetY(-10);
        $this->celda(130, 5, 'Generado el ' . $this->generado_en . ' por ' . $this->generado_por);
        $this->celda(50, 5, 'Página ' . $this->PageNo() . ' de {nb}', 0, 0, 'R');
    }

    // ===================== Bloques reutilizables =====================
    private function asegurarEspacio(float $alto): void
    {
        if ($this->GetY() + $alto > $this->PageBreakTrigger) $this->AddPage();
    }

    /** $espacio = alto total que necesita el título MÁS su contenido, para no dejar el título solo al final de una página. */
    private function seccion(string $titulo, string $subtitulo = '', float $espacio = 30): void
    {
        $this->asegurarEspacio($espacio);
        $this->Ln(3);
        $this->SetFont('Helvetica', 'B', 12);
        $this->color(self::AZUL);
        $this->celda(0, 7, $titulo, 0, 1);
        $y = $this->GetY();
        $this->color(self::NARANJA, 'fill');
        $this->Rect(15, $y, 14, 0.8, 'F');
        $this->Ln(2.5);
        if ($subtitulo !== '') {
            $this->SetFont('Helvetica', '', 8.5);
            $this->color(self::GRIS);
            $this->celda(0, 4.5, $subtitulo, 0, 1);
            $this->Ln(1.5);
        }
    }

    private function kpi(float $x, float $y, float $w, string $valor, string $etiqueta, array $color): void
    {
        $this->color(self::FONDO, 'fill');
        $this->color(self::BORDE, 'draw');
        $this->SetLineWidth(0.2);
        $this->Rect($x, $y, $w, 20, 'DF');
        $this->color($color, 'fill');
        $this->Rect($x, $y, 1.6, 20, 'F');

        $this->SetFont('Helvetica', 'B', 17);
        $this->color($color);
        $this->SetXY($x + 5, $y + 3);
        $this->celda($w - 7, 8, $valor);
        $this->SetFont('Helvetica', '', 8);
        $this->color(self::GRIS);
        $this->SetXY($x + 5, $y + 12);
        $this->celda($w - 7, 5, $etiqueta);
    }

    /** Fila de encabezado de tabla. $cols = [[titulo, ancho, alineación], ...] */
    private function encabezadoTabla(array $cols): void
    {
        $this->color(self::AZUL, 'fill');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 8.5);
        foreach ($cols as $c) $this->celda($c[1], 7, $c[0], 0, 0, $c[2], true);
        $this->Ln();
    }

    // ===================== Contenido del reporte =====================
    public function portada(array $datos, array $rango): void
    {
        $this->AddPage();
        $fmt = fn($f) => date('d/m/Y', strtotime($f));

        $this->SetFont('Helvetica', 'B', 20);
        $this->color(self::AZUL);
        $this->celda(0, 10, 'Reporte de gestión de tickets', 0, 1);
        $this->SetFont('Helvetica', '', 11);
        $this->color(self::GRIS);
        $this->celda(0, 6, 'Período: ' . $fmt($datos['desde']) . ' al ' . $fmt($datos['hasta']) . '  (' . $rango['dias'] . ' días)', 0, 1);
        if (!empty($rango['ajustado'])) {
            $this->SetFont('Helvetica', 'I', 8.5);
            $this->color(self::AMBAR);
            $this->celda(0, 5, 'El rango pedido superaba el máximo de ' . REPORTE_MAX_DIAS . ' días y se ajustó automáticamente.', 0, 1);
        }
        $this->Ln(5);

        // Fila 1 de indicadores
        $y = $this->GetY();
        $w = 43.5; $sep = 2;
        $sla = $datos['sla_pct'];
        $this->kpi(15,                 $y, $w, (string)$datos['creados'],   'Tickets creados',  self::AZUL);
        $this->kpi(15 + ($w + $sep),   $y, $w, (string)$datos['resueltos'], 'Tickets resueltos', self::VERDE);
        $this->kpi(15 + ($w + $sep)*2, $y, $w, $sla !== null ? $sla . '%' : '—', 'SLA cumplido', $this->colorPorcentaje($sla));
        $this->kpi(15 + ($w + $sep)*3, $y, $w, $datos['tiempo_prom_horas'] !== null ? $this->num($datos['tiempo_prom_horas']) . ' h' : '—', 'Tiempo prom. resolución', self::NARANJA);
        // Fila 2
        $y2 = $y + 23;
        $this->kpi(15,                 $y2, $w, (string)$datos['criticos_creados'], 'Críticos creados', self::ROJO);
        $this->kpi(15 + ($w + $sep),   $y2, $w, (string)$datos['activos_hoy'],      'Activos hoy', self::AMBAR);
        $this->kpi(15 + ($w + $sep)*2, $y2, $w, (string)$datos['sin_asignar_hoy'],  'Sin asignar hoy', $datos['sin_asignar_hoy'] > 0 ? self::ROJO : self::VERDE);
        $this->kpi(15 + ($w + $sep)*3, $y2, $w, (string)count($datos['tecnicos']),  'Técnicos activos', self::AZUL);
        $this->SetY($y2 + 24);
    }

    /** Barras verticales agrupadas: creados (azul) y resueltos (naranja) por mes. */
    public function volumenMensual(array $meses): void
    {
        $this->seccion('Volumen de tickets', 'Creados y resueltos por mes dentro del período.');
        $this->asegurarEspacio(75);

        $x0 = 28; $ancho = 165; $alto = 52;
        $y0 = $this->GetY() + 4;              // borde superior del gráfico
        $base = $y0 + $alto;                  // línea de base
        $max = 1;
        foreach ($meses as $m) $max = max($max, $m['creados'], $m['resueltos']);
        // escala "redonda" para que las líneas guía tengan números limpios
        $paso = max(1, (int)ceil($max / 4));
        $tope = $paso * 4;

        $this->SetFont('Helvetica', '', 7.5);
        for ($i = 0; $i <= 4; $i++) {
            $y = $base - ($alto * $i / 4);
            $this->color(self::BORDE, 'draw');
            $this->SetLineWidth(0.15);
            $this->Line($x0, $y, $x0 + $ancho, $y);
            $this->color(self::GRIS);
            $this->SetXY($x0 - 12, $y - 2);
            $this->celda(10, 4, (string)($paso * $i), 0, 0, 'R');
        }

        $n = max(1, count($meses));
        $celda = $ancho / $n;
        $bw = min(9, $celda / 3);
        foreach ($meses as $i => $m) {
            $cx = $x0 + $celda * $i + $celda / 2;
            foreach ([[$m['creados'], self::AZUL, -$bw], [$m['resueltos'], self::NARANJA, 0]] as [$v, $col, $off]) {
                $h = $alto * $v / $tope;
                $this->color($col, 'fill');
                if ($h > 0) $this->Rect($cx + $off, $base - $h, $bw, $h, 'F');
                if ($v > 0) {
                    $this->SetFont('Helvetica', 'B', 7);
                    $this->color($col);
                    $this->SetXY($cx + $off - 2, $base - $h - 4);
                    $this->celda($bw + 4, 3.5, (string)$v, 0, 0, 'C');
                }
            }
            $this->SetFont('Helvetica', '', 8);
            $this->color(self::GRIS);
            $this->SetXY($cx - $celda / 2, $base + 1.5);
            $this->celda($celda, 4, $m['etiqueta'], 0, 0, 'C');
        }

        // Leyenda
        $ly = $base + 8;
        $this->color(self::AZUL, 'fill');   $this->Rect($x0 + $ancho - 62, $ly, 3, 3, 'F');
        $this->SetXY($x0 + $ancho - 58, $ly - 0.8); $this->color(self::GRIS); $this->SetFont('Helvetica', '', 8);
        $this->celda(18, 4.5, 'Creados');
        $this->color(self::NARANJA, 'fill'); $this->Rect($x0 + $ancho - 36, $ly, 3, 3, 'F');
        $this->SetXY($x0 + $ancho - 32, $ly - 0.8);
        $this->celda(20, 4.5, 'Resueltos');
        $this->SetY($ly + 6);
    }

    public function tecnicos(array $tecnicos): void
    {
        $this->seccion('Rendimiento por técnico', 'Tickets resueltos en el período y carga de trabajo actual.', 29 + 7 * max(1, count($tecnicos)));
        $cols = [['Técnico', 66, 'L'], ['Resueltos', 26, 'C'], ['Tiempo prom.', 30, 'C'], ['SLA', 24, 'C'], ['Activos hoy', 34, 'C']];
        $this->asegurarEspacio(7 + 7 * max(1, count($tecnicos)));
        $this->encabezadoTabla($cols);
        if (!$tecnicos) {
            $this->SetFont('Helvetica', 'I', 9); $this->color(self::GRIS);
            $this->celda(180, 8, 'No hay técnicos activos.', 0, 1);
            return;
        }
        $this->SetFont('Helvetica', '', 9);
        foreach ($tecnicos as $i => $t) {
            $this->asegurarEspacio(7);
            $fondo = ($i % 2 === 0);
            $this->color(self::FONDO, 'fill');
            $this->color(self::AZUL);
            $this->SetFont('Helvetica', 'B', 9);
            $this->celda($cols[0][1], 7, $this->recortar($t['nombre'], 62), 0, 0, 'L', $fondo);
            $this->SetFont('Helvetica', '', 9);
            $this->color([30, 41, 59]);
            $this->celda($cols[1][1], 7, (string)$t['resueltos'], 0, 0, 'C', $fondo);
            $this->celda($cols[2][1], 7, $t['horas_prom'] !== null ? $this->num($t['horas_prom']) . ' h' : '—', 0, 0, 'C', $fondo);
            $this->color($this->colorPorcentaje($t['sla_pct']));
            $this->SetFont('Helvetica', 'B', 9);
            $this->celda($cols[3][1], 7, $t['sla_pct'] !== null ? $t['sla_pct'] . '%' : '—', 0, 0, 'C', $fondo);
            $this->SetFont('Helvetica', '', 9);
            $this->color([30, 41, 59]);
            $this->celda($cols[4][1], 7, (string)$t['activos'], 0, 1, 'C', $fondo);
        }
    }

    public function sla(array $filas): void
    {
        $this->seccion('Cumplimiento de SLA por prioridad', 'Porcentaje de tickets resueltos dentro del tiempo objetivo de su prioridad.', 29 + 8 * max(1, count($filas)));
        $cols = [['Prioridad', 30, 'L'], ['Objetivo', 28, 'C'], ['Resueltos', 24, 'C'], ['Dentro de SLA', 30, 'C'], ['Cumplimiento', 68, 'L']];
        $this->asegurarEspacio(7 + 8 * max(1, count($filas)));
        $this->encabezadoTabla($cols);
        foreach ($filas as $i => $f) {
            $this->asegurarEspacio(8);
            $y = $this->GetY();
            $fondo = ($i % 2 === 0);
            $this->color(self::FONDO, 'fill');
            $this->color(self::AZUL);
            $this->SetFont('Helvetica', 'B', 9);
            $this->celda($cols[0][1], 8, $f['nombre'], 0, 0, 'L', $fondo);
            $this->SetFont('Helvetica', '', 9);
            $this->color([30, 41, 59]);
            $this->celda($cols[1][1], 8, $f['objetivo'], 0, 0, 'C', $fondo);
            $this->celda($cols[2][1], 8, (string)$f['resueltos'], 0, 0, 'C', $fondo);
            $this->celda($cols[3][1], 8, (string)$f['dentro'], 0, 0, 'C', $fondo);
            $this->celda($cols[4][1], 8, '', 0, 1, 'L', $fondo);

            // barra de cumplimiento dentro de la última celda
            $x = 15 + $cols[0][1] + $cols[1][1] + $cols[2][1] + $cols[3][1] + 3;
            $bw = 46;
            $this->color(self::BORDE, 'fill');
            $this->Rect($x, $y + 2.6, $bw, 2.8, 'F');
            if ($f['pct'] !== null) {
                $this->color($this->colorPorcentaje($f['pct']), 'fill');
                if ($f['pct'] > 0) $this->Rect($x, $y + 2.6, $bw * $f['pct'] / 100, 2.8, 'F');
            }
            $this->SetFont('Helvetica', 'B', 9);
            $this->color($this->colorPorcentaje($f['pct']));
            $this->SetXY($x + $bw + 2, $y + 1);
            $this->celda(16, 6, $f['pct'] !== null ? $f['pct'] . '%' : 'Sin datos');
            $this->SetY($y + 8);
        }
    }

    /** Lista de barras horizontales (categoría / departamento). */
    public function barras(string $titulo, string $subtitulo, array $filas, array $color): void
    {
        $this->seccion($titulo, $subtitulo, 19 + 7 * min(max(1, count($filas)), 3));
        $total = 0; $max = 1;
        foreach ($filas as $f) { $total += (int)$f['total']; $max = max($max, (int)$f['total']); }
        if (!$filas || $total === 0) {
            $this->SetFont('Helvetica', 'I', 9); $this->color(self::GRIS);
            $this->celda(0, 7, 'Sin tickets en este período.', 0, 1);
            return;
        }
        foreach ($filas as $f) {
            $this->asegurarEspacio(8);
            $y = $this->GetY();
            $v = (int)$f['total'];
            $this->SetFont('Helvetica', '', 9);
            $this->color([30, 41, 59]);
            $this->SetXY(15, $y);
            $this->celda(58, 6.5, $this->recortar($f['nombre'], 56));
            $bx = 75; $bw = 90;
            $this->color(self::BORDE, 'fill');
            $this->Rect($bx, $y + 1.9, $bw, 3, 'F');
            if ($v > 0) { $this->color($color, 'fill'); $this->Rect($bx, $y + 1.9, $bw * $v / $max, 3, 'F'); }
            $this->SetXY($bx + $bw + 3, $y);
            $this->SetFont('Helvetica', 'B', 9);
            $this->celda(12, 6.5, (string)$v, 0, 0, 'R');
            $this->SetFont('Helvetica', '', 8);
            $this->color(self::GRIS);
            $this->celda(15, 6.5, ' (' . round($v / $total * 100) . '%)');
            $this->SetY($y + 7);
        }
    }

    public function notas(): void
    {
        $this->asegurarEspacio(38);
        $this->Ln(6);
        $this->color(self::FONDO, 'fill');
        $this->color(self::BORDE, 'draw');
        $y = $this->GetY();
        $this->Rect(15, $y, 180, 30, 'DF');
        $this->SetXY(19, $y + 3);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->color(self::AZUL);
        $this->celda(0, 4.5, 'Cómo se calculan las cifras', 0, 1);
        $this->SetFont('Helvetica', '', 8);
        $this->color(self::GRIS);
        $lineas = [
            'Creados: tickets registrados dentro del período.   Resueltos: tickets con fecha de cierre dentro del período.',
            'SLA: un ticket cumple si se resolvió dentro del tiempo objetivo de su prioridad (se mide desde su creación).',
            'Activos hoy y Sin asignar hoy reflejan el estado al momento de generar este reporte, no el del período.',
            'Por categoría y por departamento se cuentan los tickets creados en el período.',
        ];
        foreach ($lineas as $l) { $this->SetX(19); $this->MultiCell(172, 4.2, self::t($l), 0, 'L'); }
    }
}

/** substr por caracteres UTF-8 sin exigir la extensión mbstring. */
function mb_substr_compat(string $s, int $inicio, int $largo): string
{
    if (function_exists('mb_substr')) return mb_substr($s, $inicio, $largo);
    preg_match_all('/./su', $s, $m);
    return implode('', array_slice($m[0], $inicio, $largo));
}
