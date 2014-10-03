<?php
/*
Plugin Name: Contact Manager
Plugin URI: http://www.kleor.com/contact-manager
Description: Allows you to create and manage your contact forms and messages.
Version: 6.0
Author: Kleor
Author URI: http://www.kleor.com
Text Domain: contact-manager
License: GPL2
*/

/* 
Copyright 2012 Kleor (http://www.kleor.com)

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
if (!defined('ROOT_URL')) { $url = explode('/', str_replace('//', '||', HOME_URL)); define('ROOT_URL', str_replace('||', '//', $url[0])); }
if (!defined('HOME_PATH')) { $path = str_replace(ROOT_URL, '', HOME_URL); define('HOME_PATH', ($path == '' ? '/' : $path)); }
if (!defined('UTC_OFFSET')) { define('UTC_OFFSET', get_option('gmt_offset')); }
define('CONTACT_MANAGER_PATH', plugin_dir_path(__FILE__));
define('CONTACT_MANAGER_URL', plugin_dir_url(__FILE__));
define('CONTACT_MANAGER_FOLDER', str_replace('/contact-manager.php', '', plugin_basename(__FILE__)));
$plugin_data = get_file_data(__FILE__, array('Version' => 'Version'));
define('CONTACT_MANAGER_VERSION', $plugin_data['Version']);

if (!function_exists('fix_url')) { include_once CONTACT_MANAGER_PATH.'libraries/formatting-functions.php'; }
if (is_admin()) { include_once CONTACT_MANAGER_PATH.'admin.php'; }

function install_contact_manager($context = '') { include CONTACT_MANAGER_PATH.'includes/install.php'; }

function activate_contact_manager() { install_contact_manager('activation'); }

register_activation_hook(__FILE__, 'activate_contact_manager');

global $wpdb;
$contact_manager_options = (array) get_option('contact_manager');
if ((!isset($contact_manager_options['version'])) || ($contact_manager_options['version'] != CONTACT_MANAGER_VERSION)) { install_contact_manager(); }

fix_url();


function add_contact_form_in_posts($content) { include CONTACT_MANAGER_PATH.'includes/add-contact-form-in-posts.php'; return $content; }

foreach (array('get_the_content', 'the_content') as $function) { add_filter($function, 'add_contact_form_in_posts'); }


function add_message($message) { include CONTACT_MANAGER_PATH.'includes/add-message.php'; }


function contact_cron() { include CONTACT_MANAGER_PATH.'includes/cron.php'; }

if ((!defined('CONTACT_MANAGER_DEMO')) || (CONTACT_MANAGER_DEMO == false)) {
foreach (array('admin_footer', 'login_footer', 'wp_footer') as $hook) { add_action($hook, 'contact_cron'); } }


function contact_data($atts) { include CONTACT_MANAGER_PATH.'includes/data.php'; return $data; }


function contact_decimals_data($decimals, $data) { include CONTACT_MANAGER_PATH.'includes/decimals-data.php'; return $data; }


function contact_decrypt_url($url) { $action = 'decrypt'; include CONTACT_MANAGER_PATH.'includes/crypt-url.php'; return $url; }


function contact_encrypt_url($url) { $action = 'encrypt'; include CONTACT_MANAGER_PATH.'includes/crypt-url.php'; return $url; }


function contact_do_shortcode($string) { include CONTACT_MANAGER_PATH.'includes/do-shortcode.php'; return $string; }


function contact_excerpt($data, $length = 80) {
$data = (string) $data;
if (strlen($data) > $length) { $data = substr($data, 0, ($length - 4)).' […]'; }
return $data; }


function contact_filter_data($filter, $data) { include CONTACT_MANAGER_PATH.'includes/filter-data.php'; return $data; }


function contact_format_data($field, $data) { include CONTACT_MANAGER_PATH.'includes/format-data.php'; return $data; }


function contact_forms_categories_list($id) { include CONTACT_MANAGER_PATH.'includes/categories-list.php'; return $list; }


function contact_i18n($string) {
load_plugin_textdomain('contact-manager', false, CONTACT_MANAGER_FOLDER.'/languages');
return __(__($string), 'contact-manager'); }


function contact_user_data($atts) { include CONTACT_MANAGER_PATH.'includes/user-data.php'; return $data; }


function contact_item_data($type, $atts) { include CONTACT_MANAGER_PATH.'includes/item-data.php'; return $data; }


function contact_form_data($atts) {
if ((is_array($atts)) && (!isset($atts[0]))) { include_once CONTACT_MANAGER_PATH.'forms.php'; return contact_form($atts); }
elseif ((is_array($atts)) && (!isset($atts['id'])) && (isset($atts['category']))) { return contact_form_category_data($atts); }
else { return contact_item_data('contact_form', $atts); } }


function contact_form_category_data($atts) {
if ((is_array($atts)) && (isset($atts['category']))) { $atts['id'] = $atts['category']; }
return contact_item_data('contact_form_category', $atts); }


function message_data($atts) {
return contact_item_data('message', $atts); }


function contact_shortcode_atts($default_values, $atts) { include CONTACT_MANAGER_PATH.'includes/shortcode-atts.php'; return $atts; }


function contact_sql_array($table, $array) { include CONTACT_MANAGER_PATH.'includes/sql-array.php'; return $sql; }


foreach (array('contact-content', 'contact-counter', 'contact-form-counter') as $tag) {
$function = create_function('$atts, $content', 'include_once CONTACT_MANAGER_PATH."shortcodes.php"; return '.str_replace('-', '_', $tag).'($atts, $content);');
for ($i = 0; $i < 4; $i++) { add_shortcode($tag.($i == 0 ? '' : $i), $function); } }
add_shortcode('user', 'contact_user_data');
add_shortcode('contact-manager', 'contact_data');
foreach (array(
'contact-form-category',
'contact-form',
'message') as $tag) { add_shortcode($tag, str_replace('-', '_', $tag).'_data'); }
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