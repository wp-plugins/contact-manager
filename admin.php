<?php if (strstr($_SERVER['REQUEST_URI'], '/plugins.php')) { load_plugin_textdomain('contact-manager', false, 'contact-manager/languages'); }
if ((isset($_GET['page'])) && (strstr($_GET['page'], 'contact-manager'))) { include_once CONTACT_MANAGER_PATH.'/admin-pages-functions.php'; }


function contact_manager_admin_menu() {
$lang = strtolower(substr(WPLANG, 0, 2)); if ($lang == '') { $lang = 'en'; }
include CONTACT_MANAGER_PATH.'/admin-pages.php';
$options = (array) get_option('contact_manager_back_office');
if ((!isset($options['menu_title_'.$lang])) || ($options['menu_title_'.$lang] == '') || (!isset($options['pages_titles_'.$lang]))
 || ($options['pages_titles_'.$lang] == '')) { install_contact_manager(); $options = (array) get_option('contact_manager_back_office'); }
$menu_title = $options['menu_title_'.$lang]; $pages_titles = (array) $options['pages_titles_'.$lang];
if (((isset($_GET['page'])) && (strstr($_GET['page'], 'contact-manager'))) || ($menu_title == '')) { $menu_title = __('Contact', 'contact-manager'); }
if ((defined('CONTACT_MANAGER_DEMO')) && (CONTACT_MANAGER_DEMO == true)) { $capability = 'manage_options'; }
else { $role = $options['minimum_roles']['view']; $capability = $roles[$role]['capability']; }
if ($options['custom_icon_used'] == 'yes') { $icon_url = format_url($options['custom_icon_url']); } else { $icon_url = ''; }
add_menu_page('Contact Manager', $menu_title, $capability, 'contact-manager', create_function('', 'include_once CONTACT_MANAGER_PATH."/options-page.php";'), $icon_url);
$admin_menu_pages = contact_manager_admin_menu_pages();
foreach ($admin_pages as $key => $value) { if (in_array($key, $admin_menu_pages)) {
$slug = 'contact-manager'.($key == '' ? '' : '-'.str_replace('_', '-', $key));
if ((!isset($_GET['page'])) || (!strstr($_GET['page'], 'contact-manager'))) { $value['menu_title'] = $pages_titles[$key]; }
add_submenu_page('contact-manager', $value['page_title'], $value['menu_title'], $capability, $slug, create_function('', 'include_once CONTACT_MANAGER_PATH."/'.$value['file'].'";')); } } }

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
$lang = strtolower(substr(WPLANG, 0, 2)); if ($lang == '') { $lang = 'en'; }
$options = (array) get_option('contact_manager_back_office');
if ((!isset($options['meta_box_'.$lang])) || ($options['meta_box_'.$lang] == '')) { install_contact_manager(); $options = (array) get_option('contact_manager_back_office'); }
$links = (array) $options['meta_box_'.$lang];
if ((isset($links[''])) && (isset($links['#screen-options-wrap']))) { ?>
<p><a target="_blank" href="http://www.kleor.com/contact-manager/"><?php echo $links['']; ?></a>
 | <a style="color: #808080;" href="#screen-options-wrap" onclick="document.getElementById('show-settings-link').click(); document.getElementById('contact-manager-hide').click();"><?php echo $links['#screen-options-wrap']; ?></a></p>
<ul>
<?php foreach (array('', '#screen-options-wrap') as $url) { unset($links[$url]); }
foreach ($links as $url => $text) {
echo '<li><a target="_blank" href="http://www.kleor.com/contact-manager/'.$url.'">'.$text.'</a></li>'; } ?>
</ul>
<?php } }

add_action('add_meta_boxes', create_function('', 'if (contact_manager_user_can(get_option("contact_manager_back_office"), "view")) {
foreach (array("page", "post") as $type) { add_meta_box("contact-manager", "Contact Manager", "contact_manager_meta_box", $type, "side"); } }'));


function contact_manager_user_can($back_office_options, $capability) {
if ((defined('CONTACT_MANAGER_DEMO')) && (CONTACT_MANAGER_DEMO == true)) { $capability = 'manage_options'; }
else { include CONTACT_MANAGER_PATH.'/admin-pages.php'; $role = $back_office_options['minimum_roles'][$capability]; $capability = $roles[$role]['capability']; }
return current_user_can($capability); }


function contact_manager_action_links($links) {
if (!is_network_admin()) {
$links = array_merge($links, array(
'<span class="delete"><a href="admin.php?page=contact-manager&amp;action=uninstall" title="'.__('Delete the options and tables of Contact Manager', 'contact-manager').'">'.__('Uninstall', 'contact-manager').'</a></span>',
'<span class="delete"><a href="admin.php?page=contact-manager&amp;action=reset" title="'.__('Reset the options of Contact Manager', 'contact-manager').'">'.__('Reset', 'contact-manager').'</a></span>',
'<a href="admin.php?page=contact-manager">'.__('Options', 'contact-manager').'</a>')); }
else {
$links = array_merge($links, array(
'<span class="delete"><a href="../admin.php?page=contact-manager&amp;action=uninstall&amp;for=network" title="'.__('Delete the options and tables of Contact Manager for all sites in this network', 'contact-manager').'">'.__('Uninstall', 'contact-manager').'</a></span>')); }
return $links; }

foreach (array('', 'network_admin_') as $prefix) { add_filter($prefix.'plugin_action_links_contact-manager/contact-manager.php', 'contact_manager_action_links', 10, 2); }


function contact_manager_row_meta($links, $file) {
if ($file == 'contact-manager/contact-manager.php') {
$links = array_merge($links, array(
'<a href="http://www.kleor.com/contact-manager">'.__('Documentation', 'contact-manager').'</a>')); }
return $links; }

add_filter('plugin_row_meta', 'contact_manager_row_meta', 10, 2);


function reset_contact_manager() {
load_plugin_textdomain('contact-manager', false, 'contact-manager/languages');
include CONTACT_MANAGER_PATH.'/initial-options.php';
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
update_option(substr('contact_manager'.$_key, 0, 64), $value); } }


function uninstall_contact_manager($for = 'single') { include CONTACT_MANAGER_PATH.'/includes/uninstall.php'; }