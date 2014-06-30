<?php if (($decimals != '') && (is_numeric($data))) {
$decimals = explode('/', $decimals);
$n = count($decimals); for ($i = 0; $i < $n; $i++) { $decimals[$i] = (int) $decimals[$i]; }
if ($data == round($data)) { $data = number_format((float) $data, min($decimals), '.', ''); }
else { $data = number_format((float) $data, max($decimals), '.', ''); } }