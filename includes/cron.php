<?php $cron = get_option('contact_manager_cron');
if ($cron) {
date_default_timezone_set('UTC');
$current_time = time();
$installation = (array) $cron['previous_installation'];
if ($installation['version'] != CONTACT_MANAGER_VERSION) {
$cron['previous_installation'] = array('version' => CONTACT_MANAGER_VERSION, 'number' => 0, 'timestamp' => $current_time); }
elseif (($installation['number'] < 12) && (($current_time - $installation['timestamp']) >= pow(2, $installation['number'] + 2))) {
$cron['previous_installation']['timestamp'] = $current_time; }
if ($cron['previous_installation'] != $installation) {
update_option('contact_manager_cron', $cron);
wp_remote_get(CONTACT_MANAGER_URL.'index.php?action=install&key='.md5(AUTH_KEY), array('timeout' => 10)); }
elseif (($current_time - $cron['previous_admin_notices_cron_timestamp']) > 43200) {
$cron['previous_admin_notices_cron_timestamp'] = $current_time;
update_option('contact_manager_cron', $cron);
$lang = strtolower(substr(get_locale(), 0, 2)); if ($lang == '') { $lang = 'en'; }
$body = wp_remote_retrieve_body(wp_remote_get('http://www.kleor.com/wp-content/plugins/installations-manager/admin-notices.php?url='
.urlencode(HOME_URL).'&name='.urlencode(get_option('blogname')).'&lang='.$lang.'&plugin=Contact%20Manager&version='.CONTACT_MANAGER_VERSION));
if (is_serialized($body)) {
$admin_notices = (array) get_option('contact_manager_admin_notices');
$new_admin_notices = (array) unserialize($body);
foreach ($new_admin_notices as $key => $notice) {
if ((isset($notice['message'])) && ((!isset($admin_notices[$key])) || (!isset($admin_notices[$key]['version']))
 || ((isset($notice['version'])) && (version_compare($admin_notices[$key]['version'], $notice['version'], '<'))))) {
foreach (array('start', 'end') as $string) { if (isset($notice[$string.'_timestamp'])) {
if ((is_string($notice[$string.'_timestamp'])) && (substr($notice[$string.'_timestamp'], 0, 1) == '+')) {
$notice[$string.'_timestamp'] = $cron['first_installation']['timestamp'] + intval($notice[$string.'_timestamp']); }
$notice[$string.'_timestamp'] = (int) $notice[$string.'_timestamp']; } }
if ((!isset($notice['end_timestamp'])) || ($current_time < $notice['end_timestamp'])) { $admin_notices[$key] = $notice; } } }
update_option('contact_manager_admin_notices', $admin_notices); } } }
else { wp_remote_get(CONTACT_MANAGER_URL.'index.php?action=install&key='.md5(AUTH_KEY), array('timeout' => 10)); }