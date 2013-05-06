<?php global $wpdb;
if (strstr($type, 'category')) { $attribute = 'category'; } else { $attribute = 'id'; }
switch ($type) {
case 'contact_form': $table = 'forms'; $default_field = 'name'; break;
case 'contact_form_category': $table = 'forms_categories'; $default_field = 'name'; break;
case 'message': $table = 'messages'; $default_field = 'subject'; break; }
$GLOBALS[$type.'_data'] = (array) (isset($GLOBALS[$type.'_data']) ? $GLOBALS[$type.'_data'] : array());
if ((isset($GLOBALS[$type.'_id'])) && ((!isset($GLOBALS[$type.'_data']['id'])) || ($GLOBALS[$type.'_data']['id'] != $GLOBALS[$type.'_id']))) {
$n = $GLOBALS[$type.'_id']; $GLOBALS[$type.$n.'_data'] = (array) (isset($GLOBALS[$type.$n.'_data']) ? $GLOBALS[$type.$n.'_data'] : array());
if ((isset($GLOBALS[$type.$n.'_data']['id'])) && ($GLOBALS[$type.$n.'_data']['id'] == $GLOBALS[$type.'_id'])) { $GLOBALS[$type.'_data'] = $GLOBALS[$type.$n.'_data']; }
else { $GLOBALS[$type.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table." WHERE id = ".$GLOBALS[$type.'_id'], OBJECT); } }
if (!is_admin()) {
if (($type == 'message') && (!isset($GLOBALS[$type.'_data']['email_address'])) && (!isset($GLOBALS[$type.'_searched_by_ip_address']))) {
$GLOBALS[$type.'_searched_by_ip_address'] = 'yes';
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table." WHERE ip_address = '".$_SERVER['REMOTE_ADDR']."' ORDER BY date DESC LIMIT 1", OBJECT);
if ($result) { $GLOBALS[$type.'_data'] = (array) $result; } }
if (isset($GLOBALS[$type.'_data']['id'])) { $n = $GLOBALS[$type.'_data']['id']; $GLOBALS[$type.$n.'_data'] = $GLOBALS[$type.'_data']; } }
$item_data = $GLOBALS[$type.'_data'];
if (is_string($atts)) { $field = $atts; $decimals = ''; $default = ''; $filter = ''; $id = 0; $part = 0; }
else {
$field = (isset($atts[0]) ? $atts[0] : '');
foreach (array('decimals', 'default', 'filter') as $key) {
$$key = (isset($atts[$key]) ? $atts[$key] : '');
if (isset($atts[$key])) { unset($atts[$key]); } }
$id = (int) (isset($atts[$attribute]) ? do_shortcode(str_replace(array('(', ')'), array('[', ']'), $atts[$attribute])) : 0);
$part = (int) (isset($atts['part']) ? $atts['part'] : 0); }
$field = str_replace('-', '_', format_nice_name($field));
if ($field == '') { $field = $default_field; }
if (($id > 0) && ((!isset($item_data['id'])) || ($id != $item_data['id']))) {
foreach (array($type.'_id', $type.'_data') as $key) {
if (isset($GLOBALS[$key])) { $original[$key] = $GLOBALS[$key]; } }
if ((!isset($GLOBALS[$type.$id.'_data'])) || (!isset($GLOBALS[$type.$id.'_data']['id'])) || ($GLOBALS[$type.$id.'_data']['id'] != $id)) {
$GLOBALS[$type.$id.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table." WHERE id = $id", OBJECT); }
$item_data = $GLOBALS[$type.$id.'_data'];
if ($attribute == 'id') { $GLOBALS[$type.'_id'] = $id; $GLOBALS[$type.'_data'] = $item_data; } }
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
if ((is_array($atts)) && (isset($atts['category']))) { unset($atts['category']); }
$data = contact_data($atts); } break; }
$data = (string) do_shortcode($data);
if ($data == '') { $data = $default; }
$data = contact_format_data($field, $data);
$data = contact_filter_data($filter, $data);
$data = contact_decimals_data($decimals, $data);
foreach (array($type.'_id', $type.'_data') as $key) {
if (isset($original[$key])) { $GLOBALS[$key] = $original[$key]; } }