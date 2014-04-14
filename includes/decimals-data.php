<?php if (($decimals != '') && (is_numeric($data))) {
$decimals = explode('/', $decimals);
for ($i = 0; $i < count($decimals); $i++) { $decimals[$i] = (int) $decimals[$i]; }
if ($data == round($data)) { $data = number_format($data, min($decimals), '.', ''); }
else { $data = number_format($data, max($decimals), '.', ''); } }