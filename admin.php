<?php if (strstr($_SERVER['REQUEST_URI'], '/plugins.php')) { load_plugin_textdomain('contact-manager', false, 'contact-manager/languages'); }
if ((isset($_GET['page'])) && (strstr($_GET['page'], 'contact-manager'))) { include_once CONTACT_MANAGER_PATH.'/admin-pages-functions.php'; }


function contact_manager_admin_menu() {
include CONTACT_MANAGER_PATH.'/admin-pages.php';
$options = (array) get_option('contact_manager_back_office');
if (((isset($_GET['page'])) && (strstr($_GET['page'], 'contact-manager'))) || ($options['menu_title'] == '')) { $options['menu_title'] = __('Contact', 'contact-manager'); }
if ((defined('CONTACT_MANAGER_DEMO')) && (CONTACT_MANAGER_DEMO == true)) { $capability = 'manage_options'; }
else { $role = $options['minimum_roles']['view']; $capability = $roles[$role]['capability']; }
if ($options['custom_icon_used'] == 'yes') { $icon_url = format_url($options['custom_icon_url']); } else { $icon_url = ''; }
add_menu_page('Contact Manager', $options['menu_title'], $capability, 'contact-manager', create_function('', 'include_once "options-page.php";'), $icon_url);
$admin_menu_pages = contact_manager_admin_menu_pages();
foreach ($admin_pages as $key => $value) { if (in_array($key, $admin_menu_pages)) {
$slug = 'contact-manager'.($key == '' ? '' : '-'.str_replace('_', '-', $key));
if ((!isset($_GET['page'])) || (!strstr($_GET['page'], 'contact-manager'))) { $value['menu_title'] = $options['pages_titles'][$key]; }
add_submenu_page('contact-manager', $value['page_title'], $value['menu_title'], $capability, $slug, create_function('', 'include_once "'.$value['file'].'";')); } } }

add_action('admin_menu', 'contact_manager_admin_menu');


function contact_manager_admin_menu_pages() {
include CONTACT_MANAGER_PATH.'/admin-pages.php';
$options = (array) get_option('contact_manager_back_office');
$menu_items = (array) $options['menu_items'];
$numbers = (array) $options['menu_displayed_items'];
$menu_displayed_items = array();
foreach ($numbers as $i) { $menu_displayed_items[] = $menu_items[$i]; }
$admin_menu_pages = array(); foreach ($admin_pages as $key => $value) {
$slug = 'contact-manager'.($key == '' ? '' : '-'.str_replace('_', '-', $key));
if (($key == '') || ($key == 'back_office') || ((isset($_GET['page'])) && ($_GET['page'] == $slug))
 || (in_array($key, $menu_displayed_items))) { $admin_menu_pages[] = $key; } }
return $admin_menu_pages; }


function contact_manager_meta_box($post) {
include CONTACT_MANAGER_PATH.'/languages/meta-box/meta-box.php'; ?>
<p><a target="_blank" href="http://www.kleor-editions.com/contact-manager/"><?php echo $links['']; ?></a>
 | <a style="color: #808080;" href="#screen-options-wrap" onclick="document.getElementById('show-settings-link').click(); document.getElementById('contact-manager-hide').click();"><?php echo $links['#screen-options-wrap']; ?></a></p>
<ul>
<?php foreach (array('', '#screen-options-wrap') as $url) { unset($links[$url]); }
foreach ($links as $url => $text) {
echo '<li><a target="_blank" href="http://www.kleor-editions.com/contact-manager/'.$url.'">'.$text.'</a></li>'; } ?>
</ul>
<?php }

add_action('add_meta_boxes', create_function('', 'foreach (array("page", "post") as $type) {
add_meta_box("contact-manager", "Contact Manager", "contact_manager_meta_box", $type, "side"); }'));


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
include CONTACT_MANAGER_PATH.'/initial-options.php';
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
update_option(substr('contact_manager'.$_key, 0, 64), $value); } }


function uninstall_contact_manager() {
global $wpdb;
include CONTACT_MANAGER_PATH.'/tables.php';
foreach ($tables as $table_slug => $table) {
$results = $wpdb->query("DROP TABLE ".$wpdb->prefix.'contact_manager_'.$table_slug); }
include CONTACT_MANAGER_PATH.'/initial-options.php';
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
delete_option(substr('contact_manager'.$_key, 0, 64)); } }