<?php if ((isset($_GET['action'])) || (isset($_GET['url']))) {
$file = 'wp-load.php'; $i = 0;
while ((!file_exists($file)) && ($i < 8)) { $file = '../'.$file; $i = $i + 1; }
include_once $file;
if (function_exists('contact_data')) {
if (isset($_GET['action'])) {
switch ($_GET['action']) {
case 'fill-form':
if (!headers_sent()) { header('Content-type: text/plain'); }
if ((isset($_GET['page'])) && (isset($_GET['key'])) && ($_GET['key'] == md5(AUTH_KEY))) {
foreach (array('admin.php', 'admin-pages-functions.php') as $file) { include_once CONTACT_MANAGER_PATH.$file; }
if (contact_manager_user_can((array) get_option('contact_manager_back_office'), 'view')) {
$GLOBALS['action'] = 'fill_admin_page_form';
function contact_fill_admin_page_form() {
global $wpdb; $error = '';
$back_office_options = (array) get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($back_office_options));
date_default_timezone_set('UTC');
$current_time = (isset($_GET['time']) ? $_GET['time'] : time());
$current_date = date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET);
$current_date_utc = date('Y-m-d H:i:s', $current_time);
$admin_page = ($_GET['page'] == 'contact-manager' ? 'options' : str_replace('-', '_', str_replace('contact-manager-', '', $_GET['page'])));
$is_category = (strstr($admin_page, 'category'));
foreach (array('default_options_select_fields', 'ids_fields', 'other_options') as $variable) {
$$variable = (array) (isset($_POST[$variable]) ? $_POST[$variable] : array()); }
foreach ($_POST as $key => $value) { if (is_string($value)) { $_POST[$key] = stripslashes($value); } }
$_POST['update_fields'] = 'yes'; if (isset($_POST['submit'])) { unset($_POST['submit']); }
foreach (array('admin-pages.php', 'tables.php') as $file) { include CONTACT_MANAGER_PATH.$file; }
include CONTACT_MANAGER_PATH.'includes/fill-form.php';
echo json_encode(array_map('strval', $_POST)); }
contact_fill_admin_page_form(); } } break;
case 'install': if ((isset($_GET['key'])) && ($_GET['key'] == md5(AUTH_KEY))) { install_contact_manager(); } break;
case 'update-options':
if ((isset($_GET['page'])) && (isset($_GET['key'])) && ($_GET['key'] == md5(AUTH_KEY))) {
foreach (array('admin.php', 'admin-pages-functions.php') as $file) { include_once CONTACT_MANAGER_PATH.$file; }
if (contact_manager_user_can((array) get_option('contact_manager_back_office'), 'manage')) {
$options = get_option(str_replace('-', '_', $_GET['page']));
if ($options) { $options = (array) $options;
foreach ($options as $key => $value) { if (isset($_GET[$key])) { $options[$key] = stripslashes($_GET[$key]); } }
update_option(str_replace('-', '_', $_GET['page']), $options); } } } break;
default: if (!headers_sent()) { header('Location: '.HOME_URL); exit(); } } }
elseif (isset($_GET['url'])) {
$url = contact_decrypt_url($_SERVER['REQUEST_URI']);
if (!headers_sent()) { header('Location: '.$url); exit(); } } }
elseif (!headers_sent()) { header('Location: /'); exit(); } }
elseif (!headers_sent()) { header('Location: /'); exit(); }