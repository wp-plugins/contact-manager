<?php global $wpdb;
switch ($type) {
case 'contact_form': $table = 'forms'; $default_field = 'name'; break;
case 'contact_form_category': $table = 'forms_categories'; $default_field = 'name'; break;
case 'message': $table = 'messages'; $default_field = 'subject'; break; }
$GLOBALS[$type.'_data'] = (array) (isset($GLOBALS[$type.'_data']) ? $GLOBALS[$type.'_data'] : array());
if ((isset($GLOBALS[$type.'_id'])) && ((!isset($GLOBALS[$type.'_data']['id'])) || ($GLOBALS[$type.'_data']['id'] != $GLOBALS[$type.'_id']))) {
$n = $GLOBALS[$type.'_id']; $GLOBALS[$type.$n.'_data'] = (array) (isset($GLOBALS[$type.$n.'_data']) ? $GLOBALS[$type.$n.'_data'] : array());
if ((isset($GLOBALS[$type.$n.'_data']['id'])) && ($GLOBALS[$type.$n.'_data']['id'] == $GLOBALS[$type.'_id'])) { $GLOBALS[$type.'_data'] = $GLOBALS[$type.$n.'_data']; }
elseif ($GLOBALS[$type.'_id'] > 0) { $GLOBALS[$type.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table." WHERE id = ".$GLOBALS[$type.'_id'], OBJECT); } }
if (!is_admin()) {
if (($type == 'message') && (!isset($GLOBALS[$type.'_data']['email_address'])) && (!isset($GLOBALS[$type.'_searched_by_ip_address']))) {
$GLOBALS[$type.'_searched_by_ip_address'] = 'yes';
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table." WHERE ip_address = '".str_replace("'", "''", $_SERVER['REMOTE_ADDR'])."' ORDER BY date DESC LIMIT 1", OBJECT);
if ($result) { $GLOBALS[$type.'_data'] = (array) $result; } }
if (isset($GLOBALS[$type.'_data']['id'])) { $n = $GLOBALS[$type.'_data']['id']; $GLOBALS[$type.$n.'_data'] = $GLOBALS[$type.'_data']; } }
$item_data = $GLOBALS[$type.'_data'];
if (is_string($atts)) { $is_array = false; $field = $atts; $decimals = ''; $default = ''; $filter = ''; $id = 0; $part = 0; }
else {
$is_array = true;
$atts = array_map('contact_do_shortcode', (array) $atts);
$field = (isset($atts[0]) ? $atts[0] : '');
foreach (array('decimals', 'default', 'filter') as $key) {
$$key = (isset($atts[$key]) ? $atts[$key] : '');
if (isset($atts[$key])) { unset($atts[$key]); } }
$id = (int) (isset($atts['id']) ? $atts['id'] : 0);
$part = (int) (isset($atts['part']) ? $atts['part'] : 0); }
$field = str_replace('-', '_', format_nice_name($field));
if ($field == '') { $field = $default_field; }
if (($id > 0) && ((!isset($item_data['id'])) || ($id != $item_data['id']))) {
foreach (array($type.'_id', $type.'_data') as $key) {
if (isset($GLOBALS[$key])) { $original[$key] = $GLOBALS[$key]; } }
if ((!isset($GLOBALS[$type.$id.'_data'])) || (!isset($GLOBALS[$type.$id.'_data']['id'])) || ($GLOBALS[$type.$id.'_data']['id'] != $id)) {
$GLOBALS[$type.$id.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table." WHERE id = $id", OBJECT); }
$item_data = $GLOBALS[$type.$id.'_data'];
if (!strstr($type, 'category')) { $GLOBALS[$type.'_id'] = $id; $GLOBALS[$type.'_data'] = $item_data; } }
if ((!isset($item_data[$field])) && (isset($item_data['custom_fields'])) && (substr($field, 0, 13) == 'custom_field_')) {
$item_custom_fields = (array) unserialize(stripslashes($item_data['custom_fields']));
foreach ($item_custom_fields as $key => $value) { $item_data['custom_field_'.$key] = $value; } }
$data = (isset($item_data[$field]) ? $item_data[$field] : '');
if ($part > 0) { $data = explode(',', $data); $data = (isset($data[$part - 1]) ? trim($data[$part - 1]) : ''); }
switch ($type) {
case 'contact_form': case 'contact_form_category':
$data = (string) $data;
if ($data != '') { $data = contact_format_data($field, $data); }
$data = (string) $data;
if (($data == '') && (isset($item_data['category_id'])) && ($item_data['category_id'] > 0)) {
if (is_string($atts)) { $atts = array($field); }
$atts['category'] = $item_data['category_id'];
$data = contact_form_category_data($atts); }
elseif ($data == '') {
if (is_array($atts)) { foreach (array('id', 'category') as $key) { if (isset($atts[$key])) { unset($atts[$key]); } } }
$data = contact_data($atts); } break; }
$data = (string) do_shortcode($data);
if ($data == '') { $data = $default; }
$data = contact_format_data($field, $data);
$data = contact_filter_data($filter, $data);
$data = contact_decimals_data($decimals, $data);

if (($is_array) && ($default == '') && (!is_admin()) && (function_exists('wp_get_current_user')) && (function_exists('current_user_can')) && (current_user_can('edit_pages'))) {
if (((strstr($type, 'category')) || ($type == 'contact_form')) && ((!isset($item_data['id'])) || ($item_data['id'] == 0))) {
load_plugin_textdomain('contact-manager', false, 'contact-manager/languages');
$data = sprintf(__('You did not complete correctly the %1$s attribute of the %2$s shortcode.', 'contact-manager'), 'id', '['.str_replace('_', '-', $type).']'); } }

foreach (array($type.'_id', $type.'_data') as $key) {
if (isset($original[$key])) { $GLOBALS[$key] = $original[$key]; } }