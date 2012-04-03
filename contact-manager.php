<?php
/*
Plugin Name: Contact Manager
Plugin URI: http://www.kleor-editions.com/contact-manager
Description: Allows you to create and manage your contact forms and messages.
Version: 1.0
Author: Kleor
Author URI: http://www.kleor-editions.com
Text Domain: contact-manager
License: GPL2
*/

/* 
Copyright 2010 Kleor Editions (http://www.kleor-editions.com)

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

global $wpdb;
$contact_manager_options = get_option('contact_manager');
if (($contact_manager_options) && ($contact_manager_options['version'] != CONTACT_MANAGER_VERSION)) {
include_once dirname(__FILE__).'/admin.php';
install_contact_manager(); }

fix_url();


function add_contact_form_in_posts($content) {
global $post;
if ((is_single()) && (contact_data('automatic_display_enabled') == 'yes')) {
include_once dirname(__FILE__).'/forms.php';
$contact_form = contact_form(array('id' => contact_data('automatic_display_form_id')));
if (contact_data('automatic_display_location') == 'top') { $content = $contact_form.$content; }
else { $content .= $contact_form; } }
return $content; }

add_filter('the_content', 'add_contact_form_in_posts');


function add_message($message) { include dirname(__FILE__).'/add-message.php'; }


function contact_data($atts) {
global $contact_manager_options;
if (is_string($atts)) { $field = $atts; $default = ''; $filter = ''; $part = 0; }
else { $field = $atts[0]; $default = $atts['default']; $filter = $atts['filter']; $part = (int) $atts['part']; }
$field = str_replace('-', '_', format_nice_name($field));
if ($field == '') { $field = 'version'; }
if ((strstr($field, 'email_body')) || ($field == 'code') || ($field == 'message_custom_instructions')) { $data = get_option('contact_manager_'.$field); }
else { $data = $contact_manager_options[$field]; }
if ($part > 0) { $data = explode(',', $data); $data = trim($data[$part - 1]); }
$data = (string) do_shortcode($data);
if ($data == '') { $data = $default; }
$data = contact_format_data($field, $data);
$data = contact_filter_data($filter, $data);
return $data; }


function contact_decrypt_url($url) {
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$url = explode('?url=', $url);
$url = $url[1];
$url = base64_decode($url);
$url = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, md5(contact_data('encrypted_urls_key')), $url, MCRYPT_MODE_ECB));
$url = explode('|', $url);
$T = $url[0];
$url = $url[1];
$S = time() - $T;
if ($S > 3600*contact_data('encrypted_urls_validity_duration')) { $url = HOME_URL; }
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
$data = quotes_entities_decode(do_shortcode($data));
if ((strstr($field, 'date')) && ($data == '0000-00-00 00:00:00')) { $data = ''; }
elseif (substr($field, -13) == 'email_address') { $data = format_email_address($data); }
elseif (substr($field, -12) == 'instructions') { $data = format_instructions($data); }
elseif ((($field == 'url') || (strstr($field, '_url'))) && (!strstr($field, 'urls'))) { $data = format_url($data); }
switch ($field) {
case 'maximum_messages_quantity': if ($data != 'unlimited') { $data = (int) $data; } break;
case 'commission_amount': case 'commission2_amount': case 'encrypted_urls_validity_duration': $data = round(100*$data)/100; }
return $data; }


function contact_forms_categories_list($id) {
global $wpdb;
$id = (int) $id;
$list = array($id);
while ($id > 0) {
$category = $wpdb->get_row("SELECT category_id FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE id = '$id'", OBJECT);
if ($category) { $id = $category->category_id; }
if ((!$category) || (in_array($id, $list))) { $id = 0; }
$list[] = $id; }
return $list; }


function contact_i18n($string) {
load_plugin_textdomain('contact-manager', false, 'contact-manager/languages');
return __(__($string), 'contact-manager'); }


function contact_item_data($type, $atts) {
global $wpdb;
if (strstr($type, 'category')) { $attribute = 'category'; } else { $attribute = 'id'; }
switch ($type) {
case 'contact_form': $table = 'forms'; $default_field = 'name'; break;
case 'contact_form_category': $table = 'forms_categories'; $default_field = 'name'; break;
case 'message': $table = 'messages'; $default_field = 'subject'; break; }
$_GET[$type.'_data'] = (array) $_GET[$type.'_data'];
if ((isset($_GET[$type.'_id'])) && ($_GET[$type.'_data']['id'] != $_GET[$type.'_id'])) {
$_GET[$type.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table." WHERE id = '".$_GET[$type.'_id']."'", OBJECT); }
$item_data = $_GET[$type.'_data'];
if (is_string($atts)) { $field = $atts; $default = ''; $filter = ''; $id = 0; $part = 0; }
else {
$field = $atts[0];
$default = $atts['default'];
unset($atts['default']);
$filter = $atts['filter'];
unset($atts['filter']);
$id = (int) do_shortcode(str_replace(array('(', ')'), array('[', ']'), $atts[$attribute]));
$part = (int) $atts['part']; }
$field = str_replace('-', '_', format_nice_name($field));
if ($field == '') { $field = $default_field; }
if (($id == 0) || ($id == $item_data['id'])) { $data = $item_data[$field]; }
elseif ($id > 0) {
foreach (array($type.'_id', $type.'_data') as $key) {
if (isset($_GET[$key])) { $original[$key] = $_GET[$key]; } }
if ($_GET[$type.$id.'_data']['id'] != $id) {
$_GET[$type.$id.'_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table." WHERE id = '$id'", OBJECT); }
$item_data = $_GET[$type.$id.'_data'];
if ($attribute == 'id') { $_GET[$type.'_id'] = $id; $_GET[$type.'_data'] = $item_data; }
$data = $item_data[$field]; }
if ($part > 0) { $data = explode(',', $data); $data = trim($data[$part - 1]); }
switch ($type) {
case 'contact_form': case 'contact_form_category':
$data = (string) $data;
if ($data != '') { $data = contact_format_data($field, $data); }
$data = (string) $data;
if (($data == '') && ($item_data['category_id'] > 0)) {
if (is_string($atts)) { $atts = array($field); }
$atts['category'] = $item_data['category_id'];
$data = contact_form_category_data($atts); }
elseif ($data == '') {
if (is_array($atts)) { unset($atts['category']); }
$data = contact_data($atts); } break; }
$data = (string) do_shortcode($data);
if ($data == '') { $data = $default; }
$data = contact_format_data($field, $data);
$data = contact_filter_data($filter, $data);
foreach (array($type.'_id', $type.'_data') as $key) {
if (isset($original[$key])) { $_GET[$key] = $original[$key]; } }
return $data; }


function contact_form_data($atts) {
if ((is_array($atts)) && (!isset($atts[0]))) { include_once dirname(__FILE__).'/forms.php'; return contact_form($atts); }
elseif ((is_array($atts)) && (!isset($atts['id'])) && (isset($atts['category']))) { return contact_form_category_data($atts); }
else { return contact_item_data('contact_form', $atts); } }


function contact_form_category_data($atts) {
return contact_item_data('contact_form_category', $atts); }


function message_data($atts) {
return contact_item_data('message', $atts); }


function contact_jquery_js() { ?>
<script type="text/javascript" src="<?php echo CONTACT_MANAGER_URL; ?>libraries/jquery.js"></script>
<?php }


function contact_string_map($function, $string) {
if (!function_exists($function)) { $function = 'contact_'.$function; }
if (function_exists($function)) { $array = array_map($function, array($string)); $string = $array[0]; }
return $string; }



for ($i = 0; $i < 16; $i++) {
add_shortcode('contact-content'.($i == 0 ? '' : $i), create_function('$atts, $content', 'include_once dirname(__FILE__)."/shortcodes.php"; return contact_content($atts, $content);'));
foreach (array('', 'data-') as $string) { add_shortcode('contact-'.$string.'counter'.($i == 0 ? '' : $i), create_function('$atts, $content', 'include_once dirname(__FILE__)."/shortcodes.php"; return contact_counter($atts, $content);')); }
add_shortcode('contact-form-counter'.($i == 0 ? '' : $i), create_function('$atts, $content', 'include_once dirname(__FILE__)."/shortcodes.php"; return contact_form_counter($atts, $content);')); }
add_shortcode('user', create_function('$atts', 'include_once dirname(__FILE__)."/shortcodes.php"; return contact_user_data($atts);'));
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