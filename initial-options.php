<?php foreach (array('admin_email', 'blogname', 'siteurl') as $key) { $$key = get_option($key); }


$initial_options[''] = array(
'affiliation_enabled' => 'no',
'affiliation_registration_confirmation_email_sent' => '',
'affiliation_registration_notification_email_sent' => '',
'automatic_display_enabled' => 'no',
'automatic_display_form_id' => 1,
'automatic_display_location' => 'top',
'commerce_registration_confirmation_email_sent' => '',
'commerce_registration_notification_email_sent' => '',
'commission_amount' => 1,
'commission2_amount' => 0.1,
'commission2_enabled' => 'no',
'default_captcha_type' => 'recaptcha',
'default_recaptcha_theme' => 'red',
'encrypted_urls_key' => md5(mt_rand()),
'encrypted_urls_validity_duration' => 48,
'getresponse_api_key' => '',
'invalid_captcha_message' => __('The code you entered for the CAPTCHA is incorrect.', 'contact-manager'),
'invalid_email_address_message' => __('This email address appears to be invalid.', 'contact-manager'),
'mailchimp_api_key' => '',
'maximum_messages_quantity' => 'unlimited',
'maximum_messages_quantity_reached_message' => __('You have already sent [contact-form maximum-messages-quantity-per-sender] messages through this form.', 'contact-manager'),
'membership_registration_confirmation_email_sent' => '',
'membership_registration_notification_email_sent' => '',
'message_confirmation_email_receiver' => '[sender email-address]',
'message_confirmation_email_sender' => $blogname.' <'.$admin_email.'>',
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
'unfilled_field_message' => __('This field is required.', 'contact-manager'),
'unfilled_fields_message' => __('Please fill out the required fields.', 'contact-manager'),
'version' => CONTACT_MANAGER_VERSION);

include dirname(__FILE__).'/libraries/captchas.php';
$initial_options['captchas_numbers'] = $captchas_numbers;


$initial_options['code'] =
'[validation-content]<p style="color: green;">'.__('Your message has been sent successfully. If it requires an answer, we should respond within 48 hours.', 'contact-manager').'</p>
[other]<p style="color: red;">[error maximum-messages-quantity-reached] [error unfilled-fields] [error invalid-captcha]</p>[/validation-content]

<p><label><strong>'.__('Your first name:', 'contact-manager').'</strong>*<br />
[input first-name size=30 required=yes]<br />
[error style="color: red;" first-name]</label></p>

<p><label><strong>'.__('Your last name:', 'contact-manager').'</strong><br />
[input last-name size=30]<br />
[error style="color: red;" last-name]</label></p>

<p><label><strong>'.__('Your email address:', 'contact-manager').'</strong>*<br />
[input email-address size=40 required=yes]<br />
[error style="color: red;" email-address]</label></p>

<p><label><strong>'.__('Your website:', 'contact-manager').'</strong><br />
[input website-url size=40]<br />
[error style="color: red;" website-url]</label></p>

<p><label><strong>'.__('Subject of your message:', 'contact-manager').'</strong>*<br />
[input subject size=60 required=yes]<br />
[error style="color: red;" subject]</label></p>

<p><label><strong>'.__('Your message:', 'contact-manager').'</strong>*<br />
[textarea content cols=60 rows=10 required=yes][/textarea]<br />
[error style="color: red;" content]</label></p>

<p><label>[input message-confirmation-email-sent value=yes] '.__('Receive a copy of this message', 'contact-manager').'</label></p>

<div>[input submit value="'.__('Send', 'contact-manager').'"]</div>';


$initial_options['cron'] = array(
'previous_installation' => array('version' => '', 'number' => 0, 'timestamp' => 0));


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
'pages_titles',
'table',
'table_slug',
'tables');
foreach ($variables as $variable) { if (isset($$variable)) { $original[$variable] = $$variable; unset($$variable); } }


include dirname(__FILE__).'/tables.php';
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
for ($i = 0; $i < count($first_columns); $i++) { $displayed_columns[] = $i; }

$initial_options[$table_slug] = array(
'columns' => array_merge($first_columns, $last_columns),
'columns_list_displayed' => 'yes',
'displayed_columns' => $displayed_columns,
'limit' => 10,
'order' => 'desc',
'orderby' => 'id',
'searchby' => '',
'start_date' => '2011-01-01 00:00:00'); }


$initial_options['statistics'] = array(
'displayed_tables' => array(),
'filterby' => 'form_id',
'start_date' => '2011-01-01 00:00:00',
'tables' => array('messages', 'forms', 'forms_categories'));


include dirname(__FILE__).'/admin-pages.php';
$links = array();
foreach ($admin_links as $key => $value) { $links[] = $key; }
$displayed_links = array();
for ($i = 0; $i < count($links); $i++) { $displayed_links[] = $i; }
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
'forms_categories'))) {
$menu_displayed_items[] = $key; } }

$initial_options['back_office'] = array(
'back_office_page_summary_displayed' => 'yes',
'back_office_page_undisplayed_modules' => array(
	'form-page-custom-fields',
	'form-category-page-custom-fields',
	'icon',
	'message-page-custom-fields'),
'displayed_links' => $displayed_links,
'custom_icon_url' => CONTACT_MANAGER_URL.'images/icon.png',
'custom_icon_used' => 'no',
'links' => $links,
'links_displayed' => 'yes',
'form_category_page_custom_fields' => array(),
'form_category_page_summary_displayed' => 'yes',
'form_category_page_undisplayed_modules' => array(
	'affiliation',
	'autoresponders',
	'custom-fields',
	'custom-instructions',
	'gift',
	'membership',
	'messages-registration',
	'registration-as-a-client',
	'registration-to-affiliate-program',
	'wordpress'),
'form_page_custom_fields' => array(),
'form_page_summary_displayed' => 'yes',
'form_page_undisplayed_modules' => array(
	'affiliation',
	'autoresponders',
	'counters',
	'custom-fields',
	'custom-instructions',
	'gift',
	'membership',
	'messages-registration',
	'registration-as-a-client',
	'registration-to-affiliate-program',
	'wordpress'),
'menu_displayed' => 'yes',
'menu_displayed_items' => $menu_displayed_items,
'menu_items' => $menu_items,
'menu_title' => __('Contact', 'contact-manager'),
'message_page_custom_fields' => array(),
'message_page_summary_displayed' => 'yes',
'message_page_undisplayed_modules' => array(
	'affiliation',
	'autoresponders',
	'custom-fields',
	'custom-instructions',
	'membership',
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
	'captcha',
	'custom-instructions',
	'membership',
	'messages-registration',
	'registration-as-a-client',
	'registration-to-affiliate-program',
	'urls-encryption',
	'wordpress'),
'pages_titles' => $pages_titles,
'statistics_page_undisplayed_columns' => array(),
'statistics_page_undisplayed_rows' => array(),
'title' => 'Contact Manager',
'title_displayed' => 'yes');


foreach ($variables as $variable) { if (isset($original[$variable])) { $$variable = $original[$variable]; } }
if (isset($original['variables'])) { $variables = $original['variables']; }