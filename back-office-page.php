<?php global $wpdb; $error = '';
$options = (array) get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($options));
$admin_page = 'back_office';
foreach (array('admin-pages.php', 'initial-options.php') as $file) { include CONTACT_MANAGER_PATH.$file; }
$max_links = count($admin_links);
$max_menu_items = count($admin_pages);

if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!contact_manager_user_can($options, 'manage')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes(html_entity_decode(str_replace(array('&nbsp;', '&#91;', '&#93;'), array(' ', '&amp;#91;', '&amp;#93;'), $value))); } }
foreach ($initial_options['back_office'] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
foreach (array(
'custom_icon_used',
'links_displayed',
'menu_displayed',
'title_displayed') as $field) { if ($_POST[$field] != 'yes') { $_POST[$field] = 'no'; } }
foreach (array(
'back_office',
'form',
'form_category',
'message',
'options') as $page) { update_contact_manager_back_office($options, $page); }
$_POST['minimum_roles'] = array();
foreach (array('manage', 'view') as $key) { $_POST['minimum_roles'][$key] = $_POST[$key.'_minimum_role']; }
if (isset($_POST['reset_links'])) {
foreach (array('links', 'displayed_links') as $field) { $_POST[$field] = $initial_options['back_office'][$field]; } }
else {
$_POST['displayed_links'] = array();
for ($i = 0; $i < $max_links; $i++) {
$_POST['links'][$i] = $_POST['link'.$i];
if (isset($_POST['link'.$i.'_displayed'])) { $_POST['displayed_links'][] = $i; } } }
if (isset($_POST['reset_menu_items'])) {
foreach (array('menu_items', 'menu_displayed_items') as $field) { $_POST[$field] = $initial_options['back_office'][$field]; } }
else {
$_POST['menu_displayed_items'] = array();
for ($i = 0; $i < $max_menu_items; $i++) {
$_POST['menu_items'][$i] = $_POST['menu_item'.$i];
if (isset($_POST['menu_item'.$i.'_displayed'])) { $_POST['menu_displayed_items'][] = $i; } } }
foreach (array('default_options', 'documentations', 'ids_fields', 'pages_modules', 'urls_fields') as $string) {
$_POST[$string.'_links_target'] = (isset($_POST[$string.'_links_targets_opened_in_new_tab']) ? '_blank' : '_self'); }
$_POST['statistics_page_undisplayed_columns'] = array();
foreach ($statistics_columns as $key => $value) {
if ((!isset($_POST['statistics_page_'.$key.'_column_displayed'])) && ((!isset($value['required'])) || ($value['required'] != 'yes'))) { $_POST['statistics_page_undisplayed_columns'][] = $key; } }
$_POST['statistics_page_undisplayed_rows'] = array();
foreach ($statistics_rows as $key => $value) {
if ((!isset($_POST['statistics_page_'.$key.'_row_displayed'])) && ((!isset($value['required'])) || ($value['required'] != 'yes'))) { $_POST['statistics_page_undisplayed_rows'][] = $key; } }
foreach ($initial_options['back_office'] as $key => $value) { if ($_POST[$key] == '') { $_POST[$key] = $value; } $options[$key] = $_POST[$key]; }
update_option('contact_manager_back_office', $options); } }

$undisplayed_modules = (array) $options['back_office_page_undisplayed_modules']; ?>

<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($options); ?>
<?php if (isset($_POST['submit'])) { echo '<div class="updated"><p><strong>'.__('Settings saved.', 'contact-manager').'</strong></p></div>'; } ?>
<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php contact_manager_pages_summary($options); ?>

