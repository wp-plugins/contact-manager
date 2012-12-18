<?php load_plugin_textdomain('contact-manager', false, 'contact-manager/languages');
add_action('admin_enqueue_scripts', create_function('', 'wp_enqueue_script("dashboard");'));


function contact_manager_pages_links($back_office_options) {
$links = (array) $back_office_options['links'];
$displayed_links = (array) $back_office_options['displayed_links'];
if (($back_office_options['links_displayed'] == 'yes') && (count($displayed_links) > 0)) {
include 'admin-pages.php';
if ($back_office_options['title_displayed'] == 'yes') { $left_margin = '6em'; } else { $left_margin = '0'; }
echo '<ul class="subsubsub" style="margin: 2em 0 1.5em '.$left_margin.'; float: left; white-space: normal;">';
$links_markup = array(
'Documentation' => '<a href="http://www.kleor-editions.com/contact-manager">'.$admin_links['Documentation']['name'].'</a>',
'Commerce Manager' => (function_exists('commerce_manager_admin_menu') ? '<a href="admin.php?page=commerce-manager'.
($_GET['page'] == 'contact-manager-form' ? '-product' : '').
($_GET['page'] == 'contact-manager-form-category' ? '-product-category' : '').
($_GET['page'] == 'contact-manager-forms' ? '-products' : '').
($_GET['page'] == 'contact-manager-forms-categories' ? '-products-categories' : '').
(strstr($_GET['page'], 'back-office') ? '-back-office' : '').
(strstr($_GET['page'], 'statistics') ? '-statistics' : '').'">'.$admin_links['Commerce Manager']['name'].'</a>' : '<a href="http://www.kleor-editions.com/commerce-manager">'.$admin_links['Commerce Manager']['name'].'</a>'),
'Affiliation Manager' => (function_exists('affiliation_manager_admin_menu') ? '<a href="admin.php?page=affiliation-manager'.
($_GET['page'] == 'contact-manager-form' ? '-affiliate' : '').
($_GET['page'] == 'contact-manager-form-category' ? '-affiliate-category' : '').
($_GET['page'] == 'contact-manager-forms' ? '-affiliates' : '').
($_GET['page'] == 'contact-manager-forms-categories' ? '-affiliates-categories' : '').
(strstr($_GET['page'], 'back-office') ? '-back-office' : '').
(strstr($_GET['page'], 'messages') ? '-messages-commissions' : '').
(strstr($_GET['page'], 'statistics') ? '-statistics' : '').'">'.$admin_links['Affiliation Manager']['name'].'</a>' : '<a href="http://www.kleor-editions.com/affiliation-manager">'.$admin_links['Affiliation Manager']['name'].'</a>'),
'Membership Manager' => (function_exists('membership_manager_admin_menu') ? '<a href="admin.php?page=membership-manager'.
($_GET['page'] == 'contact-manager-form' ? '-member-area' : '').
($_GET['page'] == 'contact-manager-form-category' ? '-member-area-category' : '').
($_GET['page'] == 'contact-manager-forms' ? '-members-areas' : '').
($_GET['page'] == 'contact-manager-forms-categories' ? '-members-areas-categories' : '').
(strstr($_GET['page'], 'back-office') ? '-back-office' : '').
(strstr($_GET['page'], 'statistics') ? '-statistics' : '').'">'.$admin_links['Membership Manager']['name'].'</a>' : '<a href="http://www.kleor-editions.com/membership-manager">'.$admin_links['Membership Manager']['name'].'</a>'),
'Optin Manager' => (function_exists('optin_manager_admin_menu') ? '<a href="admin.php?page=optin-manager'.
($_GET['page'] == 'contact-manager-form' ? '-form' : '').
($_GET['page'] == 'contact-manager-form-category' ? '-form-category' : '').
($_GET['page'] == 'contact-manager-forms' ? '-forms' : '').
($_GET['page'] == 'contact-manager-forms-categories' ? '-forms-categories' : '').
($_GET['page'] == 'contact-manager-message' ? '-prospect' : '').
($_GET['page'] == 'contact-manager-messages' ? '-prospects' : '').
(strstr($_GET['page'], 'back-office') ? '-back-office' : '').
(strstr($_GET['page'], 'statistics') ? '-statistics' : '').'">'.$admin_links['Optin Manager']['name'].'</a>' : '<a href="http://www.kleor-editions.com/optin-manager">'.$admin_links['Optin Manager']['name'].'</a>'));
$first = true; $links_displayed = array();
for ($i = 0; $i < count($admin_links); $i++) {
$link = (isset($links[$i]) ? $links[$i] : '');
if ((in_array($i, $displayed_links)) && (isset($links_markup[$link])) && (!in_array($link, $links_displayed))) {
echo '<li>'.($first ? '' : '&nbsp;| ').$links_markup[$link].'</li>'; $first = false; $links_displayed[] = $link; } }
echo '</ul>'; } }


