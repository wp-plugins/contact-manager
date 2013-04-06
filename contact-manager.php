<?php
/*
Plugin Name: Contact Manager
Plugin URI: http://www.kleor-editions.com/contact-manager
Description: Allows you to create and manage your contact forms and messages.
Version: 5.5.1
Author: Kleor
Author URI: http://www.kleor-editions.com
Text Domain: contact-manager
License: GPL2
*/

/* 
Copyright 2012 Kleor Editions (http://www.kleor-editions.com)

This program is a free software. You can redistribute it and/or 
modify it under the terms of the GNU General Public License as 
published by the Free Software Foundation, either version 2 of 
the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, 
but without any warranty, without even the implied warranty of 
merchantability or fitness for a particular purpose. See the 
GNU General Public License for more details.
*/


if (!defined('HOME_URL')) { define('HOME_URL', get_option('home')); }
if (!defined('UTC_OFFSET')) { define('UTC_OFFSET', get_option('gmt_offset')); }
define('CONTACT_MANAGER_URL', plugin_dir_url(__FILE__));
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
define('CONTACT_MANAGER_VERSION', $plugin_data['Version']);

if (!function_exists('fix_url')) { include_once dirname(__FILE__).'/libraries/formatting-functions.php'; }
if (is_admin()) { include_once dirname(__FILE__).'/admin.php'; }

function install_contact_manager() { include dirname(__FILE__).'/install.php'; }

register_activation_hook(__FILE__, 'install_contact_manager');

global $wpdb;
$contact_manager_options = get_option('contact_manager');
if (((is_multisite()) || ($contact_manager_options)) && ((!isset($contact_manager_options['version']))
 || ($contact_manager_options['version'] != CONTACT_MANAGER_VERSION))) { install_contact_manager(); }

fix_url();


function add_contact_form_in_posts($content) {
global $post;
if ((contact_data('automatic_display_enabled') == 'yes')
 && ((contact_data('automatic_display_only_on_single_post_pages') == 'no') || (is_single()))) {
$id = contact_data('automatic_display_form_id');
$location = contact_data('automatic_display_location');
$quantity = contact_data('automatic_display_maximum_forms_quantity');
if (!isset($_ENV['contact_form'.$id.'_number'])) { $_ENV['contact_form'.$id.'_number'] = 0; }
foreach (array('top', 'bottom') as $string) {
if ((strstr($location, $string)) && (($quantity == 'unlimited') || ($_ENV['contact_form'.$id.'_number'] < $quantity))) {
include_once dirname(__FILE__).'/forms.php';
$content = ($string == 'top' ? '' : $content).contact_form(array('id' => $id)).($string == 'bottom' ? '' : $content); } } }
return $content; }

foreach (array('get_the_content', 'the_content') as $function) { add_filter($function, 'add_contact_form_in_posts'); }


function add_message($message) { include dirname(__FILE__).'/add-message.php'; }


function contact_cron() {
$cron = get_option('contact_manager_cron');
if ($cron) {
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$current_timestamp = time();
$installation = (array) $cron['previous_installation'];
if ($installation['version'] != CONTACT_MANAGER_VERSION) {
$cron['previous_installation'] = array('version' => CONTACT_MANAGER_VERSION, 'number' => 0, 'timestamp' => $current_timestamp); }
elseif (($installation['number'] < 12) && (($current_timestamp - $installation['timestamp']) >= pow(2, $installation['number'] + 2))) {
$cron['previous_installation']['timestamp'] = $current_timestamp; }
if ($cron['previous_installation'] != $installation) {
update_option('contact_manager_cron', $cron);
wp_remote_get(CONTACT_MANAGER_URL.'?action=install'); } }
elseif ((is_multisite()) || (get_option('contact_manager'))) { wp_remote_get(CONTACT_MANAGER_URL.'?action=install'); } }

if ((!defined('CONTACT_MANAGER_DEMO')) || (CONTACT_MANAGER_DEMO == false)) {
foreach (array('admin_footer', 'wp_footer') as $hook) { add_action($hook, 'contact_cron'); } }


function contact_data($atts) {
global $contact_manager_options;
if (is_string($atts)) { $field = $atts; $decimals = ''; $default = ''; $filter = ''; $part = 0; }
else {
$field = (isset($atts[0]) ? $atts[0] : '');
foreach (array('decimals', 'default', 'filter') as $key) { $$key = (isset($atts[$key]) ? $atts[$key] : ''); }
$part = (int) (isset($atts['part']) ? $atts['part'] : 0); }
$field = str_replace('-', '_', format_nice_name($field));
if ($field == '') { $field = 'version'; }
if (($field == 'code') || (substr($field, -10) == 'email_body') || (substr($field, -19) == 'custom_instructions')) {
$data = get_option(substr('contact_manager_'.$field, 0, 64)); }
else { $data = (isset($contact_manager_options[$field]) ? $contact_manager_options[$field] : ''); }
if ($part > 0) { $data = explode(',', $data); $data = (isset($data[$part - 1]) ? trim($data[$part - 1]) : ''); }
$data = (string) do_shortcode($data);
if ($data == '') { $data = $default; }
$data = contact_format_data($field, $data);
$data = contact_filter_data($filter, $data);
$data = contact_decimals_data($decimals, $data);
return $data; }


function contact_decimals_data($decimals, $data) {
if (($decimals != '') && (is_numeric($data))) {
$decimals = explode('/', $decimals);
for ($i = 0; $i < count($decimals); $i++) { $decimals[$i] = (int) $decimals[$i]; }
if ($data == round($data)) { $data = number_format($data, min($decimals), '.', ''); }
else { $data = number_format($data, max($decimals), '.', ''); } }
return $data; }


