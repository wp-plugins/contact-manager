<?php if ($type == 'contact_form') {
$atts = array_map('contact_do_shortcode', (array) $atts);
extract(shortcode_atts(array('data' => '', 'filter' => '', 'id' => '', 'limit' => ''), $atts));
global $wpdb;
$GLOBALS['contact_form_data'] = (array) (isset($GLOBALS['contact_form_data']) ? $GLOBALS['contact_form_data'] : array());
if ((isset($GLOBALS['contact_form_id'])) && ((!isset($GLOBALS['contact_form_data']['id'])) || ($GLOBALS['contact_form_data']['id'] != $GLOBALS['contact_form_id']))) {
$GLOBALS['contact_form_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = ".$GLOBALS['contact_form_id'], OBJECT); }
$contact_form_data = $GLOBALS['contact_form_data'];
$field = str_replace('-', '_', format_nice_name($data));
if (strstr($field, 'display')) { $field = 'displays_count'; } else { $field = 'messages_count'; }
$id = preg_split('#[^0-9]#', $id, 0, PREG_SPLIT_NO_EMPTY);
$m = count($id);

if ($m < 2) {
if ($m == 0) { $id = 0; }
else { $id = (int) $id[0]; }
if (($id == 0) || ((isset($contact_form_data['id'])) && ($id == $contact_form_data['id']))) { $data = $contact_form_data[$field]; }
else {
foreach (array('contact_form_id', 'contact_form_data') as $key) {
if (isset($GLOBALS[$key])) { $original[$key] = $GLOBALS[$key]; } }
$contact_form_data = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = $id", OBJECT);
$GLOBALS['contact_form_id'] = $id; $GLOBALS['contact_form_data'] = $contact_form_data;
$data = (isset($contact_form_data[$field]) ? $contact_form_data[$field] : 0); } }

else {
$data = 0; for ($i = 0; $i < $m; $i++) {
$id[$i] = (int) $id[$i];
$contact_form_data = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = ".$id[$i], OBJECT);
$data = $data + (isset($contact_form_data[$field]) ? $contact_form_data[$field] : 0); } } }

else {
date_default_timezone_set('UTC');
$atts = array_map('contact_do_shortcode', (array) $atts);
extract(shortcode_atts(array('data' => '', 'filter' => '', 'limit' => '', 'range' => '', 'status' => ''), $atts));
global $wpdb;

$datas = explode('+', $data);
$m = count($datas);
if ($m > 1) {
$atts['limit'] = '';
$data = 0; for ($i = 0; $i < $m; $i++) {
$atts['data'] = $datas[$i];
$data = $data + contact_counter($atts, '[total-number]'); } }
else {
$data = str_replace('_', '-', format_nice_name($data));
switch ($data) {
case 'forms': $table = $wpdb->prefix.'contact_manager_forms'; $field = ''; break;
case 'forms-categories': $table = $wpdb->prefix.'contact_manager_forms_categories'; $field = ''; break;
case 'messages': $table = $wpdb->prefix.'contact_manager_messages'; $field = ''; break;
default: $table = $wpdb->prefix.'contact_manager_messages'; $field = ''; }

$range = str_replace('_', '-', format_nice_name($range));
$time = time() + 3600*UTC_OFFSET;
if (is_numeric($range)) {
$range = (int) $range;
$start_date = date('Y-m-d', $time - 86400*$range).' 00:00:00';
$end_date = date('Y-m-d', $time - 86400).' 23:59:59';
$date_criteria = "AND (date BETWEEN '".$start_date."' AND '".$end_date."')"; }
else { switch ($range) {
case 'previous-week':
$N = (int) date('N', $time);
$start_date = date('Y-m-d', $time - 86400*($N + 6)).' 00:00:00';
$end_date = date('Y-m-d', $time - 86400*$N).' 23:59:59';
$date_criteria = "AND (date BETWEEN '".$start_date."' AND '".$end_date."')"; break;
case 'previous-half-month':
$j = (int) date('j', $time);
if ($j <= 15) {
$Y = (int) date('Y', $time);
$M = (int) date('n', $time);
if ($M == 1) { $m = 12; $y = $Y - 1; }
else { $m = $M - 1; $y = $Y; }
if ($m < 10) { $m = '0'.$m; }
$start_date = $y.'-'.$m.'-16 00:00:00';
$end_date = date('Y-m-d H:i:s', mktime(0, 0, 0, $M, 1, $Y) - 1); }
else {
$start_date = date('Y-m', $time).'-01 00:00:00';
$end_date = date('Y-m', $time).'-15 23:59:59'; }
$date_criteria = "AND (date BETWEEN '".$start_date."' AND '".$end_date."')"; break;
case 'previous-month':
case 'previous-bimester':
case 'previous-trimester':
case 'previous-quadrimester':
case 'previous-semester':
switch ($range) {
case 'previous-month': $months_number = 1; break;
case 'previous-bimester': $months_number = 2; break;
case 'previous-trimester': $months_number = 3; break;
case 'previous-quadrimester': $months_number = 4; break;
case 'previous-semester': $months_number = 6; }
$Y = (int) date('Y', $time);
$M = (int) date('n', $time);
$M = $M - ($M - 1)%$months_number;
if ($M == 1) { $m = 13 - $months_number; $y = $Y - 1; }
else { $m = $M - $months_number; $y = $Y; }
if ($m < 10) { $m = '0'.$m; }
$start_date = $y.'-'.$m.'-01 00:00:00';
$end_date = date('Y-m-d H:i:s', mktime(0, 0, 0, $M, 1, $Y) - 1);
$date_criteria = "AND (date BETWEEN '".$start_date."' AND '".$end_date."')"; break;
case 'previous-year':
$Y = (int) date('Y', $time);
$y = $Y - 1;
$start_date = $y.'-01-01 00:00:00';
$end_date = $y.'-12-31 23:59:59';
$date_criteria = "AND (date BETWEEN '".$start_date."' AND '".$end_date."')"; break;
default: $date_criteria = ''; } }

$status = str_replace('-', '_', format_nice_name($status));
if ($status == '') { $status_criteria = ''; }
else { $status_criteria = "AND status = '".$status."'"; }

$data_key = "contact_".$date_criteria."_".$status_criteria."_".$data;
if (isset($GLOBALS[$data_key])) { $data = $GLOBALS[$data_key]; }
else {
if (is_string($table)) {
if ($field == '') {
$row = $wpdb->get_row("SELECT count(*) as total FROM $table WHERE id > 0 $date_criteria $status_criteria", OBJECT);
$data = (int) (isset($row->total) ? $row->total : 0); }
else {
$row = $wpdb->get_row("SELECT SUM($field) AS total FROM $table WHERE id > 0 $date_criteria $status_criteria", OBJECT);
$data = (isset($row->total) ? round($row->total, 2) : 0); } }
else {
$data = 0; foreach ($table as $table_name) {
$row = $wpdb->get_row("SELECT SUM($field) AS total FROM $table_name WHERE id > 0 $date_criteria $status_criteria", OBJECT);
$data = $data + (isset($row->total) ? round($row->total, 2) : 0); } }
$GLOBALS[$data_key] = $data; } } }

$limit = str_replace(array('?', ',', ';'), '.', $limit);
if ($limit == '') { $limit = '0'; }
else { $limit = '0/'.$limit; }
$limit = preg_split('#[^0-9.]#', $limit, 0, PREG_SPLIT_NO_EMPTY);
$n = count($limit);

$i = 0; while (($i < $n) && ($limit[$i] <= $data)) { $k = $i; $i = $i + 1; }
if ($i < $n) { $remaining_number = $limit[$i] - $data; $total_remaining_number = $limit[$n - 1] - $data; }
else { $i = $n - 1; $remaining_number = 0; $total_remaining_number = 0; }

$content = explode('[after]', do_shortcode($content));

$tags = array('limit', 'number', 'remaining-number', 'total-limit', 'total-number', 'total-remaining-number');
foreach ($tags as $tag) {
$_tag = str_replace('-', '_', format_nice_name($tag));
if (isset($GLOBALS['contact_'.$_tag])) { $original['contact_'.$_tag] = $GLOBALS['contact_'.$_tag]; }
remove_shortcode($tag); add_shortcode($tag, create_function('$atts', '$atts["data"] = "'.$tag.'"; return contact_counter_tag($atts);')); }

$GLOBALS['contact_limit'] = $limit[$i];
$GLOBALS['contact_number'] = $data - $limit[$k];
$GLOBALS['contact_remaining_number'] = $remaining_number;
$GLOBALS['contact_total_limit'] = $limit[$n - 1];
$GLOBALS['contact_total_number'] = $data;
$GLOBALS['contact_total_remaining_number'] = $total_remaining_number;

$content = (isset($content[$k]) ? do_shortcode($content[$k]) : '');
$content = contact_filter_data($filter, $content);

foreach ($tags as $tag) {
$_tag = str_replace('-', '_', format_nice_name($tag));
if (isset($original['contact_'.$_tag])) { $GLOBALS['contact_'.$_tag] = $original['contact_'.$_tag]; }
remove_shortcode($tag); }

if ($type == 'contact_form') {
foreach (array('contact_form_id', 'contact_form_data') as $key) {
if (isset($original[$key])) { $GLOBALS[$key] = $original[$key]; } } }