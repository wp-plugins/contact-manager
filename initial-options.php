<?php $lang = strtolower(substr(get_locale(), 0, 2)); if ($lang == '') { $lang = 'en'; }
foreach (array('admin_email', 'blogname', 'siteurl') as $key) { $$key = get_option($key); }
$domain = $_SERVER['SERVER_NAME']; if (substr($domain, 0, 4) == 'www.') { $domain = substr($domain, 4); }
if ($blogname == '') { $blogname = ucfirst($domain); }
$blog_email = $admin_email;
if ((!strstr($blog_email, $domain)) && (isset($_SERVER['SERVER_ADMIN']))) { $blog_email = $_SERVER['SERVER_ADMIN']; }
if (!strstr($blog_email, $domain)) { $blog_email = 'contact@'.$domain; }

$initial_options = array();

$initial_options[''] = array(
'affiliation_enabled' => 'no',
'affiliation_registration_confirmation_email_sent' => '',
'affiliation_registration_notification_email_sent' => '',
'automatic_display_enabled' => 'no',
'automatic_display_form_id' => 1,
'automatic_display_location' => 'top',
'automatic_display_maximum_forms_quantity' => 2,
'automatic_display_only_on_single_post_pages' => 'yes',
'commerce_registration_confirmation_email_sent' => '',
'commerce_registration_notification_email_sent' => '',
'commission_amount' => 1,
'commission2_amount' => 0.1,
'commission2_enabled' => 'no',
'default_captcha_type' => 'recaptcha',
'default_recaptcha_theme' => 'red',
'encrypted_urls_key' => md5(mt_rand()),
'encrypted_urls_validity_duration' => 48,
'failed_upload_message' => __('The file upload failed.', 'contact-manager'),
'form_submission_custom_instructions_executed' => 'no',
'getresponse_api_key' => '',
'invalid_captcha_message' => __('The code you entered for the CAPTCHA is incorrect.', 'contact-manager'),
'invalid_email_address_message' => __('This email address appears to be invalid.', 'contact-manager'),
'invalid_field_message' => __('This field is not correctly filled.', 'contact-manager'),
'invalid_fields_message' => __('One or more fields are not correctly filled.', 'contact-manager'),
'mailchimp_api_key' => '',
'maximum_messages_quantity' => 'unlimited',
'maximum_messages_quantity_reached_message' => __('You have already sent [contact-form maximum-messages-quantity-per-sender] messages through this form.', 'contact-manager'),
'membership_registration_confirmation_email_sent' => '',
'membership_registration_notification_email_sent' => '',
'message_confirmation_email_receiver' => '[sender email-address]',
'message_confirmation_email_sender' => $blogname.' <'.$blog_email.'>',
'message_confirmation_email_sent' => 'no',
'message_confirmation_email_subject' => __('We Have Received Your Message', 'contact-manager'),
'message_custom_instructions_executed' => 'no',
'message_notification_email_receiver' => $admin_email,
'message_notification_email_sender' => '[sender first-name] [sender last-name] <[sender email-address]>',
'message_notification_email_sent' => 'yes',
'message_notification_email_subject' => '[message subject]',
'message_removal_custom_instructions_executed' => 'no',
'messages_registration_enabled' => 'no',
'recaptcha_private_key' => '',
'recaptcha_public_key' => '',
'sender_affiliate_category_id' => '',
'sender_affiliate_status' => '',
'sender_autoresponder' => 'AWeber',
'sender_autoresponder_list' => '',
'sender_client_category_id' => '',
'sender_client_status' => '',
'sender_member_category_id' => '',
'sender_member_status' => '',
'sender_members_areas' => '',
'sender_members_areas_modifications' => '',
'sender_subscribed_as_a_client' => 'no',
'sender_subscribed_as_a_user' => 'no',
'sender_subscribed_to_affiliate_program' => 'no',
'sender_subscribed_to_autoresponder' => 'no',
'sender_subscribed_to_members_areas' => 'no',
'sender_user_role' => 'subscriber',
'sg_autorepondeur_account_id' => '',
'sg_autorepondeur_activation_code' => '',
'too_large_file_message' => __('This file is too large.', 'contact-manager'),
'unauthorized_extension_message' => __('This file type is not allowed.', 'contact-manager'),
'unfilled_field_message' => __('This field is required.', 'contact-manager'),
'unfilled_fields_message' => __('Please fill out the required fields.', 'contact-manager'),
'version' => CONTACT_MANAGER_VERSION);


