<?php global $wpdb; $error = '';
$back_office_options = (array) get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($back_office_options));
date_default_timezone_set('UTC');
$current_time = time();
$current_date = date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET);
$current_date_utc = date('Y-m-d H:i:s', $current_time);
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
$results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_".$table_slug." WHERE id = ".$_GET['id']);
$result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_".$table_slug." ORDER BY id DESC LIMIT 1", OBJECT);
if (!$result) { $results = $wpdb->query("ALTER TABLE ".$wpdb->prefix."contact_manager_".$table_slug." AUTO_INCREMENT = 1"); }
elseif ($result->id < $_GET['id']) {
$results = $wpdb->query("ALTER TABLE ".$wpdb->prefix."contact_manager_".$table_slug." AUTO_INCREMENT = ".($result->id + 1)); } } } ?>
<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) {
echo '<div class="updated"><p><strong>'.($is_category ? __('Category deleted.', 'contact-manager') : __('Form deleted.', 'contact-manager')).'</strong></p></div>
<script type="text/javascript">setTimeout(\'window.location = "admin.php?page=contact-manager-forms'.($is_category ? '-categories' : '').'"\', 2000);</script>'; } ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php if (!isset($_POST['submit'])) { ?>
<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<div class="alignleft actions">
<p><strong style="color: #c00000;"><?php echo ($is_category ? __('Do you really want to permanently delete this category?', 'contact-manager') : __('Do you really want to permanently delete this form?', 'contact-manager')); ?></strong> 
<input type="submit" class="button-secondary" name="submit" id="submit" value="<?php _e('Yes', 'contact-manager'); ?>" /></p>
</div>
<div class="clear"></div>
</form><?php } ?>
</div>
</div><?php }

else {
include CONTACT_MANAGER_PATH.'admin-pages.php'; include CONTACT_MANAGER_PATH.'tables.php';
foreach ($tables[$table_slug] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!contact_manager_user_can($back_office_options, 'manage')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes(html_entity_decode(str_replace(array('&nbsp;', '&#91;', '&#93;'), array(' ', '&amp;#91;', '&amp;#93;'), $value))); } }
$back_office_options = update_contact_manager_back_office($back_office_options, $admin_page);
include CONTACT_MANAGER_PATH.'includes/fill-form.php'; } }

if (isset($_GET['id'])) {
$item_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_".$table_slug." WHERE id = ".$_GET['id'], OBJECT);
if ($item_data) {
$GLOBALS['contact_'.$admin_page.'_data'] = (array) $item_data;
$GLOBALS['contact_'.$admin_page.'_id'] = $item_data->id;
foreach ($item_data as $key => $value) { if ((!isset($_POST[$key])) || (!isset($_POST[$key.'_error']))) { $_POST[$key] = $value; } } }
elseif (!headers_sent()) { header('Location: admin.php?page=contact-manager-forms'.($is_category ? '-categories' : '')); exit(); }
else { echo '<script type="text/javascript">window.location = "admin.php?page=contact-manager-forms'.($is_category ? '-categories' : '').'";</script>'; } }
else { $GLOBALS['contact_'.$admin_page.'_id'] = 0; $GLOBALS['contact_'.$admin_page.'_data'] = array(); }

foreach ($_POST as $key => $value) {
if (is_string($value)) {
$_POST[$key] = str_replace('&amp;amp;', '&amp;', htmlspecialchars(stripslashes($value)));
if (($value == '0000-00-00 00:00:00') && ((substr($key, -4) == 'date') || (substr($key, -8) == 'date_utc'))) { $_POST[$key] = ''; } } }
$undisplayed_modules = (array) $back_office_options[$admin_page.'_page_undisplayed_modules'];
foreach (array('default_options_select_fields', 'ids_fields', 'urls_fields') as $variable) { $$variable = array(); }
if (function_exists('commerce_data')) { $currency_code = commerce_data('currency_code'); }
else { $commerce_manager_options = array_merge((array) get_option('commerce_manager'), (array) get_option('commerce_manager_client_area'));
$currency_code = (isset($commerce_manager_options['currency_code']) ? do_shortcode($commerce_manager_options['currency_code']) : ''); } ?>

<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if ((isset($updated)) && ($updated)) {
echo '<div class="updated"><p><strong>'.(isset($_GET['id']) ? ($is_category ? __('Category updated.', 'contact-manager') : __('Form updated.', 'contact-manager')) : ($is_category ? __('Category saved.', 'contact-manager') : __('Form saved.', 'contact-manager'))).'</strong></p></div>
'.(isset($_GET['id']) ? '' : '<script type="text/javascript">setTimeout(\'window.location = "admin.php?page=contact-manager-forms'.($is_category ? '-categories' : '').'"\', 2000);</script>'); } ?>
<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" onsubmit="return validate_form(this);">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<p class="description"><?php _e('Only fields marked with * are required.', 'contact-manager'); ?><span id="category-top-description"></span></p>
<?php contact_manager_pages_summary($back_office_options); ?>

