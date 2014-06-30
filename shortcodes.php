<?php function contact_content($atts, $content) {
$atts = array_map('contact_do_shortcode', (array) $atts);
extract(shortcode_atts(array('filter' => '', 'id' => ''), $atts));
global $wpdb;
$content = explode('[other]', do_shortcode($content));
$forms = array_unique(array_map('intval', preg_split('#[^0-9]#', $id, 0, PREG_SPLIT_NO_EMPTY)));
if (is_admin()) { if ((isset($GLOBALS['contact_form_id'])) && (in_array($GLOBALS['contact_form_id'], $forms))) { $n = 0; } else { $n = 1; } }
else {
$search_criteria = '';
if (count($forms) > 0) {
foreach ($forms as $form) { $search_criteria .= " OR form_id = ".$form; }
$search_criteria = 'AND ('.substr($search_criteria, 4).')'; }
$result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_messages WHERE ip_address = '".str_replace("'", "''", $_SERVER['REMOTE_ADDR'])."' $search_criteria", OBJECT);
if ($result) { $n = 0; } else { $n = 1; } }
if (!isset($content[$n])) { $content[$n] = ''; }
return contact_filter_data($filter, $content[$n]); }


function contact_counter_tag($atts) {
$atts = array_map('contact_do_shortcode', (array) $atts);
extract(shortcode_atts(array('data' => '', 'decimals' => '0/2', 'filter' => ''), $atts));
$string = $GLOBALS['contact_'.str_replace('-', '_', format_nice_name($data))];
$string = contact_filter_data($filter, $string);
$string = contact_decimals_data($decimals, $string);
return $string; }


function contact_counter($atts, $content) {
$type = '';
include CONTACT_MANAGER_PATH.'includes/counter.php';
return $content; }


function contact_form_counter($atts, $content) {
$type = 'contact_form';
include CONTACT_MANAGER_PATH.'includes/counter.php';
return $content; }