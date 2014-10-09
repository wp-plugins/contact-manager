<?php global $wpdb; $error = '';
$back_office_options = (array) get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($back_office_options));
$admin_page = 'options';

if ((isset($_GET['action'])) && (($_GET['action'] == 'reset') || ($_GET['action'] == 'uninstall'))) {
$for = (((isset($_GET['for'])) && (is_multisite()) && (current_user_can('manage_network'))) ? $_GET['for'] : 'single');
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!contact_manager_user_can($back_office_options, 'manage')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else { if ($_GET['action'] == 'reset') { reset_contact_manager(); } else { uninstall_contact_manager($for); } } } ?>
<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) {
echo '<div class="updated"><p><strong>'.($_GET['action'] == 'reset' ? __('Options reset.', 'contact-manager') : __('Options and tables deleted.', 'contact-manager')).'</strong></p></div>
<script type="text/javascript">setTimeout(\'window.location = "'.($_GET['action'] == 'reset' ? 'admin.php?page=contact-manager' : ($for == 'network' ? 'network/' : '').'plugins.php').'"\', 2000);</script>'; } ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php if (!isset($_POST['submit'])) { ?>
<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<div class="alignleft actions">
<p><strong style="color: #c00000;"><?php if ($_GET['action'] == 'reset') { _e('Do you really want to reset the options of Contact Manager?', 'contact-manager'); }
elseif ($for == 'network') { _e('Do you really want to permanently delete the options and tables of Contact Manager for all sites in this network?', 'contact-manager'); }
else { _e('Do you really want to permanently delete the options and tables of Contact Manager?', 'contact-manager'); } ?></strong> 
<input type="submit" class="button-secondary" name="submit" id="submit" value="<?php _e('Yes', 'contact-manager'); ?>" /></p>
</div>
</form><?php } ?>
</div>
</div><?php }

else {
foreach (array('admin-pages.php', 'initial-options.php') as $file) { include CONTACT_MANAGER_PATH.$file; }
$other_options = array(
'code',
'form_submission_custom_instructions',
'message_confirmation_email_body',
'message_custom_instructions',
'message_notification_email_body',
'message_removal_custom_instructions');
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!contact_manager_user_can($back_office_options, 'manage')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes(html_entity_decode(str_replace(array('&nbsp;', '&#91;', '&#93;'), array(' ', '&amp;#91;', '&amp;#93;'), $value))); } }
$back_office_options = update_contact_manager_back_office($back_office_options, 'options');
include CONTACT_MANAGER_PATH.'includes/fill-form.php'; } }
if (!isset($options)) { $options = (array) get_option('contact_manager'); }

foreach ($options as $key => $value) {
if (is_string($value)) { $options[$key] = htmlspecialchars($value); } }
$undisplayed_modules = (array) $back_office_options['options_page_undisplayed_modules'];
foreach (array('ids_fields', 'urls_fields') as $variable) { $$variable = array(); }
if (function_exists('commerce_data')) { $currency_code = commerce_data('currency_code'); }
else { $commerce_manager_options = array_merge((array) get_option('commerce_manager'), (array) get_option('commerce_manager_client_area'));
$currency_code = (isset($commerce_manager_options['currency_code']) ? do_shortcode($commerce_manager_options['currency_code']) : ''); } ?>

<div class="wrap">
<div id="poststuff" style="padding-top: 0;">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) { echo '<div class="updated"><p><strong>'.__('Settings saved.', 'contact-manager').'</strong></p></div>'; } ?>
<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>" onsubmit="return validate_form(this);">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<p class="description"><?php _e('You can reset an option by leaving the corresponding field blank.', 'contact-manager'); ?></p>
<?php contact_manager_pages_summary($back_office_options); ?>

