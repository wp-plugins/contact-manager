<?php global $wpdb;
include_once ABSPATH.'wp-admin/includes/upgrade.php';
$charset_collate = '';
if (!empty($wpdb->charset)) { $charset_collate .= 'DEFAULT CHARACTER SET '.$wpdb->charset; }
if (!empty($wpdb->collate)) { $charset_collate .= ' COLLATE '.$wpdb->collate; }
include CONTACT_MANAGER_PATH.'/tables.php';
foreach ($tables as $table_slug => $table) {
$list = ''; foreach ($table as $key => $value) { $list .= "
".$key." ".$value['type']." ".($key == "id" ? "auto_increment" : "NOT NULL").","; }
$sql = "CREATE TABLE ".$wpdb->prefix."contact_manager_".$table_slug." (".$list."
PRIMARY KEY  (id)) $charset_collate;"; dbDelta($sql);
foreach ($table as $key => $value) {
if (isset($value['constraint'])) {
$results = $wpdb->query("ALTER TABLE ".$wpdb->prefix."contact_manager_".$table_slug." ADD ".$value['constraint']." (".$key.")"); }
if (isset($value['default'])) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_".$table_slug." SET ".$key." = '".$value['default']."' WHERE ".$key." = ''"); } } }

load_plugin_textdomain('contact-manager', false, 'contact-manager/languages');
include CONTACT_MANAGER_PATH.'/initial-options.php';
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
if (is_array($value)) {
$options = (array) get_option('contact_manager'.$_key);
$current_options = $options;
if ((isset($options[0])) && ($options[0] === false)) { unset($options[0]); }
foreach ($value as $option => $initial_value) {
if (($option == 'menu_title_'.$lang) || ($option == 'meta_box_'.$lang) || ($option == 'pages_titles_'.$lang) || ($option == 'version')
 || (!isset($options[$option])) || ($options[$option] == '')) { $options[$option] = $initial_value; } }
if ($options != $current_options) { update_option('contact_manager'.$_key, $options); } }
else { add_option(substr('contact_manager'.$_key, 0, 64), $value); } }

if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$cron = (array) get_option('contact_manager_cron');
if ((!isset($cron['previous_installation'])) || ($cron['previous_installation']['version'] != CONTACT_MANAGER_VERSION)) {
$cron['previous_installation'] = array('version' => CONTACT_MANAGER_VERSION, 'number' => 1); }
else { $cron['previous_installation']['number'] = $cron['previous_installation']['number'] + 1; }
$cron['previous_installation']['timestamp'] = time();
update_option('contact_manager_cron', $cron);