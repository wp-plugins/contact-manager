<?php global $user_ID, $wpdb;
$GLOBALS['user_data'] = (array) (isset($GLOBALS['user_data']) ? $GLOBALS['user_data'] : array());
if ((!isset($GLOBALS['user_id'])) && (function_exists('is_user_logged_in')) && (is_user_logged_in())) { $GLOBALS['user_id'] = $user_ID; }
if ((isset($GLOBALS['user_id'])) && ((!isset($GLOBALS['user_data']['ID'])) || ($GLOBALS['user_data']['ID'] != $GLOBALS['user_id']))) {
$n = $GLOBALS['user_id']; $GLOBALS['user'.$n.'_data'] = (array) (isset($GLOBALS['user'.$n.'_data']) ? $GLOBALS['user'.$n.'_data'] : array());
if ((isset($GLOBALS['user'.$n.'_data']['ID'])) && ($GLOBALS['user'.$n.'_data']['ID'] == $GLOBALS['user_id'])) { $GLOBALS['user_data'] = $GLOBALS['user'.$n.'_data']; }
elseif ($GLOBALS['user_id'] > 0) { $GLOBALS['user_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE ID = ".$GLOBALS['user_id'], OBJECT); } }
$user_data = $GLOBALS['user_data'];
if (is_string($atts)) { $field = $atts; $default = ''; $filter = ''; $id = 0; }
else {
$atts = array_map('contact_do_shortcode', (array) $atts);
$field = (isset($atts[0]) ? $atts[0] : '');
foreach (array('default', 'filter', 'id') as $key) { $$key = (isset($atts[$key]) ? $atts[$key] : ''); }
$id = (int) $id; }
$field = str_replace('-', '_', format_nice_name($field));
if ($field == '') { $field = 'login'; }
switch ($field) {
case 'date': case 'date_utc': $field = 'user_registered'; break;
case 'email_address': $field = 'user_email'; break;
case 'id': $field = 'ID'; break;
case 'login': $field = 'user_login'; break;
case 'website_url': $field = 'user_url'; break; }
if (($id > 0) && ((!isset($user_data['ID'])) || ($id != $user_data['ID']))) {
foreach (array('user_id', 'user_data') as $key) {
if (isset($GLOBALS[$key])) { $original[$key] = $GLOBALS[$key]; } }
if ((!isset($GLOBALS['user'.$id.'_data'])) || (!isset($GLOBALS['user'.$id.'_data']['ID'])) || ($GLOBALS['user'.$id.'_data']['ID'] != $id)) {
$GLOBALS['user'.$id.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE ID = ".$id, OBJECT); }
$user_data = $GLOBALS['user'.$id.'_data'];
$GLOBALS['user_id'] = $id; $GLOBALS['user_data'] = $user_data; }
$data = (isset($user_data[$field]) ? $user_data[$field] : '');
switch ($field) {
case 'first_name': case 'last_name': if (($data == '') && (isset($GLOBALS['user_id'])) && ($GLOBALS['user_id'] > 0)) {
$n = $GLOBALS['user_id']; $GLOBALS['user'.$n.'_data'] = (array) (isset($GLOBALS['user'.$n.'_data']) ? $GLOBALS['user'.$n.'_data'] : array());
if (isset($GLOBALS['user'.$n.'_data'][$field])) { $data = $GLOBALS['user'.$n.'_data'][$field]; }
else {
$result = $wpdb->get_row("SELECT meta_value FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = '".$field."' AND user_id = ".$n, OBJECT);
if ($result) { $data = $result->meta_value; $GLOBALS['user'.$n.'_data'][$field] = $data; } } } }
$data = (string) do_shortcode($data);
if ($data === '') { $data = $default; }
$data = contact_filter_data($filter, $data);
foreach (array('user_id', 'user_data') as $key) {
if (isset($original[$key])) { $GLOBALS[$key] = $original[$key]; } }