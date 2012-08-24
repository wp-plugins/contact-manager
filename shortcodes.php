<?php function contact_content($atts, $content) {
global $wpdb;
extract(shortcode_atts(array('id' => ''), $atts));
$content = explode('[other]', do_shortcode($content));
$forms = array_unique(preg_split('#[^0-9]#', $id, 0, PREG_SPLIT_NO_EMPTY));
if (is_admin()) { if (in_array($_GET['contact_form_id'], $forms)) { $n = 0; } else { $n = 1; } }
else {
if (count($forms) > 0) {
foreach ($forms as $form) { $search_criteria .= " OR form_id = ".$form; }
$search_criteria = 'AND ('.substr($search_criteria, 4).')'; }
$result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_messages WHERE (ip_address = '".$_SERVER['REMOTE_ADDR']."' OR ip_address = '".message_data('ip_address')."') $search_criteria", OBJECT);
if ($result) { $n = 0; } else { $n = 1; } }
return $content[$n]; }


function contact_counter_tag($atts) {
extract(shortcode_atts(array('data' => '', 'filter' => ''), $atts));
$string = $_GET['contact_'.str_replace('-', '_', format_nice_name($data))];
$string = contact_filter_data($filter, $string);
return $string; }


function contact_counter($atts, $content) {
include dirname(__FILE__).'/counter.php';
return $content[$k]; }


function contact_form_counter($atts, $content) {
$type = 'contact_form';
include dirname(__FILE__).'/counter.php';
return $content[$k]; }


function contact_user_data($atts) {
global $user_ID, $wpdb;
$_GET['user_data'] = (array) $_GET['user_data'];
if ((!isset($_GET['user_id'])) && (function_exists('is_user_logged_in'))) { if (is_user_logged_in()) { $_GET['user_id'] = $user_ID; } }
if ((isset($_GET['user_id'])) && ($_GET['user_data']['ID'] != $_GET['user_id'])) {
$_GET['user_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE ID = ".$_GET['user_id'], OBJECT); }
$user_data = $_GET['user_data'];
if (is_string($atts)) { $field = $atts; $default = ''; $filter = ''; $id = 0; }
else {
$field = $atts[0];
$default = $atts['default'];
$filter = $atts['filter'];
$id = (int) do_shortcode(str_replace(array('(', ')'), array('[', ']'), $atts['id'])); }
$field = str_replace('-', '_', format_nice_name($field));
if ($field == '') { $field = 'login'; }
switch ($field) {
case 'date': case 'date_utc': $field = 'user_registered'; break;
case 'email_address': $field = 'user_email'; break;
case 'id': $field = 'ID'; break;
case 'login': $field = 'user_login'; break;
case 'website_url': $field = 'user_url'; break; }
if (($id == 0) || ($id == $user_data['ID'])) { $data = $user_data[$field]; }
else {
foreach (array('user_id', 'user_data') as $key) {
if (isset($_GET[$key])) { $original[$key] = $_GET[$key]; } }
if ($_GET['user'.$id.'_data']['ID'] != $id) { $_GET['user'.$id.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE ID = ".$id, OBJECT); }
$user_data = $_GET['user'.$id.'_data'];
$_GET['user_id'] = $id; $_GET['user_data'] = $user_data;
$data = $user_data[$field]; }
$data = (string) do_shortcode($data);
if ($data == '') { $data = $default; }
$data = contact_filter_data($filter, $data);
foreach (array('user_id', 'user_data') as $key) {
if (isset($original[$key])) { $_GET[$key] = $original[$key]; } }
return $data; }