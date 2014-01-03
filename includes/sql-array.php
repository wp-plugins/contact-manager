<?php foreach ($table as $key => $value) {
if (!isset($array[$key])) { $array[$key] = ''; }
$sql[$key] = ($key == 'password' ? hash('sha256', $array[$key]) : $array[$key]);
if (isset($value['type'])) {
if ($value['type'] == 'int') { $sql[$key] = (int) $sql[$key]; }
elseif ((strstr($value['type'], 'dec')) && (!is_numeric($sql[$key]))) { $sql[$key] = round($sql[$key], 2); }
elseif (($value['type'] == 'text') || ($value['type'] == 'datetime')) {
while (strstr($sql[$key], "\'")) { $sql[$key] = str_replace("\'", "'", $sql[$key]); }
$sql[$key] = "'".str_replace("'", "''", $sql[$key])."'"; } } }