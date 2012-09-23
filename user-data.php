<?php global $user_ID, $wpdb;
$_GET['user_data'] = (array) (isset($_GET['user_data']) ? $_GET['user_data'] : array());
if ((!isset($_GET['user_id'])) && (function_exists('is_user_logged_in'))) { if (is_user_logged_in()) { $_GET['user_id'] = $user_ID; } }
if ((isset($_GET['user_id'])) && ((!isset($_GET['user_data']['ID'])) || ($_GET['user_data']['ID'] != $_GET['user_id']))) {
$n = $_GET['user_id']; $_GET['user'.$n.'_data'] = (array) (isset($_GET['user'.$n.'_data']) ? $_GET['user'.$n.'_data'] : array());
if ((isset($_GET['user'.$n.'_data']['ID'])) && ($_GET['user'.$n.'_data']['ID'] == $_GET['user_id'])) { $_GET['user_data'] = $_GET['user'.$n.'_data']; }
else { $_GET['user_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE ID = ".$_GET['user_id'], OBJECT); } }
$user_data = $_GET['user_data'];
if (is_string($atts)) { $field = $atts; $default = ''; $filter = ''; $id = 0; }
else {
$field = (isset($atts[0]) ? $atts[0] : '');
$default = (isset($atts['default']) ? $atts['default'] : '');
$filter = (isset($atts['filter']) ? $atts['filter'] : '');
$id = (int) (isset($atts['id']) ? do_shortcode(str_replace(array('(', ')'), array('[', ']'), $atts['id'])) : 0); }
$field = str_replace('-', '_', format_nice_name($field));
if ($field == '') { $field = 'login'; }
switch ($field) {
case 'date': case 'date_utc': $field = 'user_registered'; break;
case 'email_address': $field = 'user_email'; break;
case 'id': $field = 'ID'; break;
case 'login': $field = 'user_login'; break;
case 'website_url': $field = 'user_url'; break; }
if (($id == 0) || ((isset($user_data['ID'])) && ($id == $user_data['ID']))) {
$data = (isset($user_data[$field]) ? $user_data[$field] : ''); }
else {
foreach (array('user_id', 'user_data') as $key) {
if (isset($_GET[$key])) { $original[$key] = $_GET[$key]; } }
if ((!isset($_GET['user'.$id.'_data'])) || (!isset($_GET['user'.$id.'_data']['ID'])) || ($_GET['user'.$id.'_data']['ID'] != $id)) {
$_GET['user'.$id.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE ID = ".$id, OBJECT); }
$user_data = $_GET['user'.$id.'_data'];
$_GET['user_id'] = $id; $_GET['user_data'] = $user_data;
$data = (isset($user_data[$field]) ? $user_data[$field] : ''); }
switch ($field) {
case 'first_name': case 'last_name': if (($data == '') && (isset($_GET['user_id']))) {
$result = $wpdb->get_row("SELECT meta_value FROM ".$wpdb->base_prefix."usermeta WHERE meta_key = '".$field."' AND user_id = ".$_GET['user_id'], OBJECT);
if ($result) { $data = $result->meta_value; } } }
$data = (string) do_shortcode($data);
if ($data == '') { $data = $default; }
$data = contact_filter_data($filter, $data);
foreach (array('user_id', 'user_data') as $key) {
if (isset($original[$key])) { $_GET[$key] = $original[$key]; } }