<?php
// includes/ReporteGestionPDF.php — PDF del reporte de gestión completo (FPDF, A4). Independiente de ReportePDF para no tocarlo.
require_once __DIR__ . '/ReportePDF.php';
require_once __DIR__ . '/sla.php';

class ReporteGestionPDF extends FPDF
{
    const AZUL = [26, 42, 74]; const NARANJA = [232, 119, 34]; const GRIS = [100, 116, 139]; const BORDE = [226, 232, 240];
    const FONDO = [248, 250, 252]; const VERDE = [22, 163, 74]; const AMBAR = [217, 119, 6]; const ROJO = [220, 38, 38];
    private string $por; private string $en;

    public function __construct(string $generado_por) {
        parent::__construct('P', 'mm', 'A4');
        $this->por = $generado_por; $this->en = date('d/m/Y H:i');
        $this->SetMargins(15, 24, 15); $this->SetAutoPageBreak(true, 18); $this->AliasNbPages();
        $this->SetTitle('Reporte de gestión — Unicon HelpDesk', true); $this->SetAuthor('Unicon HelpDesk', true);
    }
    private function col(array $c, string $t = 'text'): void { if ($t === 'fill') $this->SetFillColor(...$c); elseif ($t === 'draw') $this->SetDrawColor(...$c); else $this->SetTextColor(...$c); }
    private function c($w, $h, string $txt, $b = 0, $ln = 0, string $al = '', bool $f = false): void { $this->Cell($w, $h, ReportePDF::t($txt), $b, $ln, $al, $f); }
    private function n($v, int $dec = 1): string { return $v === null ? '—' : number_format((float)$v, $dec, ',', '.'); }

    public function Header(): void {
        $this->col(self::AZUL, 'fill'); $this->Rect(0, 0, 210, 15, 'F'); $this->col(self::NARANJA, 'fill'); $this->Rect(0, 15, 210, 1.2, 'F');
        $this->SetTextColor(255, 255, 255); $this->SetFont('Helvetica', 'B', 11); $this->SetXY(15, 4.5); $this->c(100, 6, 'Industrias Unicon C.A.');
        $this->SetFont('Helvetica', '', 9); $this->SetXY(95, 4.5); $this->c(100, 6, 'Unicon HelpDesk — Reporte de gestión completo', 0, 0, 'R'); $this->SetY(24);
    }
    public function Footer(): void {
        $this->SetY(-12); $this->col(self::BORDE, 'draw'); $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->SetFont('Helvetica', '', 8); $this->col(self::GRIS); $this->SetY(-10);
        $this->c(130, 5, 'Generado el ' . $this->en . ' por ' . $this->por); $this->c(50, 5, 'Página ' . $this->PageNo() . ' de {nb}', 0, 0, 'R');
    }
    private function seccion(string $t, string $sub = '', float $espacio = 40): void {
        if ($this->GetY() + $espacio > $this->PageBreakTrigger) $this->AddPage();
        $this->Ln(3); $this->SetFont('Helvetica', 'B', 12); $this->col(self::AZUL); $this->c(0, 7, $t, 0, 1);
        $this->col(self::NARANJA, 'fill'); $this->Rect(15, $this->GetY(), 14, 0.8, 'F'); $this->Ln(2.5);
        if ($sub !== '') { $this->SetFont('Helvetica', '', 8.5); $this->col(self::GRIS); $this->c(0, 4.5, $sub, 0, 1); $this->Ln(1.5); }
    }
    private function cab(array $cols): void {
        $this->col(self::AZUL, 'fill'); $this->SetTextColor(255, 255, 255); $this->SetFont('Helvetica', 'B', 8.5);
        foreach ($cols as $c) $this->c($c[1], 7, $c[0], 0, 0, $c[2], true); $this->Ln();
    }
    private function fila(array $cols, array $vals, bool $zebra, ?array $colorUlt = null): void {
        $this->col(self::FONDO, 'fill'); $this->SetFont('Helvetica', '', 8.5); $this->col([30, 41, 59]);
        foreach ($cols as $i => $c) {
            if ($colorUlt && $i === count($cols) - 1) { $this->col($colorUlt); $this->SetFont('Helvetica', 'B', 8.5); }
            $t = (string)$vals[$i]; while ($t !== '' && $this->GetStringWidth(ReportePDF::t($t)) > $c[1] - 2) $t = mb_substr_compat($t, 0, -1);
            $this->c($c[1], 6.5, $t, 0, 0, $c[2], $zebra);
        }
        $this->Ln();
    }
    private function flecha(?float $dl, bool $mejorSube): array {
        if ($dl === null) return ['—', self::GRIS];
        if (abs($dl) < 0.05) return ['= igual', self::GRIS];
        $bien = ($dl > 0) === $mejorSube;
        return [($dl > 0 ? '+' : '-') . number_format(abs($dl), 1, ',', '.') . '%', $bien ? self::VERDE : self::ROJO];
    }

