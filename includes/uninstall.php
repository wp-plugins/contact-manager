<?php global $wpdb;
if (($for != 'network') || (!is_multisite()) || (!current_user_can('manage_network'))) { $for = 'single'; }
deactivate_plugins('contact-manager/contact-manager.php');
if ($for == 'network') {
$blog_ids = (array) $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
$blog_prefixes = array();
$original_blog_id = get_current_blog_id();
foreach ($blog_ids as $blog_id) {
$blog_prefixes[] = $wpdb->base_prefix.($blog_id == BLOG_ID_CURRENT_SITE ? '' : $blog_id.'_');
switch_to_blog($blog_id);
$active_plugins = (array) get_option('active_plugins');
$new_active_plugins = array(); foreach ($active_plugins as $plugin) {
if ($plugin != 'contact-manager/contact-manager.php') { $new_active_plugins[] = $plugin; } }
update_option('active_plugins', $new_active_plugins); }
switch_to_blog($original_blog_id); }
else { $blog_prefixes = array($wpdb->prefix); }
include CONTACT_MANAGER_PATH.'/tables.php';
foreach ($blog_prefixes as $blog_prefix) {
foreach ($tables as $table_slug => $table) {
$results = $wpdb->query("DROP TABLE ".$blog_prefix.'contact_manager_'.$table_slug); } }
include CONTACT_MANAGER_PATH.'/initial-options.php';
if ($for == 'network') {
foreach ($blog_ids as $blog_id) {
switch_to_blog($blog_id);
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
delete_option(substr('contact_manager'.$_key, 0, 64)); } }
switch_to_blog($original_blog_id); }
else {
foreach ($initial_options as $key => $value) {
$_key = ($key == '' ? '' : '_'.$key);
delete_option(substr('contact_manager'.$_key, 0, 64)); } }