$initial_options['admin_notices'] = array();


$initial_options['code'] =
'[validation-content]<p style="color: green;">'.__('Your message has been sent successfully. If it requires an answer, we should respond within 48 hours.', 'contact-manager').'</p>
[other]<p style="color: red;">[error maximum-messages-quantity-reached] [error unfilled-fields] [error invalid-fields] [error invalid-captcha]</p>[/validation-content]

<p><label><strong>'.__('Your first name:', 'contact-manager').'</strong>*<br />
[input first-name size=30 required=yes]<br />
[error first-name style="color: red;"]</label></p>

<p><label><strong>'.__('Your last name:', 'contact-manager').'</strong><br />
[input last-name size=30]<br />
[error last-name style="color: red;"]</label></p>

<p><label><strong>'.__('Your email address:', 'contact-manager').'</strong>*<br />
[input email-address size=40 required=yes]<br />
[error email-address style="color: red;"]</label></p>

<p><label><strong>'.__('Your website:', 'contact-manager').'</strong><br />
[input website-url size=40]<br />
[error website-url style="color: red;"]</label></p>

<p><label><strong>'.__('Subject of your message:', 'contact-manager').'</strong>*<br />
[input subject size=60 required=yes]<br />
[error subject style="color: red;"]</label></p>

<p><label><strong>'.__('Your message:', 'contact-manager').'</strong>*<br />
[textarea content cols=60 rows=10 required=yes][/textarea]<br />
[error content style="color: red;"]</label></p>

<p><strong>'.__('File:', 'contact-manager').'</strong> [input attachment type=file]<br />[error attachment]</p>

<p><label>[input message-confirmation-email-sent value=yes] '.__('Receive a copy of this message', 'contact-manager').'</label></p>

<div style="margin: 1.5em 0;">[input submit style="background-color: #e0c040; border: 1px solid #a08000; border-radius: 4px; box-shadow: 1px 1px 1px #808080; cursor: pointer;" formnovalidate=formnovalidate value="'.__('Send', 'contact-manager').'"]</div>';


$initial_options['cron'] = array(
'first_installation' => array('version' => '', 'timestamp' => 0),
'previous_activation' => array('version' => '', 'timestamp' => 0),
'previous_admin_notices_cron_timestamp' => 0,
'previous_installation' => array('version' => '', 'number' => 0, 'timestamp' => 0));


$initial_options['form_submission_custom_instructions'] = '';


$initial_options['message_confirmation_email_body'] =
__('Hi', 'contact-manager').' [sender first-name],

'.__('Your message has been sent successfully. If it requires an answer, we should respond within 48 hours.', 'contact-manager').'

'.__('Your message:', 'contact-manager').'

'.__('Subject:', 'contact-manager').' [message subject]

[message content]

--
'.$blogname.'
'.HOME_URL;


$initial_options['message_custom_instructions'] = '';


$initial_options['message_notification_email_body'] =
'[message content]

[sender first-name] [sender last-name]
[sender website-url]

--
'.__('Sent through this form:', 'contact-manager').' [contact-form name]';


$initial_options['message_removal_custom_instructions'] = '';


if (isset($variables)) { $original['variables'] = $variables; }
$variables = array(
'displayed_columns',
'displayed_links',
'first_columns',
'id',
'last_columns',
'links',
'menu_displayed_items',
'menu_items',
'n',
'pages_titles',
'table',
'table_slug',
'tables');
foreach ($variables as $variable) { if (isset($$variable)) { $original[$variable] = $$variable; unset($$variable); } }


include CONTACT_MANAGER_PATH.'tables.php';
foreach ($tables as $table_slug => $table) {
switch ($table_slug) {
case 'forms': $first_columns = array(
'id',
'name',
'description',
'keywords',
'date',
'displays_count',
'messages_count'); break;
case 'forms_categories': $first_columns = array(
'id',
'name',
'description',
'keywords',
'date'); break;
case 'messages': $first_columns = array(
'id',
'subject',
'content',
'first_name',
'last_name',
'email_address',
'form_id',
'date'); }

$last_columns = array();
foreach ($table as $key => $value) {
if ((!in_array($key, $first_columns)) && (isset($value['name'])) && ($value['name'] != '')) { $last_columns[] = $key; } }
$displayed_columns = array();
$n = count($first_columns); for ($i = 0; $i < $n; $i++) { $displayed_columns[] = $i; }

$initial_options[$table_slug] = array(
'columns' => array_merge($first_columns, $last_columns),
'columns_list_displayed' => 'yes',
'displayed_columns' => $displayed_columns,
'limit' => 10,
'order' => 'desc',
'orderby' => 'id',
'searchby' => '',
'start_date' => '2000-01-01 00:00:00'); }


