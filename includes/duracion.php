<?php
// includes/duracion.php — muestra un tiempo en horas de forma legible: 0.4 → "24 min", 5.5 → "5 h 30 min", 644 → "26 d 20 h".
function hd_duracion($horas): string {
    if ($horas === null || $horas === '' || !is_numeric($horas)) return '—';
    $min = (int)round(((float)$horas) * 60);
    if ($min < 1)    return '< 1 min';
    if ($min < 60)   return $min . ' min';
    if ($min < 1440) { $h = intdiv($min, 60); $m = $min % 60; return $h . ' h' . ($m ? " $m min" : ''); }
    $d = intdiv($min, 1440); $h = intdiv($min % 1440, 60);
    return $d . ' d' . ($h ? " $h h" : '');
}
