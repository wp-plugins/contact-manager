<?php $admin_links = array(
'Documentation' => array('name' => __('Documentation', 'contact-manager')),
'Commerce Manager' => array('name' => __('Commerce', 'contact-manager')),
'Affiliation Manager' => array('name' => __('Affiliation', 'contact-manager')),
'Membership Manager' => array('name' => __('Membership', 'contact-manager')),
'Optin Manager' => array('name' => __('Optin', 'contact-manager')));

$admin_pages = array(
'' => array('page_title' => 'Contact Manager ('.__('Options', 'contact-manager').')', 'menu_title' => __('Options', 'contact-manager'), 'file' => 'options-page.php'),
'form' => array('page_title' => 'Contact Manager ('.__('Form', 'contact-manager').')', 'menu_title' => (((isset($_GET['page'])) && ($_GET['page'] == 'contact-manager-form') && (isset($_GET['id']))) ? (((isset($_GET['action'])) && ($_GET['action'] == 'delete')) ? __('Delete Form', 'contact-manager') : __('Edit Form', 'contact-manager')) : __('Add Form', 'contact-manager')), 'file' => 'form-page.php'),
'forms' => array('page_title' => 'Contact Manager ('.__('Forms', 'contact-manager').')', 'menu_title' => __('Forms', 'contact-manager'), 'file' => 'table-page.php'),
'form_category' => array('page_title' => 'Contact Manager ('.__('Form Category', 'contact-manager').')', 'menu_title' => (((isset($_GET['page'])) && ($_GET['page'] == 'contact-manager-form-category') && (isset($_GET['id']))) ? (((isset($_GET['action'])) && ($_GET['action'] == 'delete')) ? __('Delete Form Category', 'contact-manager') : __('Edit Form Category', 'contact-manager')) : __('Add Form Category', 'contact-manager')), 'file' => 'form-page.php'),
'forms_categories' => array('page_title' => 'Contact Manager ('.__('Forms Categories', 'contact-manager').')', 'menu_title' => __('Forms Categories', 'contact-manager'), 'file' => 'table-page.php'),
'message' => array('page_title' => 'Contact Manager ('.__('Message', 'contact-manager').')', 'menu_title' => (((isset($_GET['page'])) && ($_GET['page'] == 'contact-manager-message') && (isset($_GET['id']))) ? (((isset($_GET['action'])) && ($_GET['action'] == 'delete')) ? __('Delete Message', 'contact-manager') : __('Edit Message', 'contact-manager')) : __('Add Message', 'contact-manager')), 'file' => 'message-page.php'),
'messages' => array('page_title' => 'Contact Manager ('.__('Messages', 'contact-manager').')', 'menu_title' => __('Messages', 'contact-manager'), 'file' => 'table-page.php'),
'statistics' => array('page_title' => 'Contact Manager ('.__('Statistics', 'contact-manager').')', 'menu_title' => __('Statistics', 'contact-manager'), 'file' => 'statistics-page.php'),
'back_office' => array('page_title' => 'Contact Manager ('.__('Back Office', 'contact-manager').')', 'menu_title' => __('Back Office', 'contact-manager'), 'file' => 'back-office-page.php'));

$modules = array();

$modules['back_office'] = array(
'capabilities' => array('name' => __('Capabilities', 'contact-manager')),
'icon' => array('name' => __('Icon', 'contact-manager')),
'top' => array('name' => __('Top', 'contact-manager')),
'menu' => array('name' => __('Menu', 'contact-manager')),
'links' => array('name' => __('Links', 'contact-manager')),
'options-page' => array('name' => __('<em>Options</em> page', 'contact-manager')),
'form-page' => array('name' => (isset($_GET['id']) ? __('<em>Edit Form</em> page', 'contact-manager') : __('<em>Add Form</em> page', 'contact-manager')), 'modules' => array(
	'form-page-custom-fields' => array('name' => __('Custom fields', 'contact-manager')))),
'form-category-page' => array('name' => (isset($_GET['id']) ? __('<em>Edit Form Category</em> page', 'contact-manager') : __('<em>Add Form Category</em> page', 'contact-manager')), 'modules' => array(
	'form-category-page-custom-fields' => array('name' => __('Custom fields', 'contact-manager')))),
'message-page' => array('name' => (isset($_GET['id']) ? __('<em>Edit Message</em> page', 'contact-manager') : __('<em>Add Message</em> page', 'contact-manager')), 'modules' => array(
	'message-page-custom-fields' => array('name' => __('Custom fields', 'contact-manager')))),
'statistics-page' => array('name' => __('<em>Statistics</em> page', 'contact-manager')),
'back-office-page' => array('name' => __('<em>Back Office</em> page', 'contact-manager'), 'required' => 'yes'));

