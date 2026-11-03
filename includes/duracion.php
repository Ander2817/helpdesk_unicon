<?php
// includes/duracion.php — tiempos legibles y mediana.
//  hd_duracion(5.5)  → "5 h 30 min" · hd_duracion(644) → "26 d 20 h"
//  hd_mediana([..])  → mediana de una lista de números (la mediana no se distorsiona por unos pocos tickets muy atrasados)
function hd_duracion($horas): string {
    if ($horas === null || $horas === '' || !is_numeric($horas)) return '—';
    $min = (int)round(((float)$horas) * 60);
    if ($min < 1)    return '< 1 min';
    if ($min < 60)   return $min . ' min';
    if ($min < 1440) { $h = intdiv($min, 60); $m = $min % 60; return $h . ' h' . ($m ? " $m min" : ''); }
    $d = intdiv($min, 1440); $h = intdiv($min % 1440, 60);
    return $d . ' d' . ($h ? " $h h" : '');
}
function hd_mediana(array $v): ?float {
    $v = array_values(array_filter(array_map('floatval', $v), fn($x) => is_finite($x)));
    $n = count($v); if (!$n) return null;
    sort($v);
    return $n % 2 ? $v[intdiv($n, 2)] : ($v[$n / 2 - 1] + $v[$n / 2]) / 2;
}
/** "hace 5 min" / "hace 2 h" / "hace 3 d" a partir de una fecha 'Y-m-d H:i:s' */
function hd_hace(?string $fecha): string {
    if (!$fecha) return '—';
    $d = time() - strtotime($fecha);
    if ($d < 60) return 'hace un momento';
    if ($d < 3600) return 'hace ' . floor($d / 60) . ' min';
    if ($d < 86400) return 'hace ' . floor($d / 3600) . ' h';
    return 'hace ' . floor($d / 86400) . ' d';
}