<div class="postbox" id="capabilities-module"<?php if (in_array('capabilities', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="capabilities"><strong><?php echo $modules['back_office']['capabilities']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="view_minimum_role"><?php _e('Access', 'contact-manager'); ?></label></strong></th>
<td><select name="view_minimum_role" id="view_minimum_role">
<?php foreach ($roles as $key => $value) {
echo '<option value="'.$key.'"'.($options['minimum_roles']['view'] == $key ? ' selected="selected"' : '').'>'.$value['name'].'</option>'."\n"; } ?>
</select> <span class="description"><?php _e('Minimum role to access the interface of Contact Manager', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="manage_minimum_role"><?php _e('Management', 'contact-manager'); ?></label></strong></th>
<td><select name="manage_minimum_role" id="manage_minimum_role">
<?php foreach ($roles as $key => $value) {
echo '<option value="'.$key.'"'.($options['minimum_roles']['manage'] == $key ? ' selected="selected"' : '').'>'.$value['name'].'</option>'."\n"; } ?>
</select> <span class="description"><?php _e('Minimum role to change options and add, edit or delete items of Contact Manager', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="icon-module"<?php if (in_array('icon', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="icon"><strong><?php echo $modules['back_office']['icon']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="custom_icon_used" id="custom_icon_used" value="yes"<?php if ($options['custom_icon_used'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Use a custom icon', 'contact-manager'); ?></label>
<span class="description" style="vertical-align: -5%;"><?php _e('Icon displayed in the admin menu of WordPress', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="custom_icon_url"><?php _e('Icon URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="custom_icon_url" id="custom_icon_url" rows="1" cols="75" onchange="document.getElementById('custom-icon-url-link').href = format_url(this.value);"><?php echo $options['custom_icon_url']; ?></textarea> 
<span style="vertical-align: 25%;"><a id="custom-icon-url-link" target="<?php echo $options['urls_fields_links_target']; ?>" href="<?php echo htmlspecialchars(format_url(do_shortcode($options['custom_icon_url']))); ?>"><?php _e('Link', 'contact-manager'); ?></a>
<?php if (current_user_can('upload_files')) { echo ' | <a target="'.$options['urls_fields_links_target'].'" href="media-new.php" title="'.__('After the upload, you will just need to copy and paste the URL of the image in this field.', 'contact-manager').'">'.__('Upload an image', 'contact-manager').'</a>'; } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="top-module"<?php if (in_array('top', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="top"><strong><?php echo $modules['back_office']['top']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="title_displayed" id="title_displayed" value="yes"<?php if ($options['title_displayed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Display the title', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="title"><?php _e('Title', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="title" id="title" rows="1" cols="50"><?php echo $options['title']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="links_displayed" id="links_displayed" value="yes"<?php if ($options['links_displayed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Display the links', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Links', 'contact-manager'); ?></strong></th>
<td><input type="hidden" name="submit" value="true" /><input style="margin-bottom: 0.5em;" type="submit" class="button-secondary" name="reset_links" formaction="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>#top" value="<?php _e('Reset the links', 'contact-manager'); ?>" /><br />
<?php $displayed_links = (array) $options['displayed_links'];
for ($i = 0; $i < $max_links; $i++) {
echo '<label><span style="margin-right: 0.3em;">'.__('Link', 'contact-manager').' '.($i + 1).'</span> <select style="margin-right: 0.3em;" name="link'.$i.'" id="link'.$i.'">';
foreach ($admin_links as $key => $value) { echo '<option value="'.$key.'"'.($options['links'][$i] == $key ? ' selected="selected"' : '').'>'.$value['name'].'</option>'."\n"; }
echo '</select></label>
<label><input type="checkbox" name="link'.$i.'_displayed" id="link'.$i.'_displayed" value="yes"'.(!in_array($i, $displayed_links) ? '' : ' checked="checked"').' /> '.__('Display', 'contact-manager').'</label><br />'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="menu-module"<?php if (in_array('menu', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="menu"><strong><?php echo $modules['back_office']['menu']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="menu_displayed" id="menu_displayed" value="yes"<?php if ($options['menu_displayed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Display the menu', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Pages', 'contact-manager'); ?></strong></th>
<td><input type="hidden" name="submit" value="true" /><input style="margin-bottom: 0.5em;" type="submit" class="button-secondary" name="reset_menu_items" formaction="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>#menu" value="<?php _e('Reset the pages', 'contact-manager'); ?>" /><br />
<?php $menu_displayed_items = (array) $options['menu_displayed_items'];
for ($i = 0; $i < $max_menu_items; $i++) {
echo '<label><span style="margin-right: 0.3em;">'.__('Page', 'contact-manager').' '.($i + 1).'</span> <select style="margin-right: 0.3em;" name="menu_item'.$i.'" id="menu_item'.$i.'">';
foreach ($admin_pages as $key => $value) { echo '<option value="'.$key.'"'.($options['menu_items'][$i] == $key ? ' selected="selected"' : '').'>'.$value['menu_title'].'</option>'."\n"; }
echo '</select></label>
<label><input type="checkbox" name="menu_item'.$i.'_displayed" id="menu_item'.$i.'_displayed" value="yes"'.(!in_array($i, $menu_displayed_items) ? '' : ' checked="checked"').' /> '.__('Display', 'contact-manager').'</label><br />'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="links-module"<?php if (in_array('links', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="links"><strong><?php echo $modules['back_office']['links']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Open in a new tab the targets of the links', 'contact-manager'); ?></strong></th>
<td><?php foreach (array(
'documentations' => __('pointing to the documentation', 'contact-manager'),
'default_options' => __('allowing to configure the default options', 'contact-manager'),
'ids_fields' => __('below the fields allowing to enter an ID', 'contact-manager'),
'urls_fields' => __('next to the fields allowing to enter a URL', 'contact-manager'),
'pages_modules' => __('at the top of the modules of this page', 'contact-manager')) as $key => $value) {
$name = $key.'_links_targets_opened_in_new_tab';
echo '<label><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes"'.($options[$key.'_links_target'] != '_blank' ? '' : ' checked="checked"').' /> '.$value.'</label><br />'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php foreach (array(
'options-page',
'form-page',
'form-category-page',
'message-page') as $module) { contact_manager_pages_module($options, $module, $undisplayed_modules); } ?>

<div class="postbox" id="statistics-page-module"<?php if (in_array('statistics-page', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="statistics-page"><strong><?php echo $modules['back_office']['statistics-page']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a target="<?php echo $options['pages_modules_links_target']; ?>" href="admin.php?page=contact-manager-statistics"><?php _e('Click here to open this page.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Columns displayed', 'contact-manager'); ?></strong></th>
<td><?php foreach ($statistics_columns as $key => $value) {
$name = 'statistics_page_'.$key.'_column_displayed';
if ((!isset($value['title'])) || ($value['title'] == '')) {
if ((isset($value['required'])) && ($value['required'] == 'yes')) { $title = ' title="'.__('You can\'t disable the display of this column.', 'contact-manager').'"'; }
else { $title = ''; } }
else { $title = ' title="'.$value['title'].'"'; }
$undisplayed_columns = (array) $options['statistics_page_undisplayed_columns'];
if ((isset($value['required'])) && ($value['required'] == 'yes')) { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes" checked="checked" disabled="disabled" /> '.$value['name'].'</label><br />'; }
else { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes"'.(in_array($key, $undisplayed_columns) ? '' : ' checked="checked"').' /> '.$value['name'].'</label><br />'; } } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><?php _e('Rows displayed', 'contact-manager'); ?></strong></th>
<td><?php foreach ($statistics_rows as $key => $value) {
$name = 'statistics_page_'.$key.'_row_displayed';
if ((!isset($value['title'])) || ($value['title'] == '')) {
if ((isset($value['required'])) && ($value['required'] == 'yes')) { $title = ' title="'.__('You can\'t disable the display of this row.', 'contact-manager').'"'; }
else { $title = ''; } }
else { $title = ' title="'.$value['title'].'"'; }
$undisplayed_rows = (array) $options['statistics_page_undisplayed_rows'];
if ((isset($value['required'])) && ($value['required'] == 'yes')) { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes" checked="checked" disabled="disabled" /> '.$value['name'].'</label><br />'; }
else { echo '<label'.$title.'><input type="checkbox" name="'.$name.'" id="'.$name.'" value="yes"'.(in_array($key, $undisplayed_rows) ? '' : ' checked="checked"').' /> '.$value['name'].'</label><br />'; } } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php contact_manager_pages_module($options, 'back-office-page', $undisplayed_modules); ?>
<p class="submit"><input type="submit" class="button-primary" name="submit" id="submit" value="<?php _e('Save Changes', 'contact-manager'); ?>" /></p>
</form>
</div>
</div>

<script type="text/javascript">
<?php $modules_list = array(); $submodules = array();
foreach ($modules[$admin_page] as $key => $value) { $modules_list[] = $key; $submodules[$key] = array();
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) { $submodules[$key][] = $module_key; } } }
echo 'anchor = window.location.hash;
modules = '.json_encode($modules_list).';
submodules = '.json_encode($submodules).';
for (i = 0, n = modules.length; i < n; i++) {
element = document.getElementById(modules[i]+"-module");
if ((element) && (anchor == "#"+modules[i])) { element.style.display = "block"; }
for (j = 0, m = submodules[modules[i]].length; j < m; j++) {
subelement = document.getElementById(submodules[modules[i]][j]+"-module");
if ((subelement) && (anchor == "#"+submodules[modules[i]][j])) {
element.style.display = "block"; subelement.style.display = "block"; } } }'."\n"; ?>

<?php $fields = array('custom_icon_url', 'title'); echo 'fields = '.json_encode($fields).'; default_values = [];'."\n";
foreach ($fields as $field) { echo 'default_values["'.$field.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $initial_options['back_office'][$field]).'";'."\n"; }
echo 'for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
element.setAttribute("data-default", default_values[fields[i]]);
if (element.hasAttribute("onchange")) { string = " "+element.getAttribute("onchange"); } else { string = ""; }
element.setAttribute("onchange", "if (this.value === \'\') { this.value = this.getAttribute(\'data-default\'); }"+string); }'."\n"; ?>

<?php foreach (array('reset_links' => 'top', 'reset_menu_items' => 'menu') as $field => $location) { if (isset($_POST[$field])) { echo 'window.location = \'#'.$location.'\';'; } }
foreach ($modules as $key => $value) {
if ((isset($value['custom-fields'])) && ((isset($_POST['add_'.$key.'_page_custom_field'])) || (isset($_POST['delete_'.$key.'_page_custom_field'])))) {
echo 'window.location = \'#'.str_replace('_', '-', $key).'-page-custom-fields-module\';'; } } ?>
</script>