    public function contenido(array $d): void {
        $this->AddPage();
        $a = $d['actual']; $p = $d['previo'];
        $this->SetFont('Helvetica', 'B', 17); $this->col(self::AZUL); $this->c(0, 9, 'Reporte de gestión', 0, 1);
        $this->SetFont('Helvetica', '', 10); $this->col(self::GRIS);
        $this->c(0, 5.5, 'Período: ' . date('d/m/Y', strtotime($d['desde'])) . ' al ' . date('d/m/Y', strtotime($d['hasta'])) . ' (' . $d['dias'] . ' días)', 0, 1);
        $this->c(0, 5.5, 'Comparado con: ' . date('d/m/Y', strtotime($d['prev_desde'])) . ' al ' . date('d/m/Y', strtotime($d['prev_hasta'])), 0, 1);

        // 1) Comparación
        $this->seccion('Comparación con el período anterior', 'Verde = mejora; rojo = empeora.', 62);
        $cols = [['Indicador', 72, 'L'], ['Este período', 32, 'R'], ['Anterior', 32, 'R'], ['Variación', 44, 'R']];
        $this->cab($cols);
        $fil = [
            ['Tickets creados', (string)$a['creados'], (string)$p['creados'], $this->flecha(rg_delta($a['creados'], $p['creados']), false)],
            ['Tickets cerrados', (string)$a['cerrados'], (string)$p['cerrados'], $this->flecha(rg_delta($a['cerrados'], $p['cerrados']), true)],
            ['Tiempo medio de resolución (h)', $this->n($a['prom_horas']), $this->n($p['prom_horas']), $this->flecha(rg_delta($a['prom_horas'], $p['prom_horas']), false)],
            ['Cumplimiento de SLA', $a['sla_pct'] === null ? '—' : $a['sla_pct'] . '%', $p['sla_pct'] === null ? '—' : $p['sla_pct'] . '%', $this->flecha(rg_delta($a['sla_pct'], $p['sla_pct']), true)],
            ['Satisfacción (1-5)', $this->n($a['sat_prom'], 2) . ' (' . $a['sat_n'] . ')', $this->n($p['sat_prom'], 2) . ' (' . $p['sat_n'] . ')', $this->flecha(rg_delta($a['sat_prom'], $p['sat_prom']), true)],
        ];
        foreach ($fil as $i => $f) { $this->fila($cols, [$f[0], $f[1], $f[2], $f[3][0]], $i % 2 === 0, $f[3][1]); }

        // 2) Tendencia
        $this->seccion('Tendencia mensual', 'Naranja = creados · Azul = cerrados.', 20 + 9 * count($d['mensual']));
        $max = 1; foreach ($d['mensual'] as $m) $max = max($max, $m['creados'], $m['cerrados']);
        foreach ($d['mensual'] as $m) {
            $y = $this->GetY(); $this->SetFont('Helvetica', '', 8.5); $this->col([30, 41, 59]); $this->SetXY(15, $y); $this->c(22, 4, $m['mes']);
            $this->col(self::NARANJA, 'fill'); $this->Rect(38, $y, max(0.5, 115 * $m['creados'] / $max), 3.2, 'F');
            $this->col(self::AZUL, 'fill'); $this->Rect(38, $y + 3.8, max(0.5, 115 * $m['cerrados'] / $max), 3.2, 'F');
            $this->SetXY(158, $y); $this->c(37, 4, $m['creados'] . ' creados / ' . $m['cerrados'] . ' cerrados', 0, 0, 'R'); $this->SetY($y + 8.5);
        }

        // 3) SLA por prioridad
        $this->seccion('SLA por prioridad', 'Tickets cerrados en el período. El tiempo en «Pendiente» (esperando al usuario) no cuenta.', 40);
        $cols = [['Prioridad', 40, 'L'], ['Objetivo', 32, 'R'], ['Cerrados', 28, 'R'], ['Tiempo medio', 38, 'R'], ['Cumplidos', 42, 'R']];
        $this->cab($cols);
        foreach ($d['sla'] as $i => $s) {
            $col = $s['pct'] === null ? self::GRIS : ($s['pct'] >= 80 ? self::VERDE : ($s['pct'] >= 50 ? self::AMBAR : self::ROJO));
            $this->fila($cols, [$s['nombre'], sla_formato((int)$s['obj']), (string)$s['n'], sla_formato($s['prom']), $s['pct'] === null ? '—' : $s['pct'] . '%  (' . $s['ok'] . '/' . $s['n'] . ')'], $i % 2 === 0, $col);
        }
        if (!$d['sla']) { $this->SetFont('Helvetica', 'I', 9); $this->col(self::GRIS); $this->c(0, 6, 'Sin tickets cerrados en el período.', 0, 1); }

        // 4) Equipos con fallas repetidas
        $this->seccion('Equipos con fallas repetidas', 'Equipos con 2 o más tickets en el período: candidatos a revisión profunda o reemplazo.', 40);
        $cols = [['Equipo', 60, 'L'], ['Tipo', 40, 'L'], ['Departamento', 50, 'L'], ['Tickets', 30, 'R']];
        $this->cab($cols);
        foreach ($d['equipos'] as $i => $e) $this->fila($cols, [$e['codigo_equipo'], $e['tipo_equipo'], $e['depto'], (string)$e['n']], $i % 2 === 0, self::ROJO);
        if (!$d['equipos']) { $this->SetFont('Helvetica', 'I', 9); $this->col(self::GRIS); $this->c(0, 6, 'Ningún equipo con 2 o más tickets en el período.', 0, 1); }

        // 5) Satisfacción
        $this->seccion('Satisfacción de los usuarios', 'Valoración (1 a 5) que dan los usuarios al cerrar su ticket.', 70);
        $tot = max(1, array_sum($d['sat_dist']));
        for ($i = 5; $i >= 1; $i--) {
            $y = $this->GetY(); $this->SetFont('Helvetica', '', 9); $this->col([30, 41, 59]); $this->SetXY(15, $y); $this->c(14, 5, $i . ' est.');
            $this->col(self::BORDE, 'fill'); $this->Rect(30, $y + 1, 100, 3, 'F');
            $this->col(self::NARANJA, 'fill'); if ($d['sat_dist'][$i]) $this->Rect(30, $y + 1, 100 * $d['sat_dist'][$i] / $tot, 3, 'F');
            $this->SetXY(133, $y); $this->c(20, 5, (string)$d['sat_dist'][$i]); $this->SetY($y + 6);
        }
        $this->Ln(2);
        $cols = [['Técnico', 110, 'L'], ['Encuestas', 30, 'R'], ['Promedio', 40, 'R']];
        $this->cab($cols);
        foreach ($d['sat_tec'] as $i => $t) $this->fila($cols, [$t['nombre'], (string)$t['n'], $this->n($t['prom'], 2)], $i % 2 === 0, self::AZUL);
        if (!$d['sat_tec']) { $this->SetFont('Helvetica', 'I', 9); $this->col(self::GRIS); $this->c(0, 6, 'Sin encuestas en el período.', 0, 1); }
    }
}