function contact_manager_pages_menu($back_office_options) {
$menu_items = (array) $back_office_options['menu_items'];
$menu_displayed_items = (array) $back_office_options['menu_displayed_items'];
if (($back_office_options['menu_displayed'] == 'yes') && (count($menu_displayed_items) > 0)) {
include 'admin-pages.php';
echo '<ul class="subsubsub" style="margin: 0 0 1em; float: left; white-space: normal;">';
$first = true; $items_displayed = array();
for ($i = 0; $i < count($admin_pages); $i++) {
$item = (isset($menu_items[$i]) ? $menu_items[$i] : '');
if ((in_array($i, $menu_displayed_items)) && (!in_array($item, $items_displayed))) {
$slug = 'contact-manager'.($item == '' ? '' : '-'.str_replace('_', '-', $item));
echo '<li>'.($first ? '' : '&nbsp;| ').'<a href="admin.php?page='.$slug.'"'.($_GET['page'] == $slug ? ' class="current"' : '').'>'.$admin_pages[$item]['menu_title'].'</a></li>';
$first = false; $items_displayed[] = $item; } }
echo '</ul>'; } }


function contact_manager_pages_module($back_office_options, $module, $undisplayed_modules) {
include 'admin-pages.php';
$page_slug = str_replace('-', '_', str_replace('-page', '', $module));
$page_undisplayed_modules = (array) $back_office_options[$page_slug.'_page_undisplayed_modules']; ?>
<div class="postbox" id="<?php echo $module.'-module'; ?>"<?php if (in_array($module, $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="<?php echo $module; ?>"><strong><?php echo $modules['back_office'][$module]['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="<?php echo $page_slug; ?>_page_summary_displayed" id="<?php echo $page_slug; ?>_page_summary_displayed" value="yes"<?php if ($back_office_options[$page_slug.'_page_summary_displayed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Display the summary', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Modules displayed', 'contact-manager'); ?></strong></th>
<td><?php foreach ($modules[$page_slug] as $key => $value) {
$name = $page_slug.'_page_'.str_replace('-', '_', $key).'_module_displayed';
if (strstr($_GET['page'], 'back-office')) { $onmouseover = ""; }
else { $onmouseover = " onmouseover=\"document.getElementById('".$key."-submodules').style.display = 'block';\""; }
if ((isset($value['required'])) && ($value['required'] == 'yes')) { echo '<label'.$onmouseover.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes" checked="checked" disabled="disabled" /> '.$value['name'].'<br /></label>'; }
else {
if ((($page_slug != 'back_office') && (strstr($_GET['page'], 'back-office'))) || ($key == $module)) { $onclick = ""; }
else { $onclick = " onclick=\"if (this.checked == true) { document.getElementById('".$key."-module').style.display = 'block'; } else { document.getElementById('".$key."-module').style.display = 'none'; } window.location = '#".$module."-module';\""; }
echo '<label'.$onmouseover.(((!isset($_GET['id'])) || ($page_slug != 'message') || (!in_array($key, $add_message_modules))) ? '' : ' style="display: none;"').'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes"'.(in_array($key, $page_undisplayed_modules) ? '' : ' checked="checked"').$onclick.' /> '.$value['name'].'<br /></label>'; }
if (!strstr($_GET['page'], 'back-office')) { echo '<div style="display: none;" id="'.$key.'-submodules">'; }
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) {
$module_name = $page_slug.'_page_'.str_replace('-', '_', $module_key).'_module_displayed';
if ((isset($module_value['required'])) && ($module_value['required'] == 'yes')) { echo '<label><input style="margin-left: 2em;" type="checkbox" name="'.$module_name.'" id="'.$module_name.'" value="yes" checked="checked" disabled="disabled" /> '.$module_value['name'].'<br /></label>'; }
else {
if (($page_slug != 'back_office') && (strstr($_GET['page'], 'back-office'))) { $module_onclick = ""; }
else { $module_onclick = " onclick=\"if (this.checked == true) { document.getElementById('".$module_key."-module').style.display = 'block'; } else { document.getElementById('".$module_key."-module').style.display = 'none'; } window.location = '#".$module."-module';\""; }
echo '<label><input style="margin-left: 2em;" type="checkbox" name="'.$module_name.'" id="'.$module_name.'" value="yes"'.(in_array($module_key, $page_undisplayed_modules) ? '' : ' checked="checked"').$module_onclick.' /> '.$module_value['name'].'<br /></label>'; } } }
if (!strstr($_GET['page'], 'back-office')) { echo '</div>'; } } ?></td></tr>
<?php if ((strstr($_GET['page'], 'back-office')) && (isset($modules['back_office'][$module]['modules'][$module.'-custom-fields']))) { ?>
</tbody></table>
<div id="<?php echo $module; ?>-custom-fields-module"<?php if (in_array($module.'-custom-fields', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="<?php echo $module; ?>-custom-fields"><strong><?php echo $modules['back_office'][$module]['modules'][$module.'-custom-fields']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You can create custom fields to record additional datas.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/contact-manager/#custom-fields"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
</tbody></table>
<table class="form-table" style="margin-left: 8%"><tbody>
<?php $custom_fields = (array) $back_office_options[$page_slug.'_page_custom_fields'];
asort($custom_fields); $i = 0; foreach ($custom_fields as $key => $value) {
$i = $i + 1; echo '<tr style="vertical-align: top;"><th scope="row" style="width: 4%;"><strong><label for="'.$page_slug.'_page_custom_field_name'.$i.'">'.__('Name', 'contact-manager').'</label></strong></th>
<td style="width: 30%;"><textarea style="padding: 0 0.25em; height: 1.75em; width: 90%;" name="'.$page_slug.'_page_custom_field_name'.$i.'" id="'.$page_slug.'_page_custom_field_name'.$i.'" rows="1" cols="50">'.htmlspecialchars($value).'</textarea></td>
<th scope="row" style="width: 4%;"><strong><label for="'.$page_slug.'_page_custom_field_key'.$i.'">'.__('Key', 'contact-manager').'</label></strong></th>
<td style="width: 45%;"><textarea style="padding: 0 0.25em; height: 1.75em; width: 60%;" name="'.$page_slug.'_page_custom_field_key'.$i.'" id="'.$page_slug.'_page_custom_field_key'.$i.'" rows="1" cols="50">'.str_replace('_', '-', $key).'</textarea><br />
<span class="description">'.__('Letters, numbers and hyphens only', 'contact-manager').'</span></td></tr>'; }
$j = 0; while (($j == 0) || ($i < 5)) {
$i = $i + 1; $j = $j + 1; echo '<tr style="vertical-align: top;"><th scope="row" style="width: 4%;"><strong><label for="'.$page_slug.'_page_custom_field_name'.$i.'">'.__('Name', 'contact-manager').'</label></strong></th>
<td style="width: 30%;"><textarea style="padding: 0 0.25em; height: 1.75em; width: 90%;" name="'.$page_slug.'_page_custom_field_name'.$i.'" id="'.$page_slug.'_page_custom_field_name'.$i.'" rows="1" cols="50"></textarea></td>
<th scope="row" style="width: 4%;"><strong><label for="'.$page_slug.'_page_custom_field_key'.$i.'">'.__('Key', 'contact-manager').'</label></strong></th>
<td style="width: 45%;"><textarea style="padding: 0 0.25em; height: 1.75em; width: 60%;" name="'.$page_slug.'_page_custom_field_key'.$i.'" id="'.$page_slug.'_page_custom_field_key'.$i.'" rows="1" cols="50"></textarea><br />
<span class="description">'.__('Letters, numbers and hyphens only', 'contact-manager').'</span></td></tr>'; } ?>
</tbody></table>
</div>
<table class="form-table"><tbody><?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php }


function contact_manager_pages_search_field($type, $searchby, $searchby_options) { ?>
<p class="search-box" style="float: right;"><label><?php _e(ucfirst($type).' by', 'contact-manager'); ?> <select name="<?php echo $type; ?>by" id="<?php echo $type; ?>by">
<?php if ($type == 'search') { echo '<option value=""'.($searchby == '' ? ' selected="selected"' : '').'>'.__('all fields', 'contact-manager').'</option>'; } ?>
<?php foreach ($searchby_options as $key => $value) {
echo '<option value="'.$key.'"'.($searchby == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select></label><br />
<input type="text" name="s" id="s" size="40" value="<?php if (isset($_GET['s'])) { echo $_GET['s']; } ?>" />
<input type="submit" class="button" name="submit" id="<?php echo $type; ?>-submit" value="<?php _e(ucfirst($type), 'contact-manager'); ?>" /></p>
<?php }


function contact_manager_pages_summary($back_office_options) {
if ($_GET['page'] == 'contact-manager') { $page_slug = 'options'; }
else { $page_slug = str_replace('-', '_', str_replace('contact-manager-', '', $_GET['page'])); }
if ($back_office_options[$page_slug.'_page_summary_displayed'] == 'yes') {
include 'admin-pages.php';
$modules = $modules[$page_slug];
$undisplayed_modules = (array) $back_office_options[$page_slug.'_page_undisplayed_modules'];
$list = ''; foreach ($modules as $key => $value) {
if ((!isset($_GET['id'])) || ($page_slug != 'message') || (!in_array($key, $add_message_modules))) {
if (!in_array($key, $undisplayed_modules)) { $list .= '<li>&nbsp;| <a href="#'.$key.'">'.$value['name'].'</a></li>'; } } }
if ($list != '') { echo '<ul class="subsubsub" style="float: none; white-space: normal;"><li>'.substr($list, 12).'</ul>'; } } }


function contact_manager_pages_title($back_office_options) {
if ($back_office_options['title_displayed'] == 'yes') {
echo '<h2 style="float: left;">'.$back_office_options['title'].'</h2>'; } }


function contact_manager_pages_top($back_office_options) {
contact_manager_pages_title($back_office_options);
contact_manager_pages_links($back_office_options);
echo '<div class="clear"></div>'; }


function contact_manager_user_can($back_office_options, $capability) {
if ((defined('CONTACT_MANAGER_DEMO')) && (CONTACT_MANAGER_DEMO == true)) { $capability = 'manage_options'; }
else { include 'admin-pages.php'; $role = $back_office_options['minimum_roles'][$capability]; $capability = $roles[$role]['capability']; }
return current_user_can($capability); }


function contact_manager_users_roles() {
$wp_roles = new WP_Roles();
$roles = $wp_roles->get_names();
foreach ($roles as $role => $name) { $roles[$role] = translate_user_role($name); }
return $roles; }


function update_contact_manager_back_office($back_office_options, $page) {
include 'admin-pages.php';
if ((!isset($_POST[$page.'_page_summary_displayed'])) || ($_POST[$page.'_page_summary_displayed'] != 'yes')) { $_POST[$page.'_page_summary_displayed'] = 'no'; }
$_POST[$page.'_page_undisplayed_modules'] = array();
foreach ($modules[$page] as $key => $value) {
if (((!isset($_POST[$page.'_page_'.str_replace('-', '_', $key).'_module_displayed'])) || ($_POST[$page.'_page_'.str_replace('-', '_', $key).'_module_displayed'] != 'yes'))
 && ((!isset($value['required'])) || ($value['required'] != 'yes'))) { $_POST[$page.'_page_undisplayed_modules'][] = $key; }
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) {
if (((!isset($_POST[$page.'_page_'.str_replace('-', '_', $module_key).'_module_displayed'])) || ($_POST[$page.'_page_'.str_replace('-', '_', $module_key).'_module_displayed'] != 'yes'))
 && ((!isset($module_value['required'])) || ($module_value['required'] != 'yes'))) { $_POST[$page.'_page_undisplayed_modules'][] = $module_key; } } } }
if ((strstr($_GET['page'], 'back-office')) && (isset($back_office_options[$page.'_page_custom_fields']))) {
$custom_fields = (array) $back_office_options[$page.'_page_custom_fields'];
$_POST[$page.'_page_custom_fields'] = array();
for ($i = 1; $i <= max(5, count($custom_fields) + 1); $i++) {
if ((isset($_POST[$page.'_page_custom_field_name'.$i])) && ($_POST[$page.'_page_custom_field_name'.$i] != '')) {
if ((!isset($_POST[$page.'_page_custom_field_key'.$i])) || ($_POST[$page.'_page_custom_field_key'.$i] == '')) {
$_POST[$page.'_page_custom_field_key'.$i] = $_POST[$page.'_page_custom_field_name'.$i]; }
$_POST[$page.'_page_custom_field_key'.$i] = str_replace('-', '_', format_nice_name($_POST[$page.'_page_custom_field_key'.$i]));
if ($_POST[$page.'_page_custom_field_key'.$i] != '') {
$_POST[$page.'_page_custom_fields'][$_POST[$page.'_page_custom_field_key'.$i]] = $_POST[$page.'_page_custom_field_name'.$i]; } } } }
if (!strstr($_GET['page'], 'back-office')) {
foreach (array('custom_fields', 'summary_displayed', 'undisplayed_modules') as $option) {
if (isset($_POST[$page.'_page_'.$option])) { $back_office_options[$page.'_page_'.$option] = $_POST[$page.'_page_'.$option]; } }
update_option('contact_manager_back_office', $back_office_options);
return $back_office_options; } }


function contact_manager_pages_date_picker($start_date, $end_date) {
echo '<p style="margin: 0 0 1em 0; float: left;"><label><strong>'.__('Start', 'contact-manager').'</strong>
<input class="date-pick" style="margin: 0.5em;" type="text" name="start_date" id="start_date" size="20" value="'.$start_date.'" /></label>
<label style="margin-left: 3em;"><strong>'.__('End', 'contact-manager').'</strong>
<input class="date-pick" style="margin: 0.5em;" type="text" name="end_date" id="end_date" size="20" value="'.$end_date.'" /></label>
<input style="margin-left: 3em;" type="submit" class="button-secondary" name="submit" value="'.__('Display', 'contact-manager').'" /></p>
<div class="clear"></div>'; }


function contact_manager_date_picker_css() { ?>
<link rel="stylesheet" type="text/css" media="screen" href="<?php echo CONTACT_MANAGER_URL; ?>libraries/date-picker.css" />
<?php }


function contact_manager_date_picker_js() { ?>
<script type="text/javascript" src="<?php echo CONTACT_MANAGER_URL; ?>libraries/date-picker.js"></script>
<script type="text/javascript">
Date.dayNames = ['<?php _e('Sunday', 'contact-manager'); ?>', '<?php _e('Monday', 'contact-manager'); ?>', '<?php _e('Tuesday', 'contact-manager'); ?>', '<?php _e('Wednesday', 'contact-manager'); ?>', '<?php _e('Thursday', 'contact-manager'); ?>', '<?php _e('Friday', 'contact-manager'); ?>', '<?php _e('Saturday', 'contact-manager'); ?>'];
Date.abbrDayNames = ['<?php _e('Sun', 'contact-manager'); ?>', '<?php _e('Mon', 'contact-manager'); ?>', '<?php _e('Tue', 'contact-manager'); ?>', '<?php _e('Wed', 'contact-manager'); ?>', '<?php _e('Thu', 'contact-manager'); ?>', '<?php _e('Fri', 'contact-manager'); ?>', '<?php _e('Sat', 'contact-manager'); ?>'];
Date.monthNames = ['<?php _e('January', 'contact-manager'); ?>', '<?php _e('February', 'contact-manager'); ?>', '<?php _e('March', 'contact-manager'); ?>', '<?php _e('April', 'contact-manager'); ?>', '<?php _e('May', 'contact-manager'); ?>', '<?php _e('June', 'contact-manager'); ?>', '<?php _e('July', 'contact-manager'); ?>', '<?php _e('August', 'contact-manager'); ?>', '<?php _e('September', 'contact-manager'); ?>', '<?php _e('October', 'contact-manager'); ?>', '<?php _e('November', 'contact-manager'); ?>', '<?php _e('December', 'contact-manager'); ?>'];
Date.abbrMonthNames = ['<?php _e('Jan', 'contact-manager'); ?>', '<?php _e('Feb', 'contact-manager'); ?>', '<?php _e('Mar', 'contact-manager'); ?>', '<?php _e('Apr', 'contact-manager'); ?>', '<?php _e('May', 'contact-manager'); ?>', '<?php _e('Jun', 'contact-manager'); ?>', '<?php _e('Jul', 'contact-manager'); ?>', '<?php _e('Aug', 'contact-manager'); ?>', '<?php _e('Sep', 'contact-manager'); ?>', '<?php _e('Oct', 'contact-manager'); ?>', '<?php _e('Nov', 'contact-manager'); ?>', '<?php _e('Dec', 'contact-manager'); ?>'];
$.dpText = {
TEXT_PREV_YEAR : '<?php _e('Previous year', 'contact-manager'); ?>',
TEXT_PREV_MONTH : '<?php _e('Previous month', 'contact-manager'); ?>',
TEXT_NEXT_YEAR : '<?php _e('Next year', 'contact-manager'); ?>',
TEXT_NEXT_MONTH : '<?php _e('Next month', 'contact-manager'); ?>',
TEXT_CLOSE : '<?php _e('Close', 'contact-manager'); ?>',
TEXT_CHOOSE_DATE : '<?php _e('Choose a date', 'contact-manager'); ?>',
DATE_PICKER_ALT : '<?php _e('Date', 'contact-manager'); ?>',
DATE_PICKER_URL : '<?php echo CONTACT_MANAGER_URL; ?>images/date-picker.png',
HEADER_FORMAT : 'mmmm yyyy'
}; $(function(){ $('.date-pick').datePicker({startDate:'2011-01-01'}); });
</script>
<?php }


function contact_manager_format_members_areas_modifications($content) {
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$array = explode(',', $content);
$modifications = array();
foreach ($array as $string) {
$string = explode('(', trim(str_replace(')', '', $string)));
if (count($string) == 2) {
$id = (int) preg_replace('/[^0-9]/', '', $string[0]);
$sign = (substr($string[0], 0, 1) == '-' ? '-' : '+');
$d = preg_split('#[^0-9]#', trim($string[1]), 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : 0); }
if ((strstr($string[1], '/')) || (strstr($string[1], '-'))) {
$date = date('Y-m-d H:i:s', mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0])); }
else { $date = $d[0].':'.$d[1].':'.$d[2].':'.$d[3]; }
$modifications[$id] = array('sign' => $sign, 'date' => $date); } }
$members_areas = array();
foreach ($modifications as $key => $value) { $members_areas[] = $key; }
sort($members_areas, SORT_NUMERIC);
$content = '';
foreach ($members_areas as $key) { $content .= $modifications[$key]['sign'].$key." (".$modifications[$key]['date']."),\n" ; }
return $content; }


if (((!isset($_GET['action'])) || ($_GET['action'] != 'delete'))
 && ($_GET['page'] != 'contact-manager')
 && ($_GET['page'] != 'contact-manager-back-office')) {
add_action('admin_head', 'contact_manager_date_picker_css');
add_action('admin_footer', 'contact_jquery_js');
add_action('admin_footer', 'contact_manager_date_picker_js'); }