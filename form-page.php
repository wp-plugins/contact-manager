<?php global $wpdb; $error = '';
$back_office_options = (array) get_option('contact_manager_back_office');
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$is_category = (strstr($_GET['page'], 'category'));
if ($is_category) { $admin_page = 'form_category'; $table_slug = 'forms_categories'; $attribute = 'category'; }
else { $admin_page = 'form'; $table_slug = 'forms'; $attribute = 'id'; }

if ((isset($_GET['id'])) && (isset($_GET['action'])) && ($_GET['action'] == 'delete')) {
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!contact_manager_user_can($back_office_options, 'manage')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
if ($is_category) {
$category = $wpdb->get_row("SELECT category_id FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE id = ".$_GET['id'], OBJECT);
foreach (array('forms', 'forms_categories') as $table) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_".$table." SET category_id = ".$category->category_id." WHERE category_id = ".$_GET['id']); } }
$results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_".$table_slug." WHERE id = ".$_GET['id']); } } ?>
<div class="wrap">
<div id="poststuff">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) {
echo '<div class="updated"><p><strong>'.($is_category ? __('Category deleted.', 'contact-manager') : __('Form deleted.', 'contact-manager')).'</strong></p></div>
<script type="text/javascript">setTimeout(\'window.location = "admin.php?page=contact-manager-forms'.($is_category ? '-categories' : '').'"\', 2000);</script>'; } ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<div class="clear"></div>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php if (!isset($_POST['submit'])) { ?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<div class="alignleft actions">
<?php echo ($is_category ? __('Do you really want to permanently delete this category?', 'contact-manager') : __('Do you really want to permanently delete this form?', 'contact-manager')); ?> 
<input type="submit" class="button-secondary" name="submit" id="submit" value="<?php _e('Yes', 'contact-manager'); ?>" />
</div>
<div class="clear"></div>
</form><?php } ?>
</div>
</div><?php }

else {
include 'admin-pages.php'; include 'tables.php';
foreach ($tables[$table_slug] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!contact_manager_user_can($back_office_options, 'manage')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes(html_entity_decode(str_replace('&nbsp;', ' ', $value))); } }
$back_office_options = update_contact_manager_back_office($back_office_options, $admin_page);

if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
foreach (array(
'commission_amount',
'commission2_amount') as $field) { $_POST[$field] = str_replace(array('?', ',', ';'), '.', $_POST[$field]); }
if (!$is_category) {
foreach (array(
'displays_count',
'messages_count') as $field) {
$_POST[$field] = (int) $_POST[$field];
if ($_POST[$field] < 0) { $_POST[$field] = 0; } } }
$keywords = explode(',', $_POST['keywords']);
$keywords_list = '';
for ($i = 0; $i < count($keywords); $i++) { $keywords[$i] = strtolower(trim($keywords[$i])); }
sort($keywords);
foreach ($keywords as $keyword) { if ($keyword != '') { $keywords_list .= $keyword.', '; } }
$_POST['keywords'] = substr($keywords_list, 0, -2);
if (!$is_category) {
switch ($_POST['maximum_messages_quantity_per_sender']) { case '' : case 'i' : case 'infinite' : case 'u' : $_POST['maximum_messages_quantity_per_sender'] = 'unlimited'; } }
switch ($_POST['maximum_messages_quantity']) { case 'i' : case 'infinite' : case 'u' : $_POST['maximum_messages_quantity'] = 'unlimited'; }
$members_areas = array_unique(preg_split('#[^0-9]#', $_POST['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY));
sort($members_areas, SORT_NUMERIC);
$members_areas_list = '';
foreach ($members_areas as $member_area) { if ($member_area != '') { $members_areas_list .= $member_area.', '; } }
$_POST['sender_members_areas'] = substr($members_areas_list, 0, -2);
$_POST['sender_members_areas_modifications'] = contact_manager_format_members_areas_modifications($_POST['sender_members_areas_modifications']);
if ($_POST['date'] == '') {
$_POST['date'] = date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET);
$_POST['date_utc'] = date('Y-m-d H:i:s'); }
else {
$d = preg_split('#[^0-9]#', $_POST['date'], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : 0); }
$time = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
$_POST['date'] = date('Y-m-d H:i:s', $time);
$_POST['date_utc'] = date('Y-m-d H:i:s', $time - 3600*UTC_OFFSET); }
$custom_fields = (array) $back_office_options[$admin_page.'_page_custom_fields'];
$item_custom_fields = array();
foreach ($custom_fields as $key => $value) {
if ((isset($_POST['custom_field_'.$key])) && ($_POST['custom_field_'.$key] != '')) { $item_custom_fields[$key] = $_POST['custom_field_'.$key]; } }
if ($item_custom_fields != array()) { $_POST['custom_fields'] = serialize($item_custom_fields); }
if (!$is_category) {
if ($_POST['displays_count'] < $_POST['messages_count']) { $_POST['displays_count'] = $_POST['messages_count']; } }

if (!isset($_GET['id'])) {
if ($_POST['name'] == '') { $error .= ' '.__('Please fill out the required fields.', 'contact-manager'); }
elseif ($is_category) {
$result = $wpdb->get_results("SELECT name FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE name = '".str_replace("'", "''", $_POST['name'])."'", OBJECT);
if ($result) { $error .= ' '.__('This name is not available.', 'contact-manager'); } }
if ($error == '') {
if ($is_category) { $result = false; }
else { $result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_forms WHERE name = '".str_replace("'", "''", $_POST['name'])."' AND date = '".$_POST['date']."'", OBJECT); }
if (!$result) {
$updated = true;
$sql = contact_sql_array($tables[$table_slug], $_POST);
$keys_list = ''; $values_list = '';
foreach ($tables[$table_slug] as $key => $value) { if ($key != 'id') { $keys_list .= $key.","; $values_list .= $sql[$key].","; } }
$results = $wpdb->query("INSERT INTO ".$wpdb->prefix."contact_manager_".$table_slug." (".substr($keys_list, 0, -1).") VALUES(".substr($values_list, 0, -1).")"); } } }

if (isset($_GET['id'])) {
$updated = true;
if ((isset($_POST['count_messages'])) || (isset($_POST['count_messages_of_all_forms']))) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$_GET['id'], OBJECT);
$_POST['messages_count'] = (int) (isset($row->total) ? $row->total : 0);
if ($_POST['displays_count'] < $_POST['messages_count']) { $_POST['displays_count'] = $_POST['messages_count']; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET
	displays_count = ".$_POST['displays_count'].",
	messages_count = ".$_POST['messages_count']." WHERE id = ".$_GET['id']); }
if (isset($_POST['count_messages_of_all_forms'])) {
$forms = $wpdb->get_results("SELECT id, displays_count FROM ".$wpdb->prefix."contact_manager_forms WHERE id != ".$_GET['id'], OBJECT);
if ($forms) { foreach ($forms as $form) {
$displays_count = $form->displays_count;
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$form->id, OBJECT);
$messages_count = (int) (isset($row->total) ? $row->total : 0);
if ($displays_count < $messages_count) { $displays_count = $messages_count; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET
	displays_count = ".$displays_count.",
	messages_count = ".$messages_count." WHERE id = ".$form->id); } } }
if ($_POST['name'] != '') {
if (!$is_category) { $results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_".$table_slug." SET name = '".str_replace("'", "''", $_POST['name'])."' WHERE id = ".$_GET['id']); }
else {
$result = $wpdb->get_results("SELECT name FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE name = '".str_replace("'", "''", $_POST['name'])."' AND id != ".$_GET['id'], OBJECT);
if ($result) { $error .= ' '.__('This name is not available.', 'contact-manager'); }
else { $results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms_categories SET name = '".str_replace("'", "''", $_POST['name'])."' WHERE id = ".$_GET['id']); } } }
$sql = contact_sql_array($tables[$table_slug], $_POST);
$list = '';
foreach ($tables[$table_slug] as $key => $value) { switch ($key) {
case 'id': case 'name': break;
default: $list .= $key." = ".$sql[$key].","; } }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_".$table_slug." SET ".substr($list, 0, -1)." WHERE id = ".$_GET['id']); } } }

if (isset($_GET['id'])) {
$item_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table_slug." WHERE id = ".$_GET['id'], OBJECT);
if ($item_data) { foreach ($item_data as $key => $value) { $_POST[$key] = $value; } }
elseif (!headers_sent()) { header('Location: admin.php?page=contact-manager-forms'.($is_category ? '-categories' : '')); exit(); }
else { echo '<script type="text/javascript">window.location = "admin.php?page=contact-manager-forms'.($is_category ? '-categories' : '').'";</script>'; } }

foreach ($_POST as $key => $value) {
if (is_string($value)) {
$_POST[$key] = str_replace('&amp;amp;', '&amp;', htmlspecialchars(stripslashes($value)));
if ($value == '0000-00-00 00:00:00') { $_POST[$key] = ''; } } }
$undisplayed_modules = (array) $back_office_options[$admin_page.'_page_undisplayed_modules'];
if (function_exists('commerce_data')) { $currency_code = commerce_data('currency_code'); }
else { $commerce_manager_options = (array) get_option('commerce_manager');
$currency_code = do_shortcode($commerce_manager_options['currency_code']); } ?>

<div class="wrap">
<div id="poststuff">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if ((isset($updated)) && ($updated)) {
echo '<div class="updated"><p><strong>'.(isset($_GET['id']) ? ($is_category ? __('Category updated.', 'contact-manager') : __('Form updated.', 'contact-manager')) : ($is_category ? __('Category saved.', 'contact-manager') : __('Form saved.', 'contact-manager'))).'</strong></p></div>
'.(isset($_GET['id']) ? '' : '<script type="text/javascript">setTimeout(\'window.location = "admin.php?page=contact-manager-forms'.($is_category ? '-categories' : '').'"\', 2000);</script>'); } ?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<div class="clear"></div>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<p class="description"><?php _e('Only fields marked with * are required.', 'contact-manager'); ?> 
<?php if ($_POST['category_id'] > 0) { _e('You can apply the default option of the category by leaving the corresponding field blank.', 'contact-manager'); } ?></p>
<?php contact_manager_pages_summary($back_office_options); ?>

<div class="postbox" id="general-informations-module"<?php if (in_array('general-informations', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="general-informations"><strong><?php echo $modules[$admin_page]['general-informations']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php if ($_POST['category_id'] > 0) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager-form-category&amp;id=<?php echo $_POST['category_id']; ?>#general-informations">
<?php ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager')); ?></a></span></td></tr>
<?php } ?>
<?php if (isset($_GET['id'])) { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="id">'.__('ID', 'contact-manager').'</label></strong></th>
<td><input type="text" name="id" id="id" size="10" value="'.$_GET['id'].'" disabled="disabled" /> <span class="description">'.__('The ID can not be changed.', 'contact-manager').'</span><br />';
if ($is_category) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms WHERE category_id = ".$_GET['id'], OBJECT);
$forms_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE category_id = ".$_GET['id'], OBJECT);
$categories_number = (int) (isset($row->total) ? $row->total : 0);
echo '<a style="text-decoration: none;" href="admin.php?page=contact-manager-form-category&amp;id='.$_GET['id'].'&amp;action=delete">'.__('Delete').'</a>'
.($forms_number == 0 ? '' : ' | <a style="text-decoration: none;" href="admin.php?page=contact-manager-forms&amp;category_id='.$_GET['id'].'">'.__('Forms', 'contact-manager').'</a>')
.($categories_number == 0 ? '' : ' | <a style="text-decoration: none;" href="admin.php?page=contact-manager-forms-categories&amp;category_id='.$_GET['id'].'">'.__('Subcategories', 'contact-manager').'</a>'); }
else {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$_GET['id'], OBJECT);
$messages_number = (int) (isset($row->total) ? $row->total : 0);
echo '<a style="text-decoration: none;" href="admin.php?page=contact-manager-form&amp;id='.$_GET['id'].'&amp;action=delete">'.__('Delete').'</a>'
.($messages_number == 0 ? '' : ' | <a style="text-decoration: none;" href="admin.php?page=contact-manager-messages&amp;form_id='.$_GET['id'].'">'.__('Messages', 'contact-manager').'</a>'); }
echo '</td></tr>'; } ?>
<?php $categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."contact_manager_forms_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="category_id"><?php echo ($is_category ? __('Parent category', 'contact-manager') : __('Category', 'contact-manager')); ?></label></strong></th>
<td><select name="category_id" id="category_id">
<option value="0"<?php if ($_POST['category_id'] == 0) { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
if ((!$is_category) || (!isset($_GET['id'])) || (!in_array($_GET['id'], contact_forms_categories_list($category->id)))) {
echo '<option value="'.$category->id.'"'.($_POST['category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } } ?>
</select>
<span class="description"><?php ($is_category ? _e('The options of this category will apply by default to the category.', 'contact-manager') : _e('The options of this category will apply by default to the form.', 'contact-manager')); ?></span>
<?php if ($_POST['category_id'] > 0) { echo '<br />
<a style="text-decoration: none;" href="admin.php?page=contact-manager-form-category&amp;id='.$_POST['category_id'].'">'.__('Edit').'</a> | 
<a style="text-decoration: none;" href="admin.php?page=contact-manager-form-category&amp;id='.$_POST['category_id'].'&amp;action=delete">'.__('Delete').'</a>'; } ?></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;<?php if ((!isset($_GET['id'])) && (isset($_POST['submit'])) && ($_POST['name'] == '')) { echo ' color: #c00000;'; } ?>"><strong><label for="name"><?php _e('Name', 'contact-manager'); ?></label></strong> *</th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="name" id="name" rows="1" cols="50"><?php echo $_POST['name']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="description"><?php _e('Description', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="description" id="description" rows="1" cols="75"><?php echo $_POST['description']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="keywords"><?php _e('Keywords', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="keywords" id="keywords" rows="1" cols="75"><?php echo $_POST['keywords']; ?></textarea><br />
<span class="description"><?php _e('Separate the keywords with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="date"><?php _e('Creation date', 'contact-manager'); ?></label></strong></th>
<td><input class="date-pick" style="margin-right: 0.5em;" type="text" name="date" id="date" size="20" value="<?php echo ($_POST['date'] != '' ? $_POST['date'] : date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET)); ?>" /></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="custom-fields-module"<?php if (in_array('custom-fields', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="custom-fields"><strong><?php echo $modules[$admin_page]['custom-fields']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager-back-office#<?php echo str_replace('_', '-', $admin_page); ?>-page-custom-fields"><?php _e('Click here to add a new custom field.', 'contact-manager'); ?></a>
 <a href="http://www.kleor-editions.com/contact-manager/#custom-fields"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $custom_fields = (array) $back_office_options[$admin_page.'_page_custom_fields'];
$item_custom_fields = (array) unserialize(htmlspecialchars_decode($_POST['custom_fields']));
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = do_shortcode($value); }
asort($custom_fields); $content = ''; foreach ($custom_fields as $key => $value) {
$field_value = (isset($item_custom_fields[$key]) ? $item_custom_fields[$key] : '');
if ((strlen($field_value) > 75) || (strstr($field_value, '
'))) { $rows = 3; } else { $rows = 1; }
$content .= '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="custom_field_'.$key.'">'.htmlspecialchars($value).'</label></strong></th>
<td><textarea style="padding: 0 0.25em; '.($rows == 1 ? 'height: 1.75em; ' : '').'width: 75%;" name="custom_field_'.$key.'" id="custom_field_'.$key.'" rows="'.$rows.'" cols="75">'.htmlspecialchars($field_value).'</textarea>'
.(((!strstr($field_value, ' ')) && (substr($field_value, 0, 4) == 'http')) ? ' <a style="vertical-align: 25%;" href="'.htmlspecialchars($field_value).'">'.__('Link', 'contact-manager').'</a>' : '').'</td></tr>'; }
echo $content; if ($content == '') { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td>'.__('You have no custom field currently.', 'contact-manager').'</td></tr>'; } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="gift-module"<?php if (in_array('gift', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="gift"><strong><?php echo $modules[$admin_page]['gift']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php if ($_POST['category_id'] > 0) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager-form-category&amp;id=<?php echo $_POST['category_id']; ?>#gift">
<?php ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager')); ?></a></span></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="gift_download_url"><?php _e('Download URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="gift_download_url" id="gift_download_url" rows="1" cols="75"><?php echo $_POST['gift_download_url']; ?></textarea> 
<?php $url = htmlspecialchars(contact_form_data(array(0 => 'gift_download_url', 'part' => 1, $attribute => (isset($_GET['id']) ? $_GET['id'] : 0)))); if ($url != '') { ?><a style="vertical-align: 25%;" href="<?php echo $url; ?>"><?php _e('Link', 'contact-manager'); ?></a><?php } ?><br />
<span class="description"><?php _e('You can enter several URLs.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/contact-manager/#multiple-urls"><?php _e('More informations', 'contact-manager'); ?></a> 
<a href="http://www.kleor-editions.com/contact-manager/#urls-encryption"><?php _e('How to encrypt a download URL?', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="gift_instructions"><?php _e('Instructions to the sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="gift_instructions" id="gift_instructions" rows="5" cols="75"><?php echo $_POST['gift_instructions']; ?></textarea>
<span class="description"><?php _e('You can offer a gift to senders.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/contact-manager/#gift"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php if (!$is_category) { ?>
<div class="postbox" id="counters-module"<?php if (in_array('counters', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="counters"><strong><?php echo $modules[$admin_page]['counters']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity_per_sender"><?php _e('Maximum messages quantity per sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="maximum_messages_quantity_per_sender" id="maximum_messages_quantity_per_sender" rows="1" cols="25"><?php echo (!is_numeric($_POST['maximum_messages_quantity_per_sender']) ? '' : $_POST['maximum_messages_quantity_per_sender']); ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank for an unlimited quantity.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="displays_count"><?php _e('Displays count', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="displays_count" id="displays_count" rows="1" cols="25"><?php echo $_POST['displays_count']; ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank for 0.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="messages_count"><?php _e('Messages count', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="messages_count" id="messages_count" rows="1" cols="25"><?php echo $_POST['messages_count']; ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank for 0.', 'contact-manager'); ?></span><br />
<?php if ($_POST['messages_count'] > 0) { echo '<a style="text-decoration: none;" href="admin.php?page=contact-manager-messages&amp;form_id='.$_GET['id'].'">'.__('Display the messages', 'contact-manager').'</a>'; } ?>
<?php if (isset($_GET['id'])) { echo '<input type="hidden" name="submit" value="true" />
<input type="submit" class="button-secondary" name="count_messages" value="'.__('Re-count the messages', 'contact-manager').'" />
<input type="submit" class="button-secondary" name="count_messages_of_all_forms" value="'.__('Re-count the messages of all forms', 'contact-manager').'" />'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<div class="postbox" id="form-module"<?php if (in_array('form', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="form"><strong><?php echo $modules[$admin_page]['form']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager<?php echo ($_POST['category_id'] == 0 ? '#form' : '-form-category&amp;id='.$_POST['category_id'].'#form'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="code"><?php _e('Code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="code" id="code" rows="15" cols="75"><?php echo $_POST['code']; ?></textarea>
<span class="description"><a href="http://www.kleor-editions.com/contact-manager/#forms"><?php _e('How to display a form?', 'contact-manager'); ?></a><br />
<a href="http://www.kleor-editions.com/contact-manager/#forms-creation"><?php _e('How to create a form?', 'contact-manager'); ?></a></span><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
<div id="error-messages-module"<?php if (in_array('error-messages', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="error-messages"><strong><?php echo $modules[$admin_page]['form']['modules']['error-messages']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unfilled_fields_message"><?php _e('Unfilled required fields', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unfilled_fields_message" id="unfilled_fields_message" rows="1" cols="75"><?php echo $_POST['unfilled_fields_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unfilled_field_message"><?php _e('Unfilled required field', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unfilled_field_message" id="unfilled_field_message" rows="1" cols="75"><?php echo $_POST['unfilled_field_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_email_address_message"><?php _e('Invalid email address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_email_address_message" id="invalid_email_address_message" rows="1" cols="75"><?php echo $_POST['invalid_email_address_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_captcha_message"><?php _e('Invalid CAPTCHA', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_captcha_message" id="invalid_captcha_message" rows="1" cols="75"><?php echo $_POST['invalid_captcha_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="failed_upload_message"><?php _e('Failed upload', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="failed_upload_message" id="failed_upload_message" rows="1" cols="75"><?php echo $_POST['failed_upload_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="too_large_file_message"><?php _e('Too large file', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="too_large_file_message" id="too_large_file_message" rows="1" cols="75"><?php echo $_POST['too_large_file_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unauthorized_extension_message"><?php _e('Unauthorized extension', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unauthorized_extension_message" id="unauthorized_extension_message" rows="1" cols="75"><?php echo $_POST['unauthorized_extension_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity_reached_message"><?php _e('Maximum messages quantity reached', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="maximum_messages_quantity_reached_message" id="maximum_messages_quantity_reached_message" rows="1" cols="75"><?php echo $_POST['maximum_messages_quantity_reached_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
</div>
<table class="form-table"><tbody><tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr></tbody></table>
</div></div>

<div class="postbox" id="messages-registration-module"<?php if (in_array('messages-registration', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="messages-registration"><strong><?php echo $modules[$admin_page]['messages-registration']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager<?php echo ($_POST['category_id'] == 0 ? '#messages-registration' : '-form-category&amp;id='.$_POST['category_id'].'#messages-registration'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="messages_registration_enabled"><?php _e('Save messages in the database', 'contact-manager'); ?></label></strong></th>
<td><select name="messages_registration_enabled" id="messages_registration_enabled">
<option value=""<?php if ($_POST['messages_registration_enabled'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['messages_registration_enabled'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['messages_registration_enabled'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php _e('You can save only the latest messages to ease your database.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity"><?php _e('Maximum messages quantity', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="maximum_messages_quantity" id="maximum_messages_quantity" rows="1" cols="25"><?php echo ($_POST['maximum_messages_quantity'] == 'unlimited' ? 'i' : $_POST['maximum_messages_quantity']); ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Enter <em><strong>i</strong></em> for an unlimited quantity.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="message-confirmation-email-module"<?php if (in_array('message-confirmation-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="message-confirmation-email"><strong><?php echo $modules[$admin_page]['message-confirmation-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager<?php echo ($_POST['category_id'] == 0 ? '#message-confirmation-email' : '-form-category&amp;id='.$_POST['category_id'].'#message-confirmation-email'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_sent"><?php _e('Send a message confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="message_confirmation_email_sent" id="message_confirmation_email_sent">
<option value=""<?php if ($_POST['message_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['message_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['message_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_sender" id="message_confirmation_email_sender" rows="1" cols="75"><?php echo $_POST['message_confirmation_email_sender']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_receiver" id="message_confirmation_email_receiver" rows="1" cols="75"><?php echo $_POST['message_confirmation_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_subject" id="message_confirmation_email_subject" rows="1" cols="75"><?php echo $_POST['message_confirmation_email_subject']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_confirmation_email_body" id="message_confirmation_email_body" rows="15" cols="75"><?php echo $_POST['message_confirmation_email_body']; ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="message-notification-email-module"<?php if (in_array('message-notification-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="message-notification-email"><strong><?php echo $modules[$admin_page]['message-notification-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager<?php echo ($_POST['category_id'] == 0 ? '#message-notification-email' : '-form-category&amp;id='.$_POST['category_id'].'#message-notification-email'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_sent"><?php _e('Send a message notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="message_notification_email_sent" id="message_notification_email_sent">
<option value=""<?php if ($_POST['message_notification_email_sent'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['message_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['message_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_sender" id="message_notification_email_sender" rows="1" cols="75"><?php echo $_POST['message_notification_email_sender']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_receiver" id="message_notification_email_receiver" rows="1" cols="75"><?php echo $_POST['message_notification_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_subject" id="message_notification_email_subject" rows="1" cols="75"><?php echo $_POST['message_notification_email_subject']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_notification_email_body" id="message_notification_email_body" rows="15" cols="75"><?php echo $_POST['message_notification_email_body']; ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="autoresponders-module"<?php if (in_array('autoresponders', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="autoresponders"><strong><?php echo $modules[$admin_page]['autoresponders']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager<?php echo ($_POST['category_id'] == 0 ? '#autoresponders' : '-form-category&amp;id='.$_POST['category_id'].'#autoresponders'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_to_autoresponder"><?php _e('Subscribe the sender to an autoresponder list', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_to_autoresponder" id="sender_subscribed_to_autoresponder">
<option value=""<?php if ($_POST['sender_subscribed_to_autoresponder'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_to_autoresponder'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_to_autoresponder'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder"><?php _e('Autoresponder', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_autoresponder" id="sender_autoresponder">
<?php include 'libraries/autoresponders.php';
$autoresponder = do_shortcode($_POST['sender_autoresponder']);
echo '<option value=""'.($autoresponder == '' ? ' selected="selected"' : '').'>'.__('Default option', 'contact-manager').'</option>'."\n";
foreach ($autoresponders as $value) {
echo '<option value="'.$value.'"'.($autoresponder == $value ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder_list"><?php _e('List', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_autoresponder_list" id="sender_autoresponder_list" rows="1" cols="50"><?php echo $_POST['sender_autoresponder_list']; ?></textarea><br />
<span class="description"><?php _e('For some autoresponders, you must enter the list ID.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="registration-as-a-client-module"<?php if (in_array('registration-as-a-client', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="registration-as-a-client"><strong><?php echo $modules[$admin_page]['registration-as-a-client']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('commerce_manager_admin_menu')) { ?>
<a href="admin.php?page=<?php echo ($_POST['category_id'] == 0 ? 'contact-manager#registration-as-a-client' : 'contact-manager-form-category&amp;id='.$_POST['category_id'].'#registration-as-a-client'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a>
<?php } else { _e('To subscribe the senders as clients, you must have installed and activated <a href="http://www.kleor-editions.com/commerce-manager">Commerce Manager</a>.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_as_a_client"><?php _e('Subscribe the sender as a client', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_as_a_client" id="sender_subscribed_as_a_client">
<option value=""<?php if ($_POST['sender_subscribed_as_a_client'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_as_a_client'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_as_a_client'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a href="http://www.kleor-editions.com/contact-manager/#registration-as-a-client"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."commerce_manager_clients_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_category_id" id="sender_client_category_id">
<option value=""<?php if ($_POST['sender_client_category_id'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="0"<?php if ($_POST['sender_client_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_client_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if ((function_exists('commerce_manager_admin_menu')) && ($_POST['sender_client_category_id'] > 0)) { echo '<br />
<a style="text-decoration: none;" href="admin.php?page=commerce-manager-client-category&amp;id='.$_POST['sender_client_category_id'].'">'.__('Edit').'</a> | 
<a style="text-decoration: none;" href="admin.php?page=commerce-manager-client-category&amp;id='.$_POST['sender_client_category_id'].'&amp;action=delete">'.__('Delete').'</a>'; } ?></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_status" id="sender_client_status">
<option value=""<?php if ($_POST['sender_client_status'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="active"<?php if ($_POST['sender_client_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_client_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a href="http://www.kleor-editions.com/commerce-manager/documentation/#client-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commerce_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="commerce_registration_confirmation_email_sent" id="commerce_registration_confirmation_email_sent">
<option value=""<?php if ($_POST['commerce_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['commerce_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['commerce_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commerce_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="commerce_registration_notification_email_sent" id="commerce_registration_notification_email_sent">
<option value=""<?php if ($_POST['commerce_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['commerce_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['commerce_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="registration-to-affiliate-program-module"<?php if (in_array('registration-to-affiliate-program', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="registration-to-affiliate-program"><strong><?php echo $modules[$admin_page]['registration-to-affiliate-program']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('affiliation_manager_admin_menu')) { ?>
<a href="admin.php?page=<?php echo ($_POST['category_id'] == 0 ? 'contact-manager#registration-to-affiliate-program' : 'contact-manager-form-category&amp;id='.$_POST['category_id'].'#registration-to-affiliate-program'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a>
<?php } else { _e('To use affiliation, you must have installed and activated <a href="http://www.kleor-editions.com/affiliation-manager">Affiliation Manager</a>.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_to_affiliate_program"><?php _e('Subscribe the sender to affiliate program', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_to_affiliate_program" id="sender_subscribed_to_affiliate_program">
<option value=""<?php if ($_POST['sender_subscribed_to_affiliate_program'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_to_affiliate_program'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_to_affiliate_program'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a href="http://www.kleor-editions.com/contact-manager/#registration-to-affiliate-program"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."affiliation_manager_affiliates_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_category_id" id="sender_affiliate_category_id">
<option value=""<?php if ($_POST['sender_affiliate_category_id'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="0"<?php if ($_POST['sender_affiliate_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_affiliate_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if ((function_exists('affiliation_manager_admin_menu')) && ($_POST['sender_affiliate_category_id'] > 0)) { echo '<br />
<a style="text-decoration: none;" href="admin.php?page=affiliation-manager-affiliate-category&amp;id='.$_POST['sender_affiliate_category_id'].'">'.__('Edit').'</a> | 
<a style="text-decoration: none;" href="admin.php?page=affiliation-manager-affiliate-category&amp;id='.$_POST['sender_affiliate_category_id'].'&amp;action=delete">'.__('Delete').'</a>'; } ?></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_status" id="sender_affiliate_status">
<option value=""<?php if ($_POST['sender_affiliate_status'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="active"<?php if ($_POST['sender_affiliate_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_affiliate_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a href="http://www.kleor-editions.com/affiliation-manager/documentation/#affiliate-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_registration_confirmation_email_sent" id="affiliation_registration_confirmation_email_sent">
<option value=""<?php if ($_POST['affiliation_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['affiliation_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['affiliation_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_registration_notification_email_sent" id="affiliation_registration_notification_email_sent">
<option value=""<?php if ($_POST['affiliation_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['affiliation_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['affiliation_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="membership-module"<?php if (in_array('membership', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="membership"><strong><?php echo $modules[$admin_page]['membership']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('membership_manager_admin_menu')) { ?>
<a href="admin.php?page=<?php echo ($_POST['category_id'] == 0 ? 'contact-manager#membership' : 'contact-manager-form-category&amp;id='.$_POST['category_id'].'#membership'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a>
<?php } else { _e('To use membership, you must have installed and activated <a href="http://www.kleor-editions.com/membership-manager">Membership Manager</a>.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_to_members_areas"><?php _e('Subscribe the sender to a member area', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_to_members_areas" id="sender_subscribed_to_members_areas">
<option value=""<?php if ($_POST['sender_subscribed_to_members_areas'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_to_members_areas'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_to_members_areas'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a href="http://www.kleor-editions.com/contact-manager/#membership"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas"><?php _e('Members areas', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_members_areas" id="sender_members_areas" rows="1" cols="50"><?php echo $_POST['sender_members_areas']; ?></textarea>
<?php if ((function_exists('membership_manager_admin_menu')) && (is_numeric($_POST['sender_members_areas'])) && ($_POST['sender_members_areas'] > 0)) { echo '<br />
<a style="text-decoration: none;" href="admin.php?page=membership-manager-member-area&amp;id='.$_POST['sender_members_areas'].'">'.__('Edit').'</a> | 
<a style="text-decoration: none;" href="admin.php?page=membership-manager-member-area&amp;id='.$_POST['sender_members_areas'].'&amp;action=delete">'.__('Delete').'</a>'; } ?><br />
<span class="description"><?php _e('You can enter several members areas IDs. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas_modifications"><?php _e('Automatic modifications', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 50%;" name="sender_members_areas_modifications" id="sender_members_areas_modifications" rows="2" cols="50"><?php echo $_POST['sender_members_areas_modifications']; ?></textarea>
<span class="description"><?php _e('You can automatically modify the members areas to which the member can access when a certain date is reached.', 'contact-manager'); ?>
 <a href="http://www.kleor-editions.com/membership-manager/documentation/#members-areas-modifications"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."membership_manager_members_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_category_id" id="sender_member_category_id">
<option value=""<?php if ($_POST['sender_member_category_id'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="0"<?php if ($_POST['sender_member_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_member_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if ((function_exists('membership_manager_admin_menu')) && ($_POST['sender_member_category_id'] > 0)) { echo '<br />
<a style="text-decoration: none;" href="admin.php?page=membership-manager-member-category&amp;id='.$_POST['sender_member_category_id'].'">'.__('Edit').'</a> | 
<a style="text-decoration: none;" href="admin.php?page=membership-manager-member-category&amp;id='.$_POST['sender_member_category_id'].'&amp;action=delete">'.__('Delete').'</a>'; } ?></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_status" id="sender_member_status">
<option value=""<?php if ($_POST['sender_member_status'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="active"<?php if ($_POST['sender_member_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_member_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a href="http://www.kleor-editions.com/membership-manager/documentation/#member-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="membership_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="membership_registration_confirmation_email_sent" id="membership_registration_confirmation_email_sent">
<option value=""<?php if ($_POST['membership_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['membership_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['membership_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="membership_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="membership_registration_notification_email_sent" id="membership_registration_notification_email_sent">
<option value=""<?php if ($_POST['membership_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['membership_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['membership_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="wordpress-module"<?php if (in_array('wordpress', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="wordpress"><strong><?php echo $modules[$admin_page]['wordpress']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager<?php echo ($_POST['category_id'] == 0 ? '#wordpress' : '-form-category&amp;id='.$_POST['category_id'].'#wordpress'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_as_a_user"><?php _e('Subscribe the sender as a user', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_as_a_user" id="sender_subscribed_as_a_user">
<option value=""<?php if ($_POST['sender_subscribed_as_a_user'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_as_a_user'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_as_a_user'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a href="http://www.kleor-editions.com/contact-manager/#wordpress"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_user_role"><?php _e('Role', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_user_role" id="sender_user_role">
<option value=""<?php if ($_POST['sender_user_role'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<?php foreach (contact_manager_users_roles() as $role => $name) {
echo '<option value="'.$role.'"'.($_POST['sender_user_role'] == $role ? ' selected="selected"' : '').'>'.$name.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="custom-instructions-module"<?php if (in_array('custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="custom-instructions"><strong><?php echo $modules[$admin_page]['custom-instructions']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a href="admin.php?page=contact-manager<?php echo ($_POST['category_id'] == 0 ? '#custom-instructions' : '-form-category&amp;id='.$_POST['category_id'].'#custom-instructions'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_custom_instructions_executed"><?php _e('Execute custom instructions', 'contact-manager'); ?></label></strong></th>
<td><select name="message_custom_instructions_executed" id="message_custom_instructions_executed">
<option value=""<?php if ($_POST['message_custom_instructions_executed'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['message_custom_instructions_executed'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['message_custom_instructions_executed'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_custom_instructions" id="message_custom_instructions" rows="10" cols="75"><?php echo $_POST['message_custom_instructions']; ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the sending of the message.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="affiliation-module"<?php if (in_array('affiliation', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="affiliation"><strong><?php echo $modules[$admin_page]['affiliation']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('affiliation_manager_admin_menu')) { ?>
<a href="admin.php?page=contact-manager<?php echo ($_POST['category_id'] == 0 ? '#affiliation' : '-form-category&amp;id='.$_POST['category_id'].'#affiliation'); ?>">
<?php ($_POST['category_id'] == 0 ? _e('Click here to configure the default options.', 'contact-manager') : ($is_category ? _e('Click here to configure the default options of the parent category.', 'contact-manager') : _e('Click here to configure the default options of the category.', 'contact-manager'))); ?></a>
<?php } else { _e('To use affiliation, you must have installed and activated <a href="http://www.kleor-editions.com/affiliation-manager">Affiliation Manager</a>.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_enabled"><?php _e('Use affiliation', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_enabled" id="affiliation_enabled">
<option value=""<?php if ($_POST['affiliation_enabled'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['affiliation_enabled'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['affiliation_enabled'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
</tbody></table>
<div id="level-1-commission-module"<?php if (in_array('level-1-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-1-commission"><strong><?php echo $modules[$admin_page]['affiliation']['modules']['level-1-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 1 commission is awarded to the affiliate who referred the message.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission_amount" id="commission_amount" rows="1" cols="25"><?php echo $_POST['commission_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span> 
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
</div>
<div id="level-2-commission-module"<?php if (in_array('level-2-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-2-commission"><strong><?php echo $modules[$admin_page]['affiliation']['modules']['level-2-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 2 commission is awarded to the referrer of the affiliate who referred the message.', 'contact-manager'); ?> <a href="http://www.kleor-editions.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_enabled"><?php _e('Award a level 2 commission', 'contact-manager'); ?></label></strong></th>
<td><select name="commission2_enabled" id="commission2_enabled">
<option value=""<?php if ($_POST['commission2_enabled'] == '') { echo ' selected="selected"'; } ?>><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['commission2_enabled'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['commission2_enabled'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission2_amount" id="commission2_amount" rows="1" cols="25"><?php echo $_POST['commission2_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span> 
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
</div>
<table class="form-table"><tbody><tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update') : __('Save')); ?>" /></td></tr></tbody></table>
</div></div>

<p class="submit" style="margin: 0 20%;"><input type="submit" class="button-primary" name="submit" id="submit" value="<?php (isset($_GET['id']) ?  _e('Save Changes', 'contact-manager') : ($is_category ? _e('Save Category', 'contact-manager') : _e('Save Form', 'contact-manager'))); ?>" /></p>
<?php if ($is_category) { $module = 'form-category-page'; } else { $module = 'form-page'; }
contact_manager_pages_module($back_office_options, $module, $undisplayed_modules); ?>
</form>
</div>
</div>

<script type="text/javascript">
var anchor = window.location.hash;
<?php foreach ($modules[$admin_page] as $key => $value) {
echo "if (anchor == '#".$key."') { document.getElementById('".$key."-module').style.display = 'block'; }\n";
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) {
echo "if (anchor == '#".$module_key."') {
document.getElementById('".$key."-module').style.display = 'block';
document.getElementById('".$module_key."-module').style.display = 'block'; }\n"; } } } ?>
</script>
<?php }