$modules['form'] = array(
'general-informations' => array('name' => __('General informations', 'contact-manager'), 'required' => 'yes'),
'custom-fields' => array('name' => __('Custom fields', 'contact-manager')),
'gift' => array('name' => __('Gift', 'contact-manager')),
'counters' => array('name' => __('Counters', 'contact-manager')),
'form' => array('name' => __('Form', 'contact-manager'), 'modules' => array(
	'error-messages' => array('name' => __('Error messages', 'contact-manager')))),
'messages-registration' => array('name' => __('Messages registration', 'contact-manager')),
'message-confirmation-email' => array('name' => __('Message confirmation email', 'contact-manager')),
'message-notification-email' => array('name' => __('Message notification email', 'contact-manager')),
'autoresponders' => array('name' => __('Autoresponders', 'contact-manager')),
'registration-as-a-client' => array('name' => __('Registration as a client', 'contact-manager')),
'registration-to-affiliate-program' => array('name' => __('Registration to affiliate program', 'contact-manager')),
'membership' => array('name' => __('Membership', 'contact-manager')),
'wordpress' => array('name' => __('WordPress', 'contact-manager')),
'custom-instructions' => array('name' => __('Custom instructions', 'contact-manager')),
'affiliation' => array('name' => __('Affiliation', 'contact-manager'), 'modules' => array(
	'level-1-commission' => array('name' => __('Level 1 commission', 'contact-manager')),
	'level-2-commission' => array('name' => __('Level 2 commission', 'contact-manager')))),
'form-page' => array('name' => (isset($_GET['id']) ? __('<em>Edit Form</em> page', 'contact-manager') : __('<em>Add Form</em> page', 'contact-manager'))));

$modules['form_category'] = $modules['form'];
foreach (array('counters', 'form-page') as $field) { unset($modules['form_category'][$field]); }
$modules['form_category'] = array_merge($modules['form_category'], array(
'form-category-page' => array('name' => (isset($_GET['id']) ? __('<em>Edit Form Category</em> page', 'contact-manager') : __('<em>Add Form Category</em> page', 'contact-manager')))));

$modules['message'] = array(
'general-informations' => array('name' => __('General informations', 'contact-manager'), 'required' => 'yes'),
'sender' => array('name' => __('Sender', 'contact-manager')),
'custom-fields' => array('name' => __('Custom fields', 'contact-manager')),
'affiliation' => array('name' => __('Affiliation', 'contact-manager'), 'modules' => array(
	'level-1-commission' => array('name' => __('Level 1 commission', 'contact-manager')),
	'level-2-commission' => array('name' => __('Level 2 commission', 'contact-manager')))),
'message-confirmation-email' => array('name' => __('Message confirmation email', 'contact-manager')),
'message-notification-email' => array('name' => __('Message notification email', 'contact-manager')),
'autoresponders' => array('name' => __('Autoresponders', 'contact-manager')),
'registration-as-a-client' => array('name' => __('Registration as a client', 'contact-manager')),
'registration-to-affiliate-program' => array('name' => __('Registration to affiliate program', 'contact-manager')),
'membership' => array('name' => __('Membership', 'contact-manager')),
'wordpress' => array('name' => __('WordPress', 'contact-manager')),
'custom-instructions' => array('name' => __('Custom instructions', 'contact-manager')),
'message-page' => array('name' => (isset($_GET['id']) ? __('<em>Edit Message</em> page', 'contact-manager') : __('<em>Add Message</em> page', 'contact-manager'))));

$add_message_modules = array(
'message-confirmation-email',
'message-notification-email',
'autoresponders',
'registration-as-a-client',
'registration-to-affiliate-program',
'membership',
'wordpress',
'custom-instructions');