<script type="text/javascript">
function update_category_top_description(category) {
var element = document.getElementById("category-top-description");
if (element) {
if (category == 0) { element.innerHTML = ""; }
else { element.innerHTML = " <?php echo str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'),
($is_category ? __('You can apply the default option of the parent category by leaving the corresponding field blank.', 'contact-manager')
 : __('You can apply the default option of the category by leaving the corresponding field blank.', 'contact-manager'))); ?>"; } } }
update_category_top_description(<?php echo (int) $_POST['category_id']; ?>);
</script>

<div class="postbox" id="general-informations-module"<?php if (in_array('general-informations', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="general-informations"><strong><?php echo $modules[$admin_page]['general-informations']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="general-informations-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'general-informations'); ?></tr>
<?php if (isset($_GET['id'])) { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="id">'.__('ID', 'contact-manager').'</label></strong></th>
<td><input type="text" name="id" id="id" size="10" value="'.$_GET['id'].'" disabled="disabled" /> <span class="description">'.__('The ID can not be changed.', 'contact-manager').'</span>
<span id="id-links">'.contact_manager_pages_field_links($back_office_options, 'id', $_GET['id']).'</span></td></tr>'; } ?>
<?php $categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."contact_manager_forms_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="category_id"><?php echo ($is_category ? __('Parent category', 'contact-manager') : __('Category', 'contact-manager')); ?></label></strong></th>
<td><select name="category_id" id="category_id" onchange="update_category_top_description(this.value); fill_form(this.form);">
<option value="0"<?php if ($_POST['category_id'] == 0) { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
if ((!$is_category) || (!isset($_GET['id'])) || (!in_array($_GET['id'], contact_forms_categories_list($category->id)))) {
echo '<option value="'.$category->id.'"'.($_POST['category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } } ?>
</select>
<span class="description"><?php ($is_category ? _e('The options of the parent category will apply by default to this category.', 'contact-manager') : _e('The options of this category will apply by default to the form.', 'contact-manager')); ?></span>
<?php $ids_fields[] = 'category_id'; echo '<span id="category-id-links">'.contact_manager_pages_field_links($back_office_options, 'category_id', $_POST['category_id']).'</span>'; ?></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;<?php if ((!isset($_GET['id'])) && (isset($_POST['submit'])) && ($_POST['name'] == '')) { echo ' color: #c00000;'; } ?>" id="name-th"><strong><label for="name"><?php _e('Name', 'contact-manager'); ?></label></strong> *</th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="name" id="name" rows="1" cols="50"<?php if ((!isset($_GET['id'])) && (isset($_POST['submit'])) && ($_POST['name'] == '')) { echo ' placeholder="'.__('This field is required.', 'contact-manager').'"'; } ?> onchange="fill_form(this.form);"><?php echo $_POST['name']; ?></textarea>
<br /><span style="color: #c00000;" id="name_error"><?php echo (isset($_POST['name_error']) ? $_POST['name_error'] : ''); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="description"><?php _e('Description', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="description" id="description" rows="1" cols="75"><?php echo $_POST['description']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="keywords"><?php _e('Keywords', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="keywords" id="keywords" rows="1" cols="75" onchange="fill_form(this.form);"><?php echo $_POST['keywords']; ?></textarea><br />
<span class="description"><?php _e('Separate the keywords with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="date"><?php _e('Creation date', 'contact-manager'); ?></label></strong></th>
<td><input class="date-pick" type="text" name="date" id="date" size="20" value="<?php echo ($_POST['date'] != '' ? $_POST['date'] : $current_date); ?>" onchange="fill_form(this.form);" /></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="custom-fields-module"<?php if (in_array('custom-fields', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="custom-fields"><strong><?php echo $modules[$admin_page]['custom-fields']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager-back-office#<?php echo str_replace('_', '-', $admin_page); ?>-page-custom-fields"><?php _e('Click here to add a new custom field.', 'contact-manager'); ?></a>
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-fields"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $custom_fields = (array) $back_office_options[$admin_page.'_page_custom_fields'];
$item_custom_fields = (array) unserialize(htmlspecialchars_decode($_POST['custom_fields']));
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = do_shortcode($value); }
asort($custom_fields); $content = ''; foreach ($custom_fields as $key => $value) {
$field_value = (isset($item_custom_fields[$key]) ? $item_custom_fields[$key] : '');
if ((strlen($field_value) > 75) || (strstr($field_value, '
'))) { $rows = 3; } else { $rows = 1; }
$urls_fields[] = 'custom_field_'.$key; $applied_value = contact_form_data(array(0 => 'custom_field_'.$key, 'part' => 1, $attribute => (isset($_GET['id']) ? $_GET['id'] : 0)));
$content .= '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="custom_field_'.$key.'">'.htmlspecialchars($value).'</label></strong></th>
<td><textarea style="padding: 0 0.25em; '.($rows == 1 ? 'height: 1.75em; ' : '').'width: 75%;" name="custom_field_'.$key.'" id="custom_field_'.$key.'" rows="'.$rows.'" cols="75" onchange="update_links(this.form);">'.htmlspecialchars($field_value).'</textarea>
<span id="custom-field-'.str_replace('_', '-', $key).'-link">'.(((!strstr($applied_value, ' ')) && (substr($applied_value, 0, 4) == 'http')) ? '<a style="vertical-align: 25%;" '.$urls_fields_links_markup.' href="'.htmlspecialchars($applied_value).'">'.__('Link', 'contact-manager').'</a>' : '').'</span></td></tr>'; }
echo $content; if ($content == '') { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td>'.__('You have no custom field currently.', 'contact-manager').'</td></tr>'; } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="gift-module"<?php if (in_array('gift', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="gift"><strong><?php echo $modules[$admin_page]['gift']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="gift-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'gift'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="gift_download_url"><?php _e('Download URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="gift_download_url" id="gift_download_url" rows="1" cols="75" onchange="update_links(this.form);"><?php echo $_POST['gift_download_url']; ?></textarea> 
<span style="vertical-align: 25%;"><span id="gift-download-url-link"><?php $urls_fields[] = 'gift_download_url'; $url = htmlspecialchars(contact_form_data(array(0 => 'gift_download_url', 'part' => 1, $attribute => (isset($_GET['id']) ? $_GET['id'] : 0)))); if ($url != '') { echo '<a '.$urls_fields_links_markup.' href="'.$url.'">'.__('Link', 'contact-manager').'</a>'.(current_user_can('upload_files') ? ' | ' : ''); } ?></span>
<?php if (current_user_can('upload_files')) { echo '<a '.$urls_fields_links_markup.' href="media-new.php" title="'.__('After the upload, you will just need to copy and paste the URL of the file in this field.', 'contact-manager').'">'.__('Upload a file', 'contact-manager').'</a>'; } ?></span><br />
<span class="description"><?php _e('You can enter several URLs.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#multiple-urls"><?php _e('More informations', 'contact-manager'); ?></a> 
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#urls-encryption"><?php _e('How to encrypt a download URL?', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="gift_instructions"><?php _e('Instructions to the sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="gift_instructions" id="gift_instructions" rows="5" cols="75"><?php echo $_POST['gift_instructions']; ?></textarea>
<span class="description"><?php _e('You can offer a gift to senders.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#gift"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<?php if (!$is_category) { ?>
<div class="postbox" id="counters-module"<?php if (in_array('counters', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="counters"><strong><?php echo $modules[$admin_page]['counters']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity_per_sender"><?php _e('Maximum messages quantity per sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="maximum_messages_quantity_per_sender" id="maximum_messages_quantity_per_sender" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo (!is_numeric($_POST['maximum_messages_quantity_per_sender']) ? '' : $_POST['maximum_messages_quantity_per_sender']); ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank for an unlimited quantity.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="displays_count"><?php _e('Displays count', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="displays_count" id="displays_count" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo (int) $_POST['displays_count']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="messages_count"><?php _e('Messages count', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="messages_count" id="messages_count" rows="1" cols="25" <?php if (isset($_GET['id'])) { echo 'onkeyup="update_links(this.form);" '; } ?>onchange="<?php if (isset($_GET['id'])) { echo 'update_links(this.form); '; } ?>fill_form(this.form);"><?php echo (int) $_POST['messages_count']; ?></textarea>
<?php if (isset($_GET['id'])) { echo '<br /><a id="messages-count-link" style="text-decoration: none;'.($_POST['messages_count'] > 0 ? '' : ' display: none;').'" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-messages&amp;form_id='.$_GET['id'].'&amp;start_date=0">'.__('Display the messages', 'contact-manager').'</a>
<input type="hidden" name="submit" value="true" />
<input type="submit" class="button-secondary" name="count_messages" value="'.__('Re-count the messages', 'contact-manager').'" />
<input type="submit" class="button-secondary" name="count_messages_of_all_forms" value="'.__('Re-count the messages of all forms', 'contact-manager').'" />'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<div class="postbox" id="form-module"<?php if (in_array('form', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="form"><strong><?php echo $modules[$admin_page]['form']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="form-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'form'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="code"><?php _e('Code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="code" id="code" rows="15" cols="75"><?php echo $_POST['code']; ?></textarea>
<p class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#forms"><?php _e('How to display a form?', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#forms-creation"><?php _e('How to create a form?', 'contact-manager'); ?></a><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></p>
<p class="description" style="margin: 1.5em 0;">
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#input"><?php _e('Display a form field', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#textarea"><?php _e('Display a text area', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#select"><?php _e('Display a dropdown list', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#error"><?php _e('Display an error message', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#button"><?php _e('Display a submit button', 'contact-manager'); ?></a></p></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
<div id="error-messages-module"<?php if (in_array('error-messages', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="error-messages"><strong><?php echo $modules[$admin_page]['form']['modules']['error-messages']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="error-messages-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'error-messages'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unfilled_fields_message"><?php _e('Unfilled required fields', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unfilled_fields_message" id="unfilled_fields_message" rows="1" cols="75"><?php echo $_POST['unfilled_fields_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unfilled_field_message"><?php _e('Unfilled required field', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unfilled_field_message" id="unfilled_field_message" rows="1" cols="75"><?php echo $_POST['unfilled_field_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_fields_message"><?php _e('Invalid fields', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_fields_message" id="invalid_fields_message" rows="1" cols="75"><?php echo $_POST['invalid_fields_message']; ?></textarea><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_field_message"><?php _e('Invalid field', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_field_message" id="invalid_field_message" rows="1" cols="75"><?php echo $_POST['invalid_field_message']; ?></textarea><br />
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
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<div class="postbox" id="messages-registration-module"<?php if (in_array('messages-registration', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="messages-registration"><strong><?php echo $modules[$admin_page]['messages-registration']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="messages-registration-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'messages-registration'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="messages_registration_enabled"><?php _e('Save messages in the database', 'contact-manager'); ?></label></strong></th>
<td><select name="messages_registration_enabled" id="messages_registration_enabled">
<option value=""<?php $default_options_select_fields[] = 'messages_registration_enabled'; if ($_POST['messages_registration_enabled'] == '') { echo ' selected="selected"'; } ?> id="messages_registration_enabled_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['messages_registration_enabled'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['messages_registration_enabled'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php _e('You can save only the latest messages to ease your database.', 'contact-manager'); ?></span><br />
<a style="text-decoration: none;" <?php echo $ids_fields_links_markup; ?> href="admin.php?page=contact-manager-messages"><?php _e('Display the messages saved in the database', 'contact-manager'); ?></a></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity"><?php _e('Maximum messages quantity', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="maximum_messages_quantity" id="maximum_messages_quantity" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo ($_POST['maximum_messages_quantity'] === 'unlimited' ? 'i' : $_POST['maximum_messages_quantity']); ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Enter <em><strong>i</strong></em> for an unlimited quantity.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="message-confirmation-email-module"<?php if (in_array('message-confirmation-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="message-confirmation-email"><strong><?php echo $modules[$admin_page]['message-confirmation-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="message-confirmation-email-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'message-confirmation-email'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_sent"><?php _e('Send a message confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="message_confirmation_email_sent" id="message_confirmation_email_sent">
<option value=""<?php $default_options_select_fields[] = 'message_confirmation_email_sent'; if ($_POST['message_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="message_confirmation_email_sent_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
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
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="message-notification-email-module"<?php if (in_array('message-notification-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="message-notification-email"><strong><?php echo $modules[$admin_page]['message-notification-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="message-notification-email-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'message-notification-email'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_sent"><?php _e('Send a message notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="message_notification_email_sent" id="message_notification_email_sent">
<option value=""<?php $default_options_select_fields[] = 'message_notification_email_sent'; if ($_POST['message_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="message_notification_email_sent_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
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
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="autoresponders-module"<?php if (in_array('autoresponders', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="autoresponders"><strong><?php echo $modules[$admin_page]['autoresponders']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="autoresponders-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'autoresponders'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You must make some adjustments so that the subscription works with some autoresponders.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_to_autoresponder"><?php _e('Subscribe the sender to an autoresponder list', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_to_autoresponder" id="sender_subscribed_to_autoresponder">
<option value=""<?php $default_options_select_fields[] = 'sender_subscribed_to_autoresponder'; if ($_POST['sender_subscribed_to_autoresponder'] == '') { echo ' selected="selected"'; } ?> id="sender_subscribed_to_autoresponder_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_to_autoresponder'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_to_autoresponder'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder"><?php _e('Autoresponder', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_autoresponder" id="sender_autoresponder">
<?php include CONTACT_MANAGER_PATH.'libraries/autoresponders.php';
$autoresponder = do_shortcode($_POST['sender_autoresponder']);
$default_options_select_fields[] = 'sender_autoresponder'; echo '<option value=""'.($autoresponder == '' ? ' selected="selected"' : '').' id="sender_autoresponder_default_option">'.__('Default option', 'contact-manager').'</option>'."\n";
foreach ($autoresponders as $value) {
echo '<option value="'.$value.'"'.($autoresponder == $value ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder_list"><?php _e('List', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_autoresponder_list" id="sender_autoresponder_list" rows="1" cols="50"><?php echo $_POST['sender_autoresponder_list']; ?></textarea><br />
<span class="description"><?php _e('For most autoresponders, you must enter the list ID.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="registration-as-a-client-module"<?php if (in_array('registration-as-a-client', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="registration-as-a-client"><strong><?php echo $modules[$admin_page]['registration-as-a-client']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="registration-as-a-client-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'registration-as-a-client'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_as_a_client"><?php _e('Subscribe the sender as a client', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_as_a_client" id="sender_subscribed_as_a_client">
<option value=""<?php $default_options_select_fields[] = 'sender_subscribed_as_a_client'; if ($_POST['sender_subscribed_as_a_client'] == '') { echo ' selected="selected"'; } ?> id="sender_subscribed_as_a_client_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_as_a_client'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_as_a_client'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#registration-as-a-client"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (get_option('commerce_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."commerce_manager_clients_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_category_id" id="sender_client_category_id" onchange="fill_form(this.form);">
<option value=""<?php $default_options_select_fields[] = 'sender_client_category_id'; if ($_POST['sender_client_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_client_category_id_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="0"<?php if ($_POST['sender_client_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_client_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('commerce_data')) {
$ids_fields[] = 'sender_client_category_id';
$applied_value = ($_POST['sender_client_category_id'] == '' ? contact_form_category_data(array(0 => 'sender_client_category_id', 'id' => $_POST['category_id'])) : $_POST['sender_client_category_id']);
if ($applied_value == '') { $applied_value = commerce_data('clients_initial_category_id'); }
echo '<span id="sender-client-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_client_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_status" id="sender_client_status">
<option value=""<?php $default_options_select_fields[] = 'sender_client_status'; if ($_POST['sender_client_status'] == '') { echo ' selected="selected"'; } ?> id="sender_client_status_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="active"<?php if ($_POST['sender_client_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_client_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/commerce-manager/documentation/#client-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commerce_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="commerce_registration_confirmation_email_sent" id="commerce_registration_confirmation_email_sent">
<option value=""<?php $default_options_select_fields[] = 'commerce_registration_confirmation_email_sent'; if ($_POST['commerce_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="commerce_registration_confirmation_email_sent_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['commerce_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['commerce_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('commerce_data') ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=commerce-manager-client-area#registration-confirmation-email') : _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commerce_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="commerce_registration_notification_email_sent" id="commerce_registration_notification_email_sent">
<option value=""<?php $default_options_select_fields[] = 'commerce_registration_notification_email_sent'; if ($_POST['commerce_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="commerce_registration_notification_email_sent_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['commerce_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['commerce_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('commerce_data') ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=commerce-manager-client-area#registration-notification-email') : _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="registration-to-affiliate-program-module"<?php if (in_array('registration-to-affiliate-program', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="registration-to-affiliate-program"><strong><?php echo $modules[$admin_page]['registration-to-affiliate-program']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="registration-to-affiliate-program-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'registration-to-affiliate-program'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_to_affiliate_program"><?php _e('Subscribe the sender to affiliate program', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_to_affiliate_program" id="sender_subscribed_to_affiliate_program">
<option value=""<?php $default_options_select_fields[] = 'sender_subscribed_to_affiliate_program'; if ($_POST['sender_subscribed_to_affiliate_program'] == '') { echo ' selected="selected"'; } ?> id="sender_subscribed_to_affiliate_program_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_to_affiliate_program'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_to_affiliate_program'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#registration-to-affiliate-program"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (get_option('affiliation_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."affiliation_manager_affiliates_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_category_id" id="sender_affiliate_category_id" onchange="fill_form(this.form);">
<option value=""<?php $default_options_select_fields[] = 'sender_affiliate_category_id'; if ($_POST['sender_affiliate_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_affiliate_category_id_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="0"<?php if ($_POST['sender_affiliate_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_affiliate_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('affiliation_data')) {
$ids_fields[] = 'sender_affiliate_category_id';
$applied_value = ($_POST['sender_affiliate_category_id'] == '' ? contact_form_category_data(array(0 => 'sender_affiliate_category_id', 'id' => $_POST['category_id'])) : $_POST['sender_affiliate_category_id']);
if ($applied_value == '') { $applied_value = affiliation_data('affiliates_initial_category_id'); }
echo '<span id="sender-affiliate-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_affiliate_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_status" id="sender_affiliate_status">
<option value=""<?php $default_options_select_fields[] = 'sender_affiliate_status'; if ($_POST['sender_affiliate_status'] == '') { echo ' selected="selected"'; } ?> id="sender_affiliate_status_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="active"<?php if ($_POST['sender_affiliate_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_affiliate_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#affiliate-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_registration_confirmation_email_sent" id="affiliation_registration_confirmation_email_sent">
<option value=""<?php $default_options_select_fields[] = 'affiliation_registration_confirmation_email_sent'; if ($_POST['affiliation_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="affiliation_registration_confirmation_email_sent_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['affiliation_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['affiliation_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('affiliation_data') ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=affiliation-manager#registration-confirmation-email') : _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_registration_notification_email_sent" id="affiliation_registration_notification_email_sent">
<option value=""<?php $default_options_select_fields[] = 'affiliation_registration_notification_email_sent'; if ($_POST['affiliation_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="affiliation_registration_notification_email_sent_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['affiliation_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['affiliation_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('affiliation_data') ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=affiliation-manager#registration-notification-email') : _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="membership-module"<?php if (in_array('membership', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="membership"><strong><?php echo $modules[$admin_page]['membership']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="membership-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'membership'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_to_members_areas"><?php _e('Subscribe the sender to a member area', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_to_members_areas" id="sender_subscribed_to_members_areas">
<option value=""<?php $default_options_select_fields[] = 'sender_subscribed_to_members_areas'; if ($_POST['sender_subscribed_to_members_areas'] == '') { echo ' selected="selected"'; } ?> id="sender_subscribed_to_members_areas_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_to_members_areas'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_to_members_areas'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#membership"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas"><?php _e('Members areas', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_members_areas" id="sender_members_areas" rows="1" cols="50" onkeyup="update_links(this.form); fill_form(this.form);" onchange="update_links(this.form); fill_form(this.form);"><?php echo $_POST['sender_members_areas']; ?></textarea>
<?php if (function_exists('membership_data')) {
$ids_fields[] = 'sender_members_areas';
$members_areas = contact_form_data(array(0 => 'sender_members_areas', $attribute => (isset($_GET['id']) ? $_GET['id'] : 0)));
echo '<span class="description" style="vertical-align: 25%;" id="sender-members-areas-description">'.contact_manager_pages_field_description('sender_members_areas', $members_areas).'</span>';
$links = contact_manager_pages_field_links($back_office_options, 'sender_members_areas', $members_areas); echo '<span id="sender-members-areas-links">'.$links.'</span>';
$string = '-member-area&amp;id='.$members_areas; $url = 'admin.php?page=membership-manager'.(strstr($links, $string) ? $string : ''); } ?><br />
<span class="description"><?php _e('You can enter several members areas IDs. Separate them with commas.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas_modifications"><?php _e('Automatic modifications', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 50%;" name="sender_members_areas_modifications" id="sender_members_areas_modifications" rows="2" cols="50" onchange="fill_form(this.form);"><?php echo $_POST['sender_members_areas_modifications']; ?></textarea>
<span class="description"><?php _e('You can offer a temporary access, and automatically modify the list of members areas to which the member can access when a certain date is reached.', 'contact-manager'); ?>
 <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/membership-manager/documentation/#members-areas-modifications"><?php _e('More informations', 'contact-manager'); ?></a><br />
<?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<?php if (get_option('membership_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."membership_manager_members_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_category_id" id="sender_member_category_id" onchange="fill_form(this.form);">
<option value=""<?php $default_options_select_fields[] = 'sender_member_category_id'; if ($_POST['sender_member_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_member_category_id_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="0"<?php if ($_POST['sender_member_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_member_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('membership_data')) {
$ids_fields[] = 'sender_member_category_id';
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', ($_POST['sender_members_areas'] === '' ? contact_form_category_data(array(0 => 'sender_members_areas', 'id' => $_POST['category_id'])) : $_POST['sender_members_areas']), 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
$applied_value = ($_POST['sender_member_category_id'] == '' ? contact_form_category_data(array(0 => 'sender_member_category_id', 'id' => $_POST['category_id'])) : $_POST['sender_member_category_id']);
if ($applied_value == '') { $applied_value = member_area_data('members_initial_category_id'); }
echo '<span id="sender-member-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_member_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_status" id="sender_member_status">
<option value=""<?php $default_options_select_fields[] = 'sender_member_status'; if ($_POST['sender_member_status'] == '') { echo ' selected="selected"'; } ?> id="sender_member_status_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="active"<?php if ($_POST['sender_member_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_member_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/membership-manager/documentation/#member-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="membership_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="membership_registration_confirmation_email_sent" id="membership_registration_confirmation_email_sent">
<option value=""<?php $default_options_select_fields[] = 'membership_registration_confirmation_email_sent'; if ($_POST['membership_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="membership_registration_confirmation_email_sent_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['membership_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['membership_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('membership_data') ? printf(str_replace('<a', '<a id="membership-registration-confirmation-email-sent-link" '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), $url.'#registration-confirmation-email') : _e('You can configure this email through the interface of Membership Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="membership_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="membership_registration_notification_email_sent" id="membership_registration_notification_email_sent">
<option value=""<?php $default_options_select_fields[] = 'membership_registration_notification_email_sent'; if ($_POST['membership_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="membership_registration_notification_email_sent_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['membership_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['membership_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('membership_data') ? printf(str_replace('<a', '<a id="membership-registration-notification-email-sent-link" '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), $url.'#registration-notification-email') : _e('You can configure this email through the interface of Membership Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="wordpress-module"<?php if (in_array('wordpress', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="wordpress"><strong><?php echo $modules[$admin_page]['wordpress']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="wordpress-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'wordpress'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_subscribed_as_a_user"><?php _e('Subscribe the sender as a user', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_subscribed_as_a_user" id="sender_subscribed_as_a_user">
<option value=""<?php $default_options_select_fields[] = 'sender_subscribed_as_a_user'; if ($_POST['sender_subscribed_as_a_user'] == '') { echo ' selected="selected"'; } ?> id="sender_subscribed_as_a_user_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['sender_subscribed_as_a_user'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['sender_subscribed_as_a_user'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#wordpress"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_user_role"><?php _e('Role', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_user_role" id="sender_user_role">
<option value=""<?php $default_options_select_fields[] = 'sender_user_role'; if ($_POST['sender_user_role'] == '') { echo ' selected="selected"'; } ?> id="sender_user_role_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<?php foreach (contact_manager_users_roles() as $role => $name) {
echo '<option value="'.$role.'"'.($_POST['sender_user_role'] == $role ? ' selected="selected"' : '').'>'.$name.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="custom-instructions-module"<?php if (in_array('custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="custom-instructions"><strong><?php echo $modules[$admin_page]['custom-instructions']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="custom-instructions-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'custom-instructions'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_custom_instructions_executed"><?php _e('Execute custom instructions', 'contact-manager'); ?></label></strong></th>
<td><select name="message_custom_instructions_executed" id="message_custom_instructions_executed">
<option value=""<?php $default_options_select_fields[] = 'message_custom_instructions_executed'; if ($_POST['message_custom_instructions_executed'] == '') { echo ' selected="selected"'; } ?> id="message_custom_instructions_executed_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['message_custom_instructions_executed'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['message_custom_instructions_executed'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_custom_instructions" id="message_custom_instructions" rows="10" cols="75"><?php echo $_POST['message_custom_instructions']; ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the sending of the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span><br />
<span class="description"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="affiliation-module"<?php if (in_array('affiliation', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="affiliation"><strong><?php echo $modules[$admin_page]['affiliation']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;" id="affiliation-module-description"><?php echo contact_manager_pages_module_description($back_office_options, 'affiliation'); ?></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_enabled"><?php _e('Use affiliation', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_enabled" id="affiliation_enabled">
<option value=""<?php $default_options_select_fields[] = 'affiliation_enabled'; if ($_POST['affiliation_enabled'] == '') { echo ' selected="selected"'; } ?> id="affiliation_enabled_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['affiliation_enabled'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['affiliation_enabled'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php _e('Select <em>No</em> allows you to disable the award of commissions.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
<div id="level-1-commission-module"<?php if (in_array('level-1-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-1-commission"><strong><?php echo $modules[$admin_page]['affiliation']['modules']['level-1-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 1 commission is awarded to the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission_amount" id="commission_amount" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo $_POST['commission_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span> 
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div>
<div id="level-2-commission-module"<?php if (in_array('level-2-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-2-commission"><strong><?php echo $modules[$admin_page]['affiliation']['modules']['level-2-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 2 commission is awarded to the referrer of the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_enabled"><?php _e('Award a level 2 commission', 'contact-manager'); ?></label></strong></th>
<td><select name="commission2_enabled" id="commission2_enabled">
<option value=""<?php $default_options_select_fields[] = 'commission2_enabled'; if ($_POST['commission2_enabled'] == '') { echo ' selected="selected"'; } ?> id="commission2_enabled_default_option"><?php _e('Default option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($_POST['commission2_enabled'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($_POST['commission2_enabled'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission2_amount" id="commission2_amount" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo $_POST['commission2_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span> 
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank to apply the default option.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<p class="submit"><input type="submit" class="button-primary" name="submit" id="submit" value="<?php (isset($_GET['id']) ? _e('Save Changes', 'contact-manager') : ($is_category ? _e('Save Category', 'contact-manager') : _e('Save Form', 'contact-manager'))); ?>" /></p>
<?php if ($is_category) { $module = 'form-category-page'; } else { $module = 'form-page'; }
contact_manager_pages_module($back_office_options, $module, $undisplayed_modules); ?>
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

<?php $fields = array(); $default_options = array();
foreach ($tables[$table_slug] as $key => $value) { $fields[] = $key; }
foreach ($custom_fields as $key => $value) { $fields[] = 'custom_field_'.$key; }
echo 'fields = []; default_values = [];'."\n";
foreach ($fields as $field) {
if (!in_array($field, array('id', 'category_id', 'date', 'date_utc', 'custom_fields', 'name'))) {
$default_options[$field] = contact_form_category_data(array(0 => $field, 'formatting' => 'no', 'id' => $_POST['category_id']));
if (($default_options[$field] === 'unlimited') && ($field == 'maximum_messages_quantity')) { $default_options[$field] = 'i'; }
echo 'fields.push("'.$field.'"); default_values["'.$field.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $default_options[$field]).'";'."\n"; } }
echo 'for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
if ((element) && ((element.type == "text") || (element.type == "textarea"))) {
element.setAttribute("data-default", default_values[fields[i]]); if (element.value === "") { element.setAttribute("data-empty", "yes"); element.style.color = "#a0a0a0"; element.value = default_values[fields[i]]; }
if (element.hasAttribute("onfocus")) { string = " "+element.getAttribute("onfocus"); } else { string = ""; }
element.setAttribute("onfocus", "this.setAttribute(\'data-focused\', \'yes\'); if (this.getAttribute(\'data-empty\') == \'yes\') { this.style.color = \'\'; this.value = \'\'; }"+string);
events = ["onblur","onchange"]; for (j = 0; j < 2; j++) {
if (element.hasAttribute(events[j])) { string = " "+element.getAttribute(events[j]); } else { string = ""; }
element.setAttribute(events[j], "if (this.getAttribute(\'data-focused\') == \'yes\') { this.setAttribute(\'data-focused\', \'no\'); if (this.value === \'\') { this.setAttribute(\'data-empty\', \'yes\'); this.style.color = \'#a0a0a0\'; this.value = this.getAttribute(\'data-default\'); } else { this.setAttribute(\'data-empty\', \'no\'); } }"+string); } } }'."\n";

$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', ($_POST['sender_members_areas'] === '' ? $default_options['sender_members_areas'] : $_POST['sender_members_areas']), 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
foreach (array('category_id', 'status') as $field) {
if (($default_options['sender_client_'.$field] == '') && (function_exists('commerce_data'))) { $default_options['sender_client_'.$field] = commerce_data('clients_initial_'.$field); }
if (($default_options['sender_affiliate_'.$field] == '') && (function_exists('affiliation_data'))) { $default_options['sender_affiliate_'.$field] = affiliation_data('affiliates_initial_'.$field); }
if (($default_options['sender_member_'.$field] == '') && (function_exists('member_area_data'))) { $default_options['sender_member_'.$field] = member_area_data('members_initial_'.$field); } }
foreach (array('confirmation', 'notification') as $action) {
if (($default_options['commerce_registration_'.$action.'_email_sent'] == '') && (function_exists('commerce_data'))) { $default_options['commerce_registration_'.$action.'_email_sent'] = commerce_data('registration_'.$action.'_email_sent'); }
if (($default_options['affiliation_registration_'.$action.'_email_sent'] == '') && (function_exists('affiliation_data'))) { $default_options['affiliation_registration_'.$action.'_email_sent'] = affiliation_data('registration_'.$action.'_email_sent'); }
if (($default_options['membership_registration_'.$action.'_email_sent'] == '') && (function_exists('member_area_data'))) { $default_options['membership_registration_'.$action.'_email_sent'] = member_area_data('registration_'.$action.'_email_sent'); } }

foreach ($default_options_select_fields as $field) { if (isset($default_options[$field])) { echo 'element = document.getElementById("'.$field.'_default_option"); if (element) { element.innerHTML = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), contact_manager_pages_selector_default_option_content($field, $default_options[$field])).'"; }'."\n"; } } ?>

<?php $modules_list = array(); foreach ($modules[$admin_page] as $key => $value) {
$modules_list[] = $key; if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) { $modules_list[] = $module_key; } } }
echo 'fill_form_call_number = 0;
function fill_form(form) {
fill_form_call_number += 1;
data = {}; fields = '.json_encode($fields).';
for (i = 0, n = fields.length; i < n; i++) {
if ((form[fields[i]]) && ((form[fields[i]].getAttribute("data-empty") != "yes") || (form[fields[i]].getAttribute("data-focused") == "yes"))) {
if (form[fields[i]].type != "checkbox") { data[fields[i]] = form[fields[i]].value; }
else { if (form[fields[i]].checked == true) { data[fields[i]] = "yes"; } } } }
data["default_options_select_fields"] = '.json_encode($default_options_select_fields).';
ids_fields = '.json_encode($ids_fields).'; data["ids_fields"] = ids_fields;
data["fill_form_call_number"] = fill_form_call_number;
jQuery.post("'.CONTACT_MANAGER_URL.'index.php?action=fill-form&page='.$_GET['page'].(isset($_GET['id']) ? '&id='.$_GET['id'] : '').'&time='.$current_time.'&key='.md5(AUTH_KEY).'", data, function(data) {
if (data["fill_form_call_number"] == fill_form_call_number) {
for (i = 0, n = fields.length; i < n; i++) {
if (form[fields[i]]) {
if ((typeof data[fields[i]] != "undefined") && (fields[i] != document.activeElement.name)) {
if (form[fields[i]].type != "checkbox") { form[fields[i]].value = data[fields[i]]; }
else { if (data[fields[i]] == "yes") { form[fields[i]].checked = true; } else { form[fields[i]].checked = false; } } }
if (form[fields[i]].type == "select-one") {
var element = document.getElementById(fields[i]+"_default_option");
if ((element) && (typeof data[fields[i]+"_default_option_content"] != "undefined")) { element.innerHTML = data[fields[i]+"_default_option_content"]; } }
else if ((typeof data[fields[i]+"_default_value"] != "undefined") && (form[fields[i]].hasAttribute("data-default"))) {
form[fields[i]].setAttribute("data-default", data[fields[i]+"_default_value"]);
if (fields[i] != document.activeElement.name) {
if (form[fields[i]].value === "") { form[fields[i]].setAttribute("data-empty", "yes"); form[fields[i]].style.color = "#a0a0a0"; form[fields[i]].value = data[fields[i]+"_default_value"]; }
else { form[fields[i]].setAttribute("data-empty", "no"); form[fields[i]].style.color = ""; } } }
var element = document.getElementById(fields[i]+"_error"); if (element) {
if (typeof data[fields[i]+"_error"] == "undefined") { element.innerHTML = ""; }
else { element.innerHTML = data[fields[i]+"_error"]; } } } }
var strings = ["description","links"];
for (i = 0, n = ids_fields.length; i < n; i++) { for (j = 0; j < 2; j++) {
var key = ids_fields[i]+"_"+strings[j];
var element = document.getElementById(key.replace(/[_]/g, "-"));
if ((element) && (typeof data[key] != "undefined")) { element.innerHTML = data[key]; } } }
var modules = '.json_encode($modules_list).';
for (i = 0, n = modules.length; i < n; i++) {
var key = modules[i].replace(/[-]/g, "_")+"_module_description";
var element = document.getElementById(modules[i]+"-module-description");
if ((element) && (typeof data[key] != "undefined")) { element.innerHTML = data[key]; } }
update_links(form); jQuery(".noscript").css("display", "none"); } }, "json"); }'."\n"; ?>

<?php echo 'function update_links(form) {
var fields = '.json_encode($urls_fields).';
for (i = 0, n = fields.length; i < n; i++) {
var element = document.getElementById(fields[i].replace(/[_]/g, "-")+"-link");
if (element) {
if (fields[i].substr(0, 13) == "custom_field_") {
var url = form[fields[i]].value; if ((url.indexOf(" ") >= 0) || (url.substr(0, 4) != "http")) { url = ""; } }
else { var urls = form[fields[i]].value.split(","); var url = format_url(urls[0].replace(/[ ]/g, "")); }
if (url == "") { element.innerHTML = ""; }
else if (fields[i] == "gift_download_url") { element.innerHTML = \'<a '.$urls_fields_links_markup.' href="\'+url.replace(/[&]/g, "&amp;")+\'">'.__('Link', 'contact-manager').'</a>'.(current_user_can('upload_files') ? ' | ' : '').'\'; }
else { element.innerHTML = \'<a style="vertical-align: 25%;" '.$urls_fields_links_markup.' href="\'+url.replace(/[&]/g, "&amp;")+\'">'.__('Link', 'contact-manager').'</a>\'; } } }
'.(((!isset($_GET['id'])) || ($is_category)) ? '' : 'var element = document.getElementById("messages-count-link");
if (element) {
var value = form["messages_count"].value.replace(/[^0-9]/g, ""); if (value == "") { value = 0; }
if (value > 0) { element.style.display = ""; } else { element.style.display = "none"; } }').'
'.(!function_exists('membership_data') ? '' : 'var field = form["sender_members_areas"]; if (field) {
var url = "admin.php?page=membership-manager";
var element = document.getElementById("sender-members-areas-links");
if ((field.value != "") && (field.value != 0) && (element.innerHTML.indexOf("id="+field.value) >= 0)) { url += "-member-area&id="+field.value; }
var actions = ["confirmation","notification"]; for (i = 0; i < 2; i++) {
var element = document.getElementById("membership-registration-"+actions[i]+"-email-sent-link");
if (element) { element.href = url+"#registration-"+actions[i]+"-email"; } } }').' }'."\n"; ?>

<?php $required_fields = array('name');
echo 'function validate_form(form) {
var error = false;
if (form.update_back_office_options.getAttribute("data-clicked") != "yes") {
var fields = '.json_encode($required_fields).';
for (i = 0, n = fields.length; i < n; i++) {
var element = document.getElementById(fields[i]+"_error");
if ((form[fields[i]].value === "") || ((element) && (element.innerHTML != ""))) {
document.getElementById(fields[i].replace(/[_]/g, "-")+"-th").style.color = "#c00000";
form[fields[i]].placeholder = "'.__('This field is required.', 'contact-manager').'";
if (!error) { form[fields[i]].focus(); } error = true; }
else { document.getElementById(fields[i].replace(/[_]/g, "-")+"-th").style.color = ""; } } }
if (!error) { for (i = 0, n = form.length; i < n; i++) { if (form[i].getAttribute("data-empty") == "yes") { form[i].value = ""; } } }
return !error; }'."\n"; ?>
</script>
<?php }