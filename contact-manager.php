<?php
/*
Plugin Name: Contact Manager
Plugin URI: http://www.kleor-editions.com/contact-manager
Description: Allows you to create and manage your contact forms and messages.
Version: 5.6.1
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
define('CONTACT_MANAGER_PATH', dirname(__FILE__));
define('CONTACT_MANAGER_URL', plugin_dir_url(__FILE__));
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
define('CONTACT_MANAGER_VERSION', $plugin_data['Version']);

if (!function_exists('fix_url')) { include_once CONTACT_MANAGER_PATH.'/libraries/formatting-functions.php'; }
if (is_admin()) { include_once CONTACT_MANAGER_PATH.'/admin.php'; }

function install_contact_manager() { include CONTACT_MANAGER_PATH.'/includes/install.php'; }

register_activation_hook(__FILE__, 'install_contact_manager');

global $wpdb;
$contact_manager_options = get_option('contact_manager');
if (((is_multisite()) || ($contact_manager_options)) && ((!isset($contact_manager_options['version']))
 || ($contact_manager_options['version'] != CONTACT_MANAGER_VERSION))) { install_contact_manager(); }

fix_url();


function add_contact_form_in_posts($content) { include CONTACT_MANAGER_PATH.'/includes/add-contact-form-in-posts.php'; return $content; }

foreach (array('get_the_content', 'the_content') as $function) { add_filter($function, 'add_contact_form_in_posts'); }


function add_message($message) { include CONTACT_MANAGER_PATH.'/includes/add-message.php'; }


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
foreach (array('admin_footer', 'login_footer', 'wp_footer') as $hook) { add_action($hook, 'contact_cron'); } }


function contact_data($atts) { include CONTACT_MANAGER_PATH.'/includes/data.php'; return $data; }


function contact_decimals_data($decimals, $data) {
if (($decimals != '') && (is_numeric($data))) {
$decimals = explode('/', $decimals);
for ($i = 0; $i < count($decimals); $i++) { $decimals[$i] = (int) $decimals[$i]; }
if ($data == round($data)) { $data = number_format($data, min($decimals), '.', ''); }
else { $data = number_format($data, max($decimals), '.', ''); } }
return $data; }


function contact_decrypt_url($url) { $action = 'decrypt'; include CONTACT_MANAGER_PATH.'/includes/crypt-url.php'; return $url; }


function contact_encrypt_url($url) { $action = 'encrypt'; include CONTACT_MANAGER_PATH.'/includes/crypt-url.php'; return $url; }


function contact_filter_data($filter, $data) {
if (is_string($filter)) { $filter = preg_split('#[^a-zA-Z0-9_]#', str_replace('-', '_', $filter), 0, PREG_SPLIT_NO_EMPTY); }
if (is_array($filter)) { foreach ($filter as $function) { $data = contact_string_map($function, $data); } }
return $data; }


function contact_format_data($field, $data) { include CONTACT_MANAGER_PATH.'/includes/format-data.php'; return $data; }


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


function contact_user_data($atts) { include CONTACT_MANAGER_PATH.'/includes/user-data.php'; return $data; }


function contact_item_data($type, $atts) { include CONTACT_MANAGER_PATH.'/includes/item-data.php'; return $data; }


function contact_form_data($atts) {
if ((is_array($atts)) && (!isset($atts[0]))) { include_once CONTACT_MANAGER_PATH.'/forms.php'; return contact_form($atts); }
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


function contact_sql_array($table, $array) { include CONTACT_MANAGER_PATH.'/includes/sql-array.php'; return $sql; }


function contact_string_map($function, $string) {
if (!function_exists($function)) { $function = 'contact_'.$function; }
if (function_exists($function)) { $array = array_map($function, array($string)); $string = $array[0]; }
return $string; }


for ($i = 0; $i < 4; $i++) {
foreach (array('contact-content', 'contact-counter', 'contact-form-counter') as $tag) {
add_shortcode($tag.($i == 0 ? '' : $i), create_function('$atts, $content', 'include_once CONTACT_MANAGER_PATH."/shortcodes.php"; return '.str_replace('-', '_', $tag).'($atts, $content);')); } }
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