$add_message_fields = array(
'message_confirmation_email_sent',
'message_confirmation_email_sender',
'message_confirmation_email_receiver',
'message_confirmation_email_subject',
'message_confirmation_email_body',
'message_notification_email_sent',
'message_notification_email_sender',
'message_notification_email_receiver',
'message_notification_email_subject',
'message_notification_email_body',
'sender_subscribed_to_autoresponder',
'sender_autoresponder',
'sender_autoresponder_list',
'sender_subscribed_as_a_client',
'sender_client_category_id',
'sender_client_status',
'commerce_registration_confirmation_email_sent',
'commerce_registration_notification_email_sent',
'sender_subscribed_to_affiliate_program',
'sender_affiliate_category_id',
'sender_affiliate_status',
'affiliation_registration_confirmation_email_sent',
'affiliation_registration_notification_email_sent',
'sender_subscribed_to_members_areas',
'sender_members_areas',
'sender_members_areas_modifications',
'sender_member_category_id',
'sender_member_status',
'membership_registration_confirmation_email_sent',
'membership_registration_notification_email_sent',
'sender_subscribed_as_a_user',
'sender_user_role',
'message_custom_instructions_executed',
'message_custom_instructions');

$modules['options'] = array(
'automatic-display' => array('name' => __('Automatic display', 'contact-manager')),
'forms' => array('name' => __('Forms', 'contact-manager'), 'modules' => array(
	'captcha' => array('name' => __('CAPTCHA', 'contact-manager')),
	'error-messages' => array('name' => __('Error messages', 'contact-manager')))),
'messages-registration' => array('name' => __('Messages registration', 'contact-manager')),
'urls-encryption' => array('name' => __('URLs encryption', 'contact-manager')),
'message-confirmation-email' => array('name' => __('Message confirmation email', 'contact-manager')),
'message-notification-email' => array('name' => __('Message notification email', 'contact-manager')),
'autoresponders' => array('name' => __('Autoresponders', 'contact-manager')),
'autoresponders-integration' => array('name' => __('Autoresponders integration', 'contact-manager'), 'modules' => array(
	'aweber' => array('name' => 'AWeber'),
	'cybermailing' => array('name' => 'CyberMailing'),
	'getresponse' => array('name' => 'GetResponse'),
	'mailchimp' => array('name' => 'MailChimp'),
	'sg-autorepondeur' => array('name' => 'SG Autorépondeur'))),
'registration-as-a-client' => array('name' => __('Registration as a client', 'contact-manager')),
'registration-to-affiliate-program' => array('name' => __('Registration to affiliate program', 'contact-manager')),
'membership' => array('name' => __('Membership', 'contact-manager')),
'wordpress' => array('name' => __('WordPress', 'contact-manager')),
'custom-instructions' => array('name' => __('Custom instructions', 'contact-manager'), 'modules' => array(
	'message-custom-instructions' => array('name' => __('New message', 'contact-manager')),
	'message-removal-custom-instructions' => array('name' => __('Message removal', 'contact-manager')),
	'form-submission-custom-instructions' => array('name' => __('Form submission', 'contact-manager')))),
'affiliation' => array('name' => __('Affiliation', 'contact-manager'), 'modules' => array(
	'level-1-commission' => array('name' => __('Level 1 commission', 'contact-manager')),
	'level-2-commission' => array('name' => __('Level 2 commission', 'contact-manager')))),
'options-page' => array('name' => __('<em>Options</em> page', 'contact-manager')));

$statistics_columns = array(
'data' => array('name' => __('Data', 'contact-manager'), 'width' => 30, 'required' => 'yes'),
'quantity' => array('name' => __('Quantity', 'contact-manager'), 'width' => 20));

$statistics_rows = array(
'messages' => array('name' => __('Messages', 'contact-manager')),
'forms' => array('name' => __('Forms', 'contact-manager')),
'forms_categories' => array('name' => __('Forms categories', 'contact-manager')));

$roles = array(
'administrator' => array('name' => __('Administrator', 'contact-manager'), 'capability' => 'manage_options'),
'editor' => array('name' => __('Editor', 'contact-manager'), 'capability' => 'edit_pages'),
'author' => array('name' => __('Author', 'contact-manager'), 'capability' => 'publish_posts'),
'contributor' => array('name' => __('Contributor', 'contact-manager'), 'capability' => 'edit_posts'),
'subscriber' => array('name' => __('Subscriber', 'contact-manager'), 'capability' => 'read'));