<?php $atts = (array) $atts; $string = '';
foreach ($atts as $key => $value) { if (is_int($key)) { $string .= $value.' '; } }
$string = trim($string);
if (strstr($string, '=')) {
$new_keys = array();
$array = explode('=', $string);
$n = count($array); for ($i = 0; $i < $n - 1; $i++) {
$array2 = array_reverse(explode(' ', $array[$i]));
if (($array2[0] != '') && (!in_array(substr($array2[0], 0, 1), array('"', "'")))
 && (!in_array($array2[0], $new_keys))) { $new_keys[] = $array2[0]; } }
foreach ($new_keys as $key) {
if ($string != '') {
$array = explode($key.'=', $string);
if (!isset($array[1])) { $string = ''; }
else {
$string = $array[1];
$n = count($array); for ($i = 2; $i < $n; $i++) { $string .= $key.'='.$array[$i]; }
$character = substr($string, 0, 1); switch ($character) {
case '"': case "'": $array2 = explode($character, $string);
$atts[$key] = $array2[1]; $string = substr($string, strlen($array2[1]) + 2); break;
default: $array2 = explode(' ', $string);
$atts[$key] = $array2[0]; $string = substr($string, strlen($array2[0])); } } } } }
$atts = array_map('contact_do_shortcode', (array) $atts);
foreach ($default_values as $key => $value) {
if ((!isset($atts[$key])) || ($atts[$key] == '')) { $atts[$key] = $default_values[$key]; } }