$initial_options['statistics'] = array(
'displayed_tables' => array(),
'filterby' => 'form_id',
'start_date' => '2000-01-01 00:00:00',
'tables' => array('messages', 'forms', 'forms_categories'));


include CONTACT_MANAGER_PATH.'admin-pages.php';
$links = array();
foreach ($admin_links as $key => $value) { $links[] = $key; }
$displayed_links = array();
$n = count($links); for ($i = 0; $i < $n; $i++) { $displayed_links[] = $i; }
$menu_items = array();
$pages_titles = array();
foreach ($admin_pages as $key => $value) {
$menu_items[] = $key;
if (isset($_GET['id'])) { $id = $_GET['id']; unset($_GET['id']); }
$pages_titles[$key] = $value['menu_title'];
if (isset($id)) { $_GET['id'] = $id; unset($id); } }
$menu_displayed_items = array();
foreach ($menu_items as $key => $value) {
if (!in_array($value, array(
'form_category',
'forms_categories',
'message',
'statistics'))) {
$menu_displayed_items[] = $key; } }

$initial_options['back_office'] = array(
'back_office_page_summary_displayed' => 'yes',
'back_office_page_undisplayed_modules' => array(
	'form-category-page-custom-fields',
	'form-page-custom-fields'),
'custom_icon_url' => CONTACT_MANAGER_URL.'images/icon.png',
'custom_icon_used' => 'yes',
'default_options_links_target' => '_blank',
'displayed_links' => $displayed_links,
'documentations_links_target' => '_blank',
'form_category_page_custom_fields' => array(),
'form_category_page_summary_displayed' => 'yes',
'form_category_page_undisplayed_modules' => array(
	'affiliation',
	'autoresponders',
	'custom-fields',
	'custom-instructions',
	'gift',
	'membership',
	'registration-as-a-client',
	'registration-to-affiliate-program',
	'wordpress'),
'form_page_custom_fields' => array(),
'form_page_summary_displayed' => 'yes',
'form_page_undisplayed_modules' => array(
	'affiliation',
	'autoresponders',
	'custom-fields',
	'custom-instructions',
	'gift',
	'membership',
	'registration-as-a-client',
	'registration-to-affiliate-program',
	'wordpress'),
'ids_fields_links_target' => '_blank',
'links' => $links,
'links_displayed' => 'yes',
'menu_displayed' => 'yes',
'menu_displayed_items' => $menu_displayed_items,
'menu_items' => $menu_items,
'menu_title_'.$lang => __('Contact', 'contact-manager'),
'meta_box_'.$lang => array(
	'' => __('Documentation', 'contact-manager'),
	'#forms' => __('Display a form', 'contact-manager'),
	'#sender-contents' => __('Display a content restricted to senders', 'contact-manager'),
	'#screen-options-wrap' => __('Hide this box', 'contact-manager')),
'message_page_custom_fields' => array(),
'message_page_summary_displayed' => 'yes',
'message_page_undisplayed_modules' => array(
	'affiliation',
	'autoresponders',
	'custom-instructions',
	'membership',
	'message-confirmation-email',
	'message-notification-email',
	'registration-as-a-client',
	'registration-to-affiliate-program',
	'wordpress'),
'minimum_roles' => array(
	'manage' => 'administrator',
	'view' => 'administrator'),
'options_page_summary_displayed' => 'yes',
'options_page_undisplayed_modules' => array(
	'affiliation',
	'automatic-display',
	'autoresponders',
	'autoresponders-integration',
	'custom-instructions',
	'membership',
	'registration-as-a-client',
	'registration-to-affiliate-program',
	'urls-encryption',
	'wordpress'),
'pages_modules_links_target' => '_blank',
'pages_titles_'.$lang => $pages_titles,
'statistics_page_undisplayed_columns' => array(),
'statistics_page_undisplayed_rows' => array('forms_categories'),
'title' => 'Contact Manager',
'title_displayed' => 'yes',
'urls_fields_links_target' => '_blank');


foreach ($variables as $variable) { if (isset($original[$variable])) { $$variable = $original[$variable]; } }
if (isset($original['variables'])) { $variables = $original['variables']; }