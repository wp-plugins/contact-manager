<?php if (strstr($_SERVER['REQUEST_URI'], '/plugins.php')) { load_plugin_textdomain('contact-manager', false, 'contact-manager/languages'); }
if ((isset($_GET['page'])) && (strstr($_GET['page'], 'contact-manager'))) { include_once dirname(__FILE__).'/admin-pages-functions.php'; }


function contact_manager_admin_menu() {
include dirname(__FILE__).'/admin-pages.php';
$options = (array) get_option('contact_manager_back_office');
if (((isset($_GET['page'])) && (strstr($_GET['page'], 'contact-manager'))) || ($options['menu_title'] == '')) { $options['menu_title'] = __('Contact', 'contact-manager'); }
$menu_items = (array) $options['menu_items'];
$numbers = (array) $options['menu_displayed_items'];
$menu_displayed_items = array();
foreach ($numbers as $i) { $menu_displayed_items[] = $menu_items[$i]; }
if ((defined('CONTACT_MANAGER_DEMO')) && (CONTACT_MANAGER_DEMO == true)) { $capability = 'manage_options'; }
else { $role = $options['minimum_roles']['view']; $capability = $roles[$role]['capability']; }
if ($options['custom_icon_used'] == 'yes') { $icon_url = format_url($options['custom_icon_url']); } else { $icon_url = ''; }
add_menu_page('Contact Manager', $options['menu_title'], $capability, 'contact-manager', create_function('', 'include_once "options-page.php";'), $icon_url);
foreach ($admin_pages as $key => $value) {
$slug = 'contact-manager'.($key == '' ? '' : '-'.str_replace('_', '-', $key));
if ((!isset($_GET['page'])) || (!strstr($_GET['page'], 'contact-manager'))) { $value['menu_title'] = $options['pages_titles'][$key]; }
if (($key == '') || ($key == 'back_office') || ((isset($_GET['page'])) && ($_GET['page'] == $slug)) || (in_array($key, $menu_displayed_items))) {
add_submenu_page('contact-manager', $value['page_title'], $value['menu_title'], $capability, $slug, create_function('', 'include_once "'.$value['file'].'";')); } } }

add_action('admin_menu', 'contact_manager_admin_menu');


function contact_manager_action_links($links, $file) {
if ($file == 'contact-manager/contact-manager.php') {
if (!is_multisite()) {
$links = array_merge($links, array(
'<a href="admin.php?page=contact-manager&amp;action=uninstall">'.__('Uninstall', 'contact-manager').'</a>')); }
$links = array_merge($links, array(
'<a href="admin.php?page=contact-manager&amp;action=reset">'.__('Reset', 'contact-manager').'</a>',
'<a href="admin.php?page=contact-manager">'.__('Options', 'contact-manager').'</a>')); }
return $links; }

add_filter('plugin_action_links', 'contact_manager_action_links', 10, 2);


function contact_manager_row_meta($links, $file) {
if ($file == 'contact-manager/contact-manager.php') {
$links = array_merge($links, array(
'<a href="http://www.kleor-editions.com/contact-manager">'.__('Documentation', 'contact-manager').'</a>')); }
return $links; }

add_filter('plugin_row_meta', 'contact_manager_row_meta', 10, 2);


function reset_contact_manager() {
load_plugin_textdomain('contact-manager', false, 'contact-manager/languages');
include dirname(__FILE__).'/initial-options.php';
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
update_option(substr('contact_manager'.$_key, 0, 64), $value); } }


function uninstall_contact_manager() {
global $wpdb;
include dirname(__FILE__).'/tables.php';
foreach ($tables as $table_slug => $table) {
$results = $wpdb->query("DROP TABLE ".$wpdb->prefix.'contact_manager_'.$table_slug); }
include dirname(__FILE__).'/initial-options.php';
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
delete_option(substr('contact_manager'.$_key, 0, 64)); } }