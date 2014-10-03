<?php global $wpdb;
include_once ABSPATH.'wp-admin/includes/upgrade.php';
$charset_collate = '';
if (!empty($wpdb->charset)) { $charset_collate .= 'DEFAULT CHARACTER SET '.$wpdb->charset; }
if (!empty($wpdb->collate)) { $charset_collate .= ' COLLATE '.$wpdb->collate; }
include CONTACT_MANAGER_PATH.'tables.php';
foreach ($tables as $table_slug => $table) {
$list = ''; foreach ($table as $key => $value) { $list .= "
".$key." ".$value['type']." ".($key == "id" ? "auto_increment" : "NOT NULL").","; }
$sql = "CREATE TABLE ".$wpdb->prefix."contact_manager_".$table_slug." (".$list."
PRIMARY KEY  (id)) $charset_collate;"; dbDelta($sql); }

load_plugin_textdomain('contact-manager', false, CONTACT_MANAGER_FOLDER.'/languages');
include CONTACT_MANAGER_PATH.'initial-options.php';
$overwrited_options = array('menu_title_'.$lang, 'meta_box_'.$lang, 'pages_titles_'.$lang, 'version');
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
if (is_array($value)) {
$options = (array) get_option('contact_manager'.$_key);
$current_options = $options;
if ((isset($options[0])) && ($options[0] === false)) { unset($options[0]); }
foreach ($value as $option => $initial_value) {
if ((!isset($options[$option])) || ($options[$option] == '') || (in_array($option, $overwrited_options))) { $options[$option] = $initial_value; } }
if ($options != $current_options) { update_option('contact_manager'.$_key, $options); } }
else { add_option(substr('contact_manager'.$_key, 0, 64), $value); } }

date_default_timezone_set('UTC');
$current_time = time();
$cron = (array) get_option('contact_manager_cron');
if (($context == 'activation') || (!isset($cron['previous_activation'])) || ($cron['previous_activation']['version'] == '')) {
$cron['previous_activation'] = array('version' => CONTACT_MANAGER_VERSION, 'timestamp' => $current_time); }
if ((!isset($cron['first_installation'])) || ($cron['first_installation']['version'] == '')) {
$cron['first_installation'] = array('version' => CONTACT_MANAGER_VERSION, 'timestamp' => $current_time); }
if ((!isset($cron['previous_installation'])) || ($cron['previous_installation']['version'] != CONTACT_MANAGER_VERSION)) {
$cron['previous_installation'] = array('version' => CONTACT_MANAGER_VERSION, 'number' => 1); }
else { $cron['previous_installation']['number'] = $cron['previous_installation']['number'] + 1; }
$cron['previous_installation']['timestamp'] = $current_time;
update_option('contact_manager_cron', $cron);