<div class="postbox" id="automatic-display-module"<?php if (in_array('automatic-display', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="automatic-display"><strong><?php echo $modules['options']['automatic-display']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="automatic_display_enabled" id="automatic_display_enabled" value="yes"<?php if ($options['automatic_display_enabled'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Enable automatic display', 'contact-manager'); ?></label> 
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#automatic-display"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="automatic_display_only_on_single_post_pages" id="automatic_display_only_on_single_post_pages" value="yes"<?php if ($options['automatic_display_only_on_single_post_pages'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Only on single post pages', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="automatic_display_location"><?php _e('Location', 'contact-manager'); ?></label></strong></th>
<td><select name="automatic_display_location" id="automatic_display_location">
<option value="top"<?php if ($options['automatic_display_location'] == 'top') { echo ' selected="selected"'; } ?>><?php _e('On the top of posts', 'contact-manager'); ?></option>
<option value="bottom"<?php if ($options['automatic_display_location'] == 'bottom') { echo ' selected="selected"'; } ?>><?php _e('On the bottom of posts', 'contact-manager'); ?></option>
<option value="top, bottom"<?php if ($options['automatic_display_location'] == 'top, bottom') { echo ' selected="selected"'; } ?>><?php _e('On the top and bottom of posts', 'contact-manager'); ?></option>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="automatic_display_form_id"><?php _e('Form ID', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="automatic_display_form_id" id="automatic_display_form_id" rows="1" cols="25" onkeyup="if (this.value != '') { fill_form(this.form); }" onchange="fill_form(this.form);"><?php echo $options['automatic_display_form_id']; ?></textarea>
<span class="description" style="vertical-align: 25%;" id="automatic-display-form-id-description"><?php echo contact_manager_pages_field_description('automatic_display_form_id', $options['automatic_display_form_id']); ?></span>
<span id="automatic-display-form-id-links"><?php $ids_fields[] = 'automatic_display_form_id'; echo contact_manager_pages_field_links($back_office_options, 'automatic_display_form_id', $options['automatic_display_form_id']); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="automatic_display_maximum_forms_quantity"><?php _e('Maximum quantity of forms displayed per page', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="automatic_display_maximum_forms_quantity" id="automatic_display_maximum_forms_quantity" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo ($options['automatic_display_maximum_forms_quantity'] === 'unlimited' ? '' : $options['automatic_display_maximum_forms_quantity']); ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Leave this field blank for an unlimited quantity.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="forms-module"<?php if (in_array('forms', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="forms"><strong><?php echo $modules['options']['forms']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="code"><?php _e('Code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="code" id="code" rows="15" cols="75"><?php echo htmlspecialchars(get_option('contact_manager_code')); ?></textarea>
<p class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#forms"><?php _e('How to display a form?', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#forms-creation"><?php _e('How to create a form?', 'contact-manager'); ?></a></p>
<p class="description" style="margin: 1.5em 0;">
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#input"><?php _e('Display a form field', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#textarea"><?php _e('Display a text area', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#select"><?php _e('Display a dropdown list', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#error"><?php _e('Display an error message', 'contact-manager'); ?></a><br />
<a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#button"><?php _e('Display a submit button', 'contact-manager'); ?></a></p></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
<div id="captcha-module"<?php if (in_array('captcha', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="captcha"><strong><?php echo $modules['options']['forms']['modules']['captcha']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#captcha"><?php _e('How to display a CAPTCHA?', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="default_captcha_type"><?php _e('Default type', 'contact-manager'); ?></label></strong></th>
<td><select name="default_captcha_type" id="default_captcha_type">
<?php include CONTACT_MANAGER_PATH.'libraries/captchas.php';
$captcha_type = do_shortcode($options['default_captcha_type']);
asort($captchas_types);
foreach ($captchas_types as $key => $value) {
echo '<option value="'.$key.'"'.($captcha_type == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#captcha"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="default_recaptcha_theme"><?php _e('Default reCAPTCHA theme', 'contact-manager'); ?></label></strong></th>
<td><select name="default_recaptcha_theme" id="default_recaptcha_theme">
<?php include CONTACT_MANAGER_PATH.'libraries/captchas.php';
$recaptcha_theme = do_shortcode($options['default_recaptcha_theme']);
asort($recaptcha_themes);
foreach ($recaptcha_themes as $key => $value) {
echo '<option value="'.$key.'"'.($recaptcha_theme == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#recaptcha"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="recaptcha_public_key"><?php _e('reCAPTCHA public key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="recaptcha_public_key" id="recaptcha_public_key" rows="1" cols="50"><?php echo $options['recaptcha_public_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#recaptcha"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="recaptcha_private_key"><?php _e('reCAPTCHA private key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="recaptcha_private_key" id="recaptcha_private_key" rows="1" cols="50"><?php echo $options['recaptcha_private_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#recaptcha"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="error-messages-module"<?php if (in_array('error-messages', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="error-messages"><strong><?php echo $modules['options']['forms']['modules']['error-messages']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#error"><?php _e('How to display an error message?', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unfilled_fields_message"><?php _e('Unfilled required fields', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unfilled_fields_message" id="unfilled_fields_message" rows="1" cols="75"><?php echo $options['unfilled_fields_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unfilled_field_message"><?php _e('Unfilled required field', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unfilled_field_message" id="unfilled_field_message" rows="1" cols="75"><?php echo $options['unfilled_field_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_fields_message"><?php _e('Invalid fields', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_fields_message" id="invalid_fields_message" rows="1" cols="75"><?php echo $options['invalid_fields_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_field_message"><?php _e('Invalid field', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_field_message" id="invalid_field_message" rows="1" cols="75"><?php echo $options['invalid_field_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_email_address_message"><?php _e('Invalid email address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_email_address_message" id="invalid_email_address_message" rows="1" cols="75"><?php echo $options['invalid_email_address_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="invalid_captcha_message"><?php _e('Invalid CAPTCHA', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="invalid_captcha_message" id="invalid_captcha_message" rows="1" cols="75"><?php echo $options['invalid_captcha_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="failed_upload_message"><?php _e('Failed upload', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="failed_upload_message" id="failed_upload_message" rows="1" cols="75"><?php echo $options['failed_upload_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="too_large_file_message"><?php _e('Too large file', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="too_large_file_message" id="too_large_file_message" rows="1" cols="75"><?php echo $options['too_large_file_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="unauthorized_extension_message"><?php _e('Unauthorized extension', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="unauthorized_extension_message" id="unauthorized_extension_message" rows="1" cols="75"><?php echo $options['unauthorized_extension_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity_reached_message"><?php _e('Maximum messages quantity reached', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="maximum_messages_quantity_reached_message" id="maximum_messages_quantity_reached_message" rows="1" cols="75"><?php echo $options['maximum_messages_quantity_reached_message']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<div class="postbox" id="messages-registration-module"<?php if (in_array('messages-registration', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="messages-registration"><strong><?php echo $modules['options']['messages-registration']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="messages_registration_enabled" id="messages_registration_enabled" value="yes"<?php if ($options['messages_registration_enabled'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Save messages in the database', 'contact-manager'); ?></label><br />
<a style="text-decoration: none;" <?php echo $ids_fields_links_markup; ?> href="admin.php?page=contact-manager-messages"><?php _e('Display the messages saved in the database', 'contact-manager'); ?></a></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="maximum_messages_quantity"><?php _e('Maximum messages quantity', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="maximum_messages_quantity" id="maximum_messages_quantity" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo ($options['maximum_messages_quantity'] === 'unlimited' ? '' : $options['maximum_messages_quantity']); ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('You can save only the latest messages to ease your database.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank for an unlimited quantity.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="urls-encryption-module"<?php if (in_array('urls-encryption', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="urls-encryption"><strong><?php echo $modules['options']['urls-encryption']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You can encrypt the download URLs.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#urls-encryption"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="encrypted_urls_validity_duration"><?php _e('Validity duration', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="encrypted_urls_validity_duration" id="encrypted_urls_validity_duration" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo $options['encrypted_urls_validity_duration']; ?></textarea> <span style="vertical-align: 25%;"><?php _e('hours', 'contact-manager'); ?></span>
<span class="description" style="vertical-align: 25%;"><?php _e('Encrypted URLs must have a limited validity duration.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="encrypted_urls_key"><?php _e('Encryption key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="encrypted_urls_key" id="encrypted_urls_key" rows="1" cols="50"><?php echo $options['encrypted_urls_key']; ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Enter a difficult-to-guess string of characters.', 'contact-manager'); ?><br />
<?php _e('Leave this field blank to automatically generate a new key.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="message-confirmation-email-module"<?php if (in_array('message-confirmation-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="message-confirmation-email"><strong><?php echo $modules['options']['message-confirmation-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_confirmation_email_sent" id="message_confirmation_email_sent" value="yes"<?php if ($options['message_confirmation_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Send a message confirmation email', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_sender" id="message_confirmation_email_sender" rows="1" cols="75"><?php echo $options['message_confirmation_email_sender']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_receiver" id="message_confirmation_email_receiver" rows="1" cols="75"><?php echo $options['message_confirmation_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_subject" id="message_confirmation_email_subject" rows="1" cols="75"><?php echo $options['message_confirmation_email_subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_confirmation_email_body" id="message_confirmation_email_body" rows="15" cols="75"><?php echo htmlspecialchars(get_option('contact_manager_message_confirmation_email_body')); ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="message-notification-email-module"<?php if (in_array('message-notification-email', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="message-notification-email"><strong><?php echo $modules['options']['message-notification-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_notification_email_sent" id="message_notification_email_sent" value="yes"<?php if ($options['message_notification_email_sent'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Send a message notification email', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_sender" id="message_notification_email_sender" rows="1" cols="75"><?php echo $options['message_notification_email_sender']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_receiver" id="message_notification_email_receiver" rows="1" cols="75"><?php echo $options['message_notification_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_subject" id="message_notification_email_subject" rows="1" cols="75"><?php echo $options['message_notification_email_subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_notification_email_body" id="message_notification_email_body" rows="15" cols="75"><?php echo htmlspecialchars(get_option('contact_manager_message_notification_email_body')); ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="autoresponders-module"<?php if (in_array('autoresponders', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="autoresponders"><strong><?php echo $modules['options']['autoresponders']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You must make some adjustments so that the subscription works with some autoresponders.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_autoresponder" id="sender_subscribed_to_autoresponder" value="yes"<?php if ($options['sender_subscribed_to_autoresponder'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Subscribe the sender to an autoresponder list', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder"><?php _e('Autoresponder', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_autoresponder" id="sender_autoresponder">
<?php include CONTACT_MANAGER_PATH.'libraries/autoresponders.php';
$autoresponder = do_shortcode($options['sender_autoresponder']);
foreach ($autoresponders as $value) {
echo '<option value="'.$value.'"'.($autoresponder == $value ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder_list"><?php _e('List', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_autoresponder_list" id="sender_autoresponder_list" rows="1" cols="50"><?php echo $options['sender_autoresponder_list']; ?></textarea><br />
<span class="description"><?php _e('For most autoresponders, you must enter the list ID.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="autoresponders-integration-module"<?php if (in_array('autoresponders-integration', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="autoresponders-integration"><strong><?php echo $modules['options']['autoresponders-integration']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php if (function_exists('commerce_data')) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=commerce-manager#autoresponders-integration"><?php _e('Click here to configure the options of Commerce Manager.', 'contact-manager'); ?></a></span></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You must make some adjustments so that the subscription works with some autoresponders.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
</tbody></table>
<div id="aweber-module"<?php if (in_array('aweber', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="aweber"><strong><?php echo $modules['options']['autoresponders-integration']['modules']['aweber']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#aweber"><?php _e('Click here to read the instructions for integration.', 'contact-manager'); ?></a></span></td></tr>
</tbody></table>
</div>
<div id="cybermailing-module"<?php if (in_array('cybermailing', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="cybermailing"><strong><?php echo $modules['options']['autoresponders-integration']['modules']['cybermailing']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You have no adjustment to make so that the subscription works with CyberMailing.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
</div>
<div id="getresponse-module"<?php if (in_array('getresponse', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="getresponse"><strong><?php echo $modules['options']['autoresponders-integration']['modules']['getresponse']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="getresponse_api_key"><?php _e('API key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="getresponse_api_key" id="getresponse_api_key" rows="1" cols="50"><?php echo $options['getresponse_api_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#getresponse"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="mailchimp-module"<?php if (in_array('mailchimp', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="mailchimp"><strong><?php echo $modules['options']['autoresponders-integration']['modules']['mailchimp']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="mailchimp_api_key"><?php _e('API key', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="mailchimp_api_key" id="mailchimp_api_key" rows="1" cols="50"><?php echo $options['mailchimp_api_key']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#mailchimp"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="sg-autorepondeur-module"<?php if (in_array('sg-autorepondeur', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="sg-autorepondeur"><strong><?php echo $modules['options']['autoresponders-integration']['modules']['sg-autorepondeur']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sg_autorepondeur_account_id"><?php _e('Account ID', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="sg_autorepondeur_account_id" id="sg_autorepondeur_account_id" rows="1" cols="25"><?php echo $options['sg_autorepondeur_account_id']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#sg-autorepondeur"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sg_autorepondeur_activation_code"><?php _e('Activation code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sg_autorepondeur_activation_code" id="sg_autorepondeur_activation_code" rows="1" cols="50"><?php echo $options['sg_autorepondeur_activation_code']; ?></textarea> 
<span class="description" style="vertical-align: 25%;"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#sg-autorepondeur"><?php _e('More informations', 'contact-manager'); ?></a>
<?php if (function_exists('commerce_data')) { echo '<br />'.__('Leave this field blank to apply the Commerce Manager\'s option.', 'contact-manager'); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<div class="postbox" id="registration-as-a-client-module"<?php if (in_array('registration-as-a-client', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="registration-as-a-client"><strong><?php echo $modules['options']['registration-as-a-client']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php echo (function_exists('commerce_data') ? '<a '.$default_options_links_markup.' href="admin.php?page=commerce-manager-client-area">'.__('Click here to configure the options of Commerce Manager.', 'contact-manager').'</a>' : str_replace('<a', '<a '.$documentations_links_markup, __('To subscribe the senders as clients, you must have installed and activated <a href="http://www.kleor.com/commerce-manager">Commerce Manager</a>.', 'contact-manager'))); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_as_a_client" id="sender_subscribed_as_a_client" value="yes"<?php if ($options['sender_subscribed_as_a_client'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender as a client', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#registration-as-a-client"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (get_option('commerce_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."commerce_manager_clients_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_category_id" id="sender_client_category_id" onchange="fill_form(this.form);">
<option value=""<?php if ($options['sender_client_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_client_category_id_default_option"><?php _e('Commerce Manager\'s option', 'contact-manager'); ?></option>
<option value="0"<?php if ($options['sender_client_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($options['sender_client_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('commerce_data')) {
$ids_fields[] = 'sender_client_category_id';
$applied_value = ($options['sender_client_category_id'] == '' ? commerce_data('clients_initial_category_id') : $options['sender_client_category_id']);
echo '<span id="sender-client-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_client_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_status" id="sender_client_status">
<option value=""<?php if ($options['sender_client_status'] == '') { echo ' selected="selected"'; } ?> id="sender_client_status_default_option"><?php _e('Commerce Manager\'s option', 'contact-manager'); ?></option>
<option value="active"<?php if ($options['sender_client_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($options['sender_client_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/commerce-manager/documentation/#client-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commerce_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="commerce_registration_confirmation_email_sent" id="commerce_registration_confirmation_email_sent">
<option value=""<?php if ($options['commerce_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="commerce_registration_confirmation_email_sent_default_option"><?php _e('Commerce Manager\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['commerce_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['commerce_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('commerce_data') ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=commerce-manager-client-area#registration-confirmation-email') : _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commerce_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="commerce_registration_notification_email_sent" id="commerce_registration_notification_email_sent">
<option value=""<?php if ($options['commerce_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="commerce_registration_notification_email_sent_default_option"><?php _e('Commerce Manager\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['commerce_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['commerce_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('commerce_data') ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=commerce-manager-client-area#registration-notification-email') : _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="registration-to-affiliate-program-module"<?php if (in_array('registration-to-affiliate-program', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="registration-to-affiliate-program"><strong><?php echo $modules['options']['registration-to-affiliate-program']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php echo (function_exists('affiliation_data') ? '<a '.$default_options_links_markup.' href="admin.php?page=affiliation-manager">'.__('Click here to configure the options of Affiliation Manager.', 'contact-manager').'</a>' : str_replace('<a', '<a '.$documentations_links_markup, __('To use affiliation, you must have installed and activated <a href="http://www.kleor.com/affiliation-manager">Affiliation Manager</a>.', 'contact-manager'))); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_affiliate_program" id="sender_subscribed_to_affiliate_program" value="yes"<?php if ($options['sender_subscribed_to_affiliate_program'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender to affiliate program', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#registration-to-affiliate-program"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (get_option('affiliation_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."affiliation_manager_affiliates_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_category_id" id="sender_affiliate_category_id" onchange="fill_form(this.form);">
<option value=""<?php if ($options['sender_affiliate_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_affiliate_category_id_default_option"><?php _e('Affiliation Manager\'s option', 'contact-manager'); ?></option>
<option value="0"<?php if ($options['sender_affiliate_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($options['sender_affiliate_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('affiliation_data')) {
$ids_fields[] = 'sender_affiliate_category_id';
$applied_value = ($options['sender_affiliate_category_id'] == '' ? affiliation_data('affiliates_initial_category_id') : $options['sender_affiliate_category_id']);
echo '<span id="sender-affiliate-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_affiliate_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_status" id="sender_affiliate_status">
<option value=""<?php if ($options['sender_affiliate_status'] == '') { echo ' selected="selected"'; } ?> id="sender_affiliate_status_default_option"><?php _e('Affiliation Manager\'s option', 'contact-manager'); ?></option>
<option value="active"<?php if ($options['sender_affiliate_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($options['sender_affiliate_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#affiliate-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_registration_confirmation_email_sent" id="affiliation_registration_confirmation_email_sent">
<option value=""<?php if ($options['affiliation_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="affiliation_registration_confirmation_email_sent_default_option"><?php _e('Affiliation Manager\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['affiliation_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['affiliation_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('affiliation_data') ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=affiliation-manager#registration-confirmation-email') : _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="affiliation_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="affiliation_registration_notification_email_sent" id="affiliation_registration_notification_email_sent">
<option value=""<?php if ($options['affiliation_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="affiliation_registration_notification_email_sent_default_option"><?php _e('Affiliation Manager\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['affiliation_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['affiliation_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('affiliation_data') ? printf(str_replace('<a', '<a '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), 'admin.php?page=affiliation-manager#registration-notification-email') : _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="membership-module"<?php if (in_array('membership', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="membership"><strong><?php echo $modules['options']['membership']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php echo (function_exists('membership_data') ? '<a '.$default_options_links_markup.' href="admin.php?page=membership-manager">'.__('Click here to configure the options of Membership Manager.', 'contact-manager').'</a>' : str_replace('<a', '<a '.$documentations_links_markup, __('To use membership, you must have installed and activated <a href="http://www.kleor.com/membership-manager">Membership Manager</a>.', 'contact-manager'))); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_members_areas" id="sender_subscribed_to_members_areas" value="yes"<?php if ($options['sender_subscribed_to_members_areas'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender to a member area', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#membership"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas"><?php _e('Members areas', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_members_areas" id="sender_members_areas" rows="1" cols="50" onkeyup="update_links(this.form); fill_form(this.form);" onchange="update_links(this.form); fill_form(this.form);"><?php echo $options['sender_members_areas']; ?></textarea>
<?php if (function_exists('membership_data')) {
$ids_fields[] = 'sender_members_areas';
echo '<span class="description" style="vertical-align: 25%;" id="sender-members-areas-description">'.contact_manager_pages_field_description('sender_members_areas', $options['sender_members_areas']).'</span>';
$links = contact_manager_pages_field_links($back_office_options, 'sender_members_areas', $options['sender_members_areas']); echo '<span id="sender-members-areas-links">'.$links.'</span>';
$string = '-member-area&amp;id='.$options['sender_members_areas']; $url = 'admin.php?page=membership-manager'.(strstr($links, $string) ? $string : ''); } ?><br />
<span class="description"><?php _e('You can enter several members areas IDs. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas_modifications"><?php _e('Automatic modifications', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 50%;" name="sender_members_areas_modifications" id="sender_members_areas_modifications" rows="2" cols="50" onchange="fill_form(this.form);"><?php echo $options['sender_members_areas_modifications']; ?></textarea>
<span class="description"><?php _e('You can offer a temporary access, and automatically modify the list of members areas to which the member can access when a certain date is reached.', 'contact-manager'); ?>
 <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/membership-manager/documentation/#members-areas-modifications"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php if (get_option('membership_manager')) {
$categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."membership_manager_members_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_category_id" id="sender_member_category_id" onchange="fill_form(this.form);">
<option value=""<?php if ($options['sender_member_category_id'] == '') { echo ' selected="selected"'; } ?> id="sender_member_category_id_default_option"><?php _e('Member area\'s option', 'contact-manager'); ?></option>
<option value="0"<?php if ($options['sender_member_category_id'] == '0') { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($options['sender_member_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if (function_exists('membership_data')) {
$ids_fields[] = 'sender_member_category_id';
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $options['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
$applied_value = ($options['sender_member_category_id'] == '' ? member_area_data('members_initial_category_id') : $options['sender_member_category_id']);
echo '<span id="sender-member-category-id-links">'.contact_manager_pages_field_links($back_office_options, 'sender_member_category_id', $applied_value).'</span>'; } ?></td></tr>
<?php } } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_status" id="sender_member_status">
<option value=""<?php if ($options['sender_member_status'] == '') { echo ' selected="selected"'; } ?> id="sender_member_status_default_option"><?php _e('Member area\'s option', 'contact-manager'); ?></option>
<option value="active"<?php if ($options['sender_member_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($options['sender_member_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/membership-manager/documentation/#member-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="membership_registration_confirmation_email_sent"><?php _e('Send a registration confirmation email', 'contact-manager'); ?></label></strong></th>
<td><select name="membership_registration_confirmation_email_sent" id="membership_registration_confirmation_email_sent">
<option value=""<?php if ($options['membership_registration_confirmation_email_sent'] == '') { echo ' selected="selected"'; } ?> id="membership_registration_confirmation_email_sent_default_option"><?php _e('Member area\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['membership_registration_confirmation_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['membership_registration_confirmation_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('membership_data') ? printf(str_replace('<a', '<a id="membership-registration-confirmation-email-sent-link" '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), $url.'#registration-confirmation-email') : _e('You can configure this email through the interface of Membership Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="membership_registration_notification_email_sent"><?php _e('Send a registration notification email', 'contact-manager'); ?></label></strong></th>
<td><select name="membership_registration_notification_email_sent" id="membership_registration_notification_email_sent">
<option value=""<?php if ($options['membership_registration_notification_email_sent'] == '') { echo ' selected="selected"'; } ?> id="membership_registration_notification_email_sent_default_option"><?php _e('Member area\'s option', 'contact-manager'); ?></option>
<option value="yes"<?php if ($options['membership_registration_notification_email_sent'] == 'yes') { echo ' selected="selected"'; } ?>><?php _e('Yes', 'contact-manager'); ?></option>
<option value="no"<?php if ($options['membership_registration_notification_email_sent'] == 'no') { echo ' selected="selected"'; } ?>><?php _e('No', 'contact-manager'); ?></option>
</select>
<span class="description"><?php (function_exists('membership_data') ? printf(str_replace('<a', '<a id="membership-registration-notification-email-sent-link" '.$default_options_links_markup, __('You can configure this email <a href="%1$s">here</a>.', 'contact-manager')), $url.'#registration-notification-email') : _e('You can configure this email through the interface of Membership Manager.', 'contact-manager')); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="wordpress-module"<?php if (in_array('wordpress', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="wordpress"><strong><?php echo $modules['options']['wordpress']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_as_a_user" id="sender_subscribed_as_a_user" value="yes"<?php if ($options['sender_subscribed_as_a_user'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender as a user', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#wordpress"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_user_role"><?php _e('Role', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_user_role" id="sender_user_role">
<?php foreach (contact_manager_users_roles() as $role => $name) {
echo '<option value="'.$role.'"'.($options['sender_user_role'] == $role ? ' selected="selected"' : '').'>'.$name.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="custom-instructions-module"<?php if (in_array('custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="custom-instructions"><strong><?php echo $modules['options']['custom-instructions']['name']; ?></strong></h3>
<div class="inside">
<div id="message-custom-instructions-module"<?php if (in_array('message-custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="message-custom-instructions"><strong><?php echo $modules['options']['custom-instructions']['modules']['message-custom-instructions']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_custom_instructions_executed" id="message_custom_instructions_executed" value="yes"<?php if ($options['message_custom_instructions_executed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Execute custom instructions', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_custom_instructions" id="message_custom_instructions" rows="10" cols="75"><?php echo htmlspecialchars(get_option('contact_manager_message_custom_instructions')); ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the sending of a message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="message-removal-custom-instructions-module"<?php if (in_array('message-removal-custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="message-removal-custom-instructions"><strong><?php echo $modules['options']['custom-instructions']['modules']['message-removal-custom-instructions']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_removal_custom_instructions_executed" id="message_removal_custom_instructions_executed" value="yes"<?php if ($options['message_removal_custom_instructions_executed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Execute custom instructions', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_removal_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_removal_custom_instructions" id="message_removal_custom_instructions" rows="10" cols="75"><?php echo htmlspecialchars(get_option('contact_manager_message_removal_custom_instructions')); ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the removal of a message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="form-submission-custom-instructions-module"<?php if (in_array('form-submission-custom-instructions', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="form-submission-custom-instructions"><strong><?php echo $modules['options']['custom-instructions']['modules']['form-submission-custom-instructions']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="form_submission_custom_instructions_executed" id="form_submission_custom_instructions_executed" value="yes"<?php if ($options['form_submission_custom_instructions_executed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Execute custom instructions', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="form_submission_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="form_submission_custom_instructions" id="form_submission_custom_instructions" rows="10" cols="75"><?php echo htmlspecialchars(get_option('contact_manager_form_submission_custom_instructions')); ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the submission of a form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<div class="postbox" id="affiliation-module"<?php if (in_array('affiliation', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 style="font-size: 1.25em;" id="affiliation"><strong><?php echo $modules['options']['affiliation']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('affiliation_data')) { _e('You can award a commission to the affiliate who referred a message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#affiliation"><?php _e('More informations', 'contact-manager'); ?></a><?php }
else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To use affiliation, you must have installed and activated <a href="http://www.kleor.com/affiliation-manager">Affiliation Manager</a>.', 'contact-manager')); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="affiliation_enabled" id="affiliation_enabled" value="yes"<?php if ($options['affiliation_enabled'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Use affiliation', 'contact-manager'); ?></label>
<span class="description" style="vertical-align: -5%;"><?php _e('Uncheck this box allows you to disable the award of commissions.', 'contact-manager'); ?></span></td></tr>
</tbody></table>
<div id="level-1-commission-module"<?php if (in_array('level-1-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-1-commission"><strong><?php echo $modules['options']['affiliation']['modules']['level-1-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 1 commission is awarded to the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission_amount" id="commission_amount" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo $options['commission_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
<div id="level-2-commission-module"<?php if (in_array('level-2-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-2-commission"><strong><?php echo $modules['options']['affiliation']['modules']['level-2-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 2 commission is awarded to the referrer of the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="commission2_enabled" id="commission2_enabled" value="yes"<?php if ($options['commission2_enabled'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Award a level 2 commission', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission2_amount" id="commission2_amount" rows="1" cols="25" onchange="fill_form(this.form);"><?php echo $options['commission2_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Update', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<p class="submit"><input type="submit" class="button-primary" name="submit" id="submit" value="<?php _e('Save Changes', 'contact-manager'); ?>" /></p>
<?php contact_manager_pages_module($back_office_options, 'options-page', $undisplayed_modules); ?>
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

<?php echo 'fields = []; default_values = [];'."\n";
foreach ($initial_options[''] as $key => $value) {
$value = (string) $value; if (($value != '') && (!in_array($key, array('automatic_display_maximum_forms_quantity', 'maximum_messages_quantity')))) {
echo 'fields.push("'.$key.'"); default_values["'.$key.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $value).'";'."\n"; } }
foreach ($other_options as $field) {
$value = (string) $initial_options[$field]; if ($value != '') {
echo 'fields.push("'.$field.'"); default_values["'.$field.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $value).'";'."\n"; } }
echo 'for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
if ((element) && ((element.type == "text") || (element.type == "textarea"))) {
element.setAttribute("data-default", default_values[fields[i]]);
if (element.hasAttribute("onchange")) { string = " "+element.getAttribute("onchange"); } else { string = ""; }
element.setAttribute("onchange", "if (this.value === \'\') { this.value = this.getAttribute(\'data-default\'); }"+string); } }'."\n";

if (function_exists('commerce_data')) {
$default_options = (array) get_option('commerce_manager');
include CONTACT_MANAGER_PATH.'libraries/api-fields.php';
echo 'fields = []; default_values = [];'."\n";
foreach ($api_fields as $field) { if (isset($default_options[$field])) {
echo 'fields.push("'.$field.'"); default_values["'.$field.'"] = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $default_options[$field]).'";'."\n"; } }
echo 'for (i = 0, n = fields.length; i < n; i++) {
element = document.getElementById(fields[i]);
element.setAttribute("data-default", default_values[fields[i]]); if (element.value === "") { element.setAttribute("data-empty", "yes"); element.style.color = "#a0a0a0"; element.value = default_values[fields[i]]; }
if (element.hasAttribute("onfocus")) { string = " "+element.getAttribute("onfocus"); } else { string = ""; }
element.setAttribute("onfocus", "this.setAttribute(\'data-focused\', \'yes\'); if (this.getAttribute(\'data-empty\') == \'yes\') { this.style.color = \'\'; this.value = \'\'; }"+string);
events = ["onblur","onchange"]; for (j = 0; j < 2; j++) {
if (element.hasAttribute(events[j])) { string = " "+element.getAttribute(events[j]); } else { string = ""; }
element.setAttribute(events[j], "if (this.getAttribute(\'data-focused\') == \'yes\') { this.setAttribute(\'data-focused\', \'no\'); if (this.value === \'\') { this.setAttribute(\'data-empty\', \'yes\'); this.style.color = \'#a0a0a0\'; this.value = this.getAttribute(\'data-default\'); } else { this.setAttribute(\'data-empty\', \'no\'); } }"+string); } }'."\n"; }

$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $options['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
$fields = array(); $default_options = array();
foreach (array('category_id', 'status') as $field) {
$fields[] = 'sender_client_'.$field; $default_options['sender_client_'.$field] = (function_exists('commerce_data') ? commerce_data('clients_initial_'.$field) : '');
$fields[] = 'sender_affiliate_'.$field; $default_options['sender_affiliate_'.$field] = (function_exists('affiliation_data') ? affiliation_data('affiliates_initial_'.$field) : '');
$fields[] = 'sender_member_'.$field; $default_options['sender_member_'.$field] = (function_exists('member_area_data') ? member_area_data('members_initial_'.$field) : ''); }
foreach (array('confirmation', 'notification') as $action) {
$fields[] = 'commerce_registration_'.$action.'_email_sent'; $default_options['commerce_registration_'.$action.'_email_sent'] = (function_exists('commerce_data') ? commerce_data('registration_'.$action.'_email_sent') : '');
$fields[] = 'affiliation_registration_'.$action.'_email_sent'; $default_options['affiliation_registration_'.$action.'_email_sent'] = (function_exists('affiliation_data') ? affiliation_data('registration_'.$action.'_email_sent') : '');
$fields[] = 'membership_registration_'.$action.'_email_sent'; $default_options['membership_registration_'.$action.'_email_sent'] = (function_exists('member_area_data') ? member_area_data('registration_'.$action.'_email_sent') : ''); }
foreach ($fields as $field) { echo 'element = document.getElementById("'.$field.'_default_option"); if (element) { element.innerHTML = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), contact_manager_pages_selector_default_option_content($field, $default_options[$field])).'"; }'."\n"; } ?>

<?php $fields = array();
foreach ($initial_options[''] as $key => $value) { $fields[] = $key; }
$fields = array_merge($fields, $other_options);
echo 'fill_form_call_number = 0;
function fill_form(form) {
fill_form_call_number += 1;
data = {}; fields = '.json_encode($fields).';
for (i = 0, n = fields.length; i < n; i++) {
if ((form[fields[i]]) && ((form[fields[i]].getAttribute("data-empty") != "yes") || (form[fields[i]].getAttribute("data-focused") == "yes"))) {
if (form[fields[i]].type != "checkbox") { data[fields[i]] = form[fields[i]].value; }
else { if (form[fields[i]].checked == true) { data[fields[i]] = "yes"; } } } }
data["other_options"] = '.json_encode($other_options).';
ids_fields = '.json_encode($ids_fields).'; data["ids_fields"] = ids_fields;
data["fill_form_call_number"] = fill_form_call_number;
jQuery.post("'.CONTACT_MANAGER_URL.'index.php?action=fill-form&page='.$_GET['page'].'&key='.md5(AUTH_KEY).'", data, function(data) {
if (data["fill_form_call_number"] == fill_form_call_number) {
for (i = 0, n = fields.length; i < n; i++) {
if ((form[fields[i]]) && (typeof data[fields[i]] != "undefined") && (fields[i] != document.activeElement.name)) {
if (form[fields[i]].type != "checkbox") { form[fields[i]].value = data[fields[i]]; }
else { if (data[fields[i]] == "yes") { form[fields[i]].checked = true; } else { form[fields[i]].checked = false; } } } }
fields = ["sender_member_category_id","sender_member_status","membership_registration_confirmation_email_sent","membership_registration_notification_email_sent"];
for (i = 0, n = fields.length; i < n; i++) {
var element = document.getElementById(fields[i]+"_default_option");
if ((element) && (typeof data[fields[i]+"_default_option_content"] != "undefined")) { element.innerHTML = data[fields[i]+"_default_option_content"]; } }
var strings = ["description","links"];
for (i = 0, n = ids_fields.length; i < n; i++) { for (j = 0; j < 2; j++) {
var key = ids_fields[i]+"_"+strings[j];
var element = document.getElementById(key.replace(/[_]/g, "-"));
if ((element) && (typeof data[key] != "undefined")) { element.innerHTML = data[key]; } } }
update_links(form); jQuery(".noscript").css("display", "none"); } }, "json"); }'."\n"; ?>

<?php echo 'function update_links(form) {
var fields = '.json_encode($urls_fields).';
for (i = 0, n = fields.length; i < n; i++) {
var element = document.getElementById(fields[i].replace(/[_]/g, "-")+"-link");
if (element) {
var urls = form[fields[i]].value.split(","); var url = format_url(urls[0].replace(/[ ]/g, ""));
if (url == "") { element.innerHTML = ""; }
else { element.innerHTML = \'<a style="vertical-align: 25%;" '.$urls_fields_links_markup.' href="\'+url.replace(/[&]/g, "&amp;")+\'">'.__('Link', 'contact-manager').'</a>\'; } } }
'.(!function_exists('membership_data') ? '' : 'var field = form["sender_members_areas"]; if (field) {
var url = "admin.php?page=membership-manager";
var element = document.getElementById("sender-members-areas-links");
if ((field.value != "") && (field.value != 0) && (element.innerHTML.indexOf("id="+field.value) >= 0)) { url += "-member-area&id="+field.value; }
var actions = ["confirmation","notification"]; for (i = 0; i < 2; i++) {
var element = document.getElementById("membership-registration-"+actions[i]+"-email-sent-link");
if (element) { element.href = url+"#registration-"+actions[i]+"-email"; } } }').' }'."\n"; ?>

<?php echo 'function validate_form(form) {
for (i = 0, n = form.length; i < n; i++) { if (form[i].getAttribute("data-empty") == "yes") { form[i].value = ""; } }
return true; }'."\n"; ?>
</script>
<?php }