function contact_decrypt_url($url) {
if (strstr($url, '?url=')) {
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$url = explode('?url=', $url);
$url = $url[1];
$url = base64_decode($url);
$url = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, md5(contact_data('encrypted_urls_key')), $url, MCRYPT_MODE_ECB));
$url = explode('|', $url);
$T = $url[0];
$url = $url[1];
$S = time() - $T;
if ($S > 3600*contact_data('encrypted_urls_validity_duration')) { $url = HOME_URL; } }
return $url; }


function contact_encrypt_url($url) {
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$url = time().'|'.$url;
$url = mcrypt_encrypt(MCRYPT_BLOWFISH, md5(contact_data('encrypted_urls_key')), $url, MCRYPT_MODE_ECB);
$url = base64_encode($url);
$url = CONTACT_MANAGER_URL.'?url='.$url;
return $url; }


function contact_filter_data($filter, $data) {
if (is_string($filter)) { $filter = preg_split('#[^a-zA-Z0-9_]#', str_replace('-', '_', $filter), 0, PREG_SPLIT_NO_EMPTY); }
if (is_array($filter)) { foreach ($filter as $function) { $data = contact_string_map($function, $data); } }
return $data; }


function contact_format_data($field, $data) {
$data = do_shortcode($data);
if ($field != 'code') { $data = quotes_entities_decode($data); }
if ((strstr($field, 'date')) && ($data == '0000-00-00 00:00:00')) { $data = ''; }
elseif (substr($field, -13) == 'email_address') { $data = format_email_address($data); }
elseif (substr($field, -12) == 'instructions') { $data = format_instructions($data); }
elseif ((($field == 'url') || (strstr($field, '_url'))) && (!strstr($field, 'urls'))) { $data = format_url($data); }
switch ($field) {
case 'automatic_display_maximum_forms_quantity': case 'maximum_messages_quantity': if ($data != 'unlimited') { $data = (int) $data; } break;
case 'maximum_messages_quantity_per_sender': if ($data != 'unlimited') { $data = (int) $data; } if ($data == 0) { $data = 'unlimited'; } break;
case 'commission_amount': case 'commission2_amount': case 'encrypted_urls_validity_duration': $data = round(100*$data)/100; }
return $data; }


function contact_forms_categories_list($id) {
global $wpdb;
$id = (int) $id;
$list = array($id);
while ($id > 0) {
$category = $wpdb->get_row("SELECT category_id FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE id = $id", OBJECT);
if ($category) { $id = $category->category_id; }
if ((!$category) || (in_array($id, $list))) { $id = 0; }
$list[] = $id; }
return $list; }


function contact_i18n($string) {
load_plugin_textdomain('contact-manager', false, 'contact-manager/languages');
return __(__($string), 'contact-manager'); }


function contact_user_data($atts) { include dirname(__FILE__).'/user-data.php'; return $data; }


function contact_item_data($type, $atts) { include dirname(__FILE__).'/item-data.php'; return $data; }


function contact_form_data($atts) {
if ((is_array($atts)) && (!isset($atts[0]))) { include_once dirname(__FILE__).'/forms.php'; return contact_form($atts); }
elseif ((is_array($atts)) && (!isset($atts['id'])) && (isset($atts['category']))) { return contact_form_category_data($atts); }
else { return contact_item_data('contact_form', $atts); } }


function contact_form_category_data($atts) {
return contact_item_data('contact_form_category', $atts); }


function message_data($atts) {
return contact_item_data('message', $atts); }


function contact_jquery_js() {
if (!defined('KLEOR_JQUERY_LOADED')) { define('KLEOR_JQUERY_LOADED', true); ?>
<script type="text/javascript" src="<?php echo CONTACT_MANAGER_URL; ?>libraries/jquery.js"></script>
<?php } }


function contact_sql_array($table, $array) {
foreach ($table as $key => $value) {
if (!isset($array[$key])) { $array[$key] = ''; }
$sql[$key] = ($key == 'password' ? hash('sha256', $array[$key]) : $array[$key]);
if (isset($value['type'])) {
if ($value['type'] == 'int') { $sql[$key] = (int) $sql[$key]; }
elseif ((strstr($value['type'], 'dec')) && (!is_numeric($sql[$key]))) { $sql[$key] = round(100*$sql[$key])/100; }
elseif (($value['type'] == 'text') || ($value['type'] == 'datetime')) {
$sql[$key] = "'".str_replace("'", "''", stripslashes($sql[$key]))."'"; } } }
return $sql; }


function contact_string_map($function, $string) {
if (!function_exists($function)) { $function = 'contact_'.$function; }
if (function_exists($function)) { $array = array_map($function, array($string)); $string = $array[0]; }
return $string; }


for ($i = 0; $i < 4; $i++) {
foreach (array('contact-content', 'contact-counter', 'contact-form-counter') as $tag) {
add_shortcode($tag.($i == 0 ? '' : $i), create_function('$atts, $content', 'include_once dirname(__FILE__)."/shortcodes.php"; return '.str_replace('-', '_', $tag).'($atts, $content);')); } }
add_shortcode('user', 'contact_user_data');
add_shortcode('contact-manager', 'contact_data');
add_shortcode('contact-form', 'contact_form_data');
add_shortcode('message', 'message_data');
add_shortcode('sender', 'message_data');


foreach (array(
'get_the_excerpt',
'get_the_title',
'single_post_title',
'the_excerpt',
'the_excerpt_rss',
'the_title',
'the_title_attribute',
'the_title_rss',
'widget_text',
'widget_title') as $function) { add_filter($function, 'do_shortcode'); }