<?php $tables['forms'] = array(
'id' => array('type' => 'int', 'modules' => array('general-informations'), 'name' => __('ID', 'contact-manager'), 'width' => 5),
'category_id' => array('type' => 'int', 'modules' => array('general-informations'), 'name' => (((isset($_GET['page'])) && (strstr($_GET['page'], 'categories'))) ? __('Parent category ID', 'contact-manager') : __('Category ID', 'contact-manager')), 'width' => 12),
'name' => array('type' => 'text', 'modules' => array('general-informations'), 'name' => __('Name', 'contact-manager'), 'width' => 18, 'searchby' => __('the name', 'contact-manager')),
'description' => array('type' => 'text', 'modules' => array('general-informations'), 'name' => __('Description', 'contact-manager'), 'width' => 18, 'searchby' => __('the description', 'contact-manager')),
'keywords' => array('type' => 'text', 'modules' => array('general-informations'), 'name' => __('Keywords', 'contact-manager'), 'width' => 18, 'searchby' => __('the keywords', 'contact-manager')),
'date' => array('type' => 'datetime', 'modules' => array('general-informations'), 'name' => __('Creation date', 'contact-manager'), 'width' => 18, 'searchby' => __('the creation date', 'contact-manager')),
'date_utc' => array('type' => 'datetime', 'modules' => array('general-informations'), 'name' => __('Creation date (UTC)', 'contact-manager'), 'width' => 18, 'searchby' => __('the creation date (UTC)', 'contact-manager')),
'gift_download_url' => array('type' => 'text', 'modules' => array('gift'), 'name' => __('Gift download URL', 'contact-manager'), 'width' => 18, 'searchby' => __('the gift download URL', 'contact-manager')),
'gift_instructions' => array('type' => 'text', 'modules' => array('gift'), 'name' => __('Instructions to the sender', 'contact-manager'), 'width' => 18, 'searchby' => __('the instructions to the sender', 'contact-manager')),
'maximum_messages_quantity_per_sender' => array('type' => 'text', 'default' => 'unlimited', 'modules' => array('general-informations'), 'name' => __('Maximum messages quantity per sender', 'contact-manager'), 'width' => 12, 'searchby' => __('the maximum messages quantity per sender', 'contact-manager')),
'displays_count' => array('type' => 'int', 'modules' => array('counters'), 'name' => __('Displays count', 'contact-manager'), 'width' => 12, 'searchby' => __('the displays count', 'contact-manager')),
'messages_count' => array('type' => 'int', 'modules' => array('counters'), 'name' => __('Messages count', 'contact-manager'), 'width' => 12, 'searchby' => __('the messages count', 'contact-manager')),
'code' => array('type' => 'text', 'modules' => array('form'), 'name' => __('Code', 'contact-manager'), 'width' => 18, 'searchby' => __('the code', 'contact-manager')),
'unfilled_fields_message' => array('type' => 'text', 'modules' => array('form', 'error-messages'), 'name' => __('Unfilled fields message', 'contact-manager'), 'width' => 18),
'unfilled_field_message' => array('type' => 'text', 'modules' => array('form', 'error-messages'), 'name' => __('Unfilled field\'s message', 'contact-manager'), 'width' => 18),
'invalid_email_address_message' => array('type' => 'text', 'modules' => array('form', 'error-messages'), 'name' => __('Invalid email address message', 'contact-manager'), 'width' => 18),
'invalid_captcha_message' => array('type' => 'text', 'modules' => array('form', 'error-messages'), 'name' => __('Invalid CAPTCHA message', 'contact-manager'), 'width' => 18),
'maximum_messages_quantity_reached_message' => array('type' => 'text', 'modules' => array('form', 'error-messages'), 'name' => __('Message of maximum messages quantity reached', 'contact-manager'), 'width' => 18),
'messages_registration_enabled' => array('type' => 'text', 'modules' => array('messages-registration'), 'name' => __('Messages registration enabled', 'contact-manager'), 'width' => 15),
'maximum_messages_quantity' => array('type' => 'text', 'modules' => array('messages-registration'), 'name' => __('Maximum messages quantity', 'contact-manager'), 'width' => 12, 'searchby' => __('the maximum messages quantity', 'contact-manager')),
'message_confirmation_email_sent' => array('type' => 'text', 'modules' => array('message-confirmation-email'), 'name' => __('Message confirmation email sent', 'contact-manager'), 'width' => 15),
'message_confirmation_email_sender' => array('type' => 'text', 'modules' => array('message-confirmation-email'), 'name' => __('Sender of the message confirmation email', 'contact-manager'), 'width' => 15),
'message_confirmation_email_receiver' => array('type' => 'text', 'modules' => array('message-confirmation-email'), 'name' => __('Receiver of the message confirmation email', 'contact-manager'), 'width' => 15),
'message_confirmation_email_subject' => array('type' => 'text', 'modules' => array('message-confirmation-email'), 'name' => __('Subject of the message confirmation email', 'contact-manager'), 'width' => 15),
'message_confirmation_email_body' => array('type' => 'text', 'modules' => array('message-confirmation-email'), 'name' => __('Body of the message confirmation email', 'contact-manager'), 'width' => 18),
'message_notification_email_sent' => array('type' => 'text', 'modules' => array('message-notification-email'), 'name' => __('Message notification email sent', 'contact-manager'), 'width' => 15),
'message_notification_email_sender' => array('type' => 'text', 'modules' => array('message-notification-email'), 'name' => __('Sender of the message notification email', 'contact-manager'), 'width' => 15),
'message_notification_email_receiver' => array('type' => 'text', 'modules' => array('message-notification-email'), 'name' => __('Receiver of the message notification email', 'contact-manager'), 'width' => 15),
'message_notification_email_subject' => array('type' => 'text', 'modules' => array('message-notification-email'), 'name' => __('Subject of the message notification email', 'contact-manager'), 'width' => 15),
'message_notification_email_body' => array('type' => 'text', 'modules' => array('message-notification-email'), 'name' => __('Body of the message notification email', 'contact-manager'), 'width' => 18),
'sender_subscribed_to_autoresponder' => array('type' => 'text', 'modules' => array('autoresponders'), 'name' => __('Senders subscribed to an autoresponder list', 'contact-manager'), 'width' => 18),
'sender_autoresponder' => array('type' => 'text', 'modules' => array('autoresponders'), 'name' => __('Senders autoresponder', 'contact-manager'), 'width' => 12, 'searchby' => __('the senders autoresponder', 'contact-manager')),
'sender_autoresponder_list' => array('type' => 'text', 'modules' => array('autoresponders'), 'name' => __('Senders autoresponder list', 'contact-manager'), 'width' => 15, 'searchby' => __('the senders autoresponder list', 'contact-manager')),
'sender_subscribed_as_a_client' => array('type' => 'text', 'modules' => array('registration-as-a-client'), 'name' => __('Senders subscribed as clients', 'contact-manager'), 'width' => 18),
'sender_client_category_id' => array('type' => 'text', 'modules' => array('registration-as-a-client'), 'name' => __('Senders client category ID', 'contact-manager'), 'width' => 15),
'sender_client_status' => array('type' => 'text', 'modules' => array('registration-as-a-client'), 'name' => __('Senders client status', 'contact-manager'), 'width' => 15),
'commerce_registration_confirmation_email_sent' => array('type' => 'text', 'modules' => array('registration-as-a-client'), 'name' => __('Registration confirmation email sent', 'contact-manager').' '.__('(Commerce)', 'contact-manager'), 'width' => 15),
'commerce_registration_notification_email_sent' => array('type' => 'text', 'modules' => array('registration-as-a-client'), 'name' => __('Registration notification email sent', 'contact-manager').' '.__('(Commerce)', 'contact-manager'), 'width' => 15),
'sender_subscribed_to_affiliate_program' => array('type' => 'text', 'modules' => array('registration-to-affiliate-program'), 'name' => __('Senders subscribed to affiliate program', 'contact-manager'), 'width' => 18),
'sender_affiliate_category_id' => array('type' => 'text', 'modules' => array('registration-to-affiliate-program'), 'name' => __('Senders affiliate category ID', 'contact-manager'), 'width' => 15),
'sender_affiliate_status' => array('type' => 'text', 'modules' => array('registration-to-affiliate-program'), 'name' => __('Senders affiliate status', 'contact-manager'), 'width' => 15),
'affiliation_registration_confirmation_email_sent' => array('type' => 'text', 'modules' => array('registration-to-affiliate-program'), 'name' => __('Registration confirmation email sent', 'contact-manager').' '.__('(Affiliation)', 'contact-manager'), 'width' => 15),
'affiliation_registration_notification_email_sent' => array('type' => 'text', 'modules' => array('registration-to-affiliate-program'), 'name' => __('Registration notification email sent', 'contact-manager').' '.__('(Affiliation)', 'contact-manager'), 'width' => 15),
'sender_subscribed_to_members_areas' => array('type' => 'text', 'modules' => array('membership'), 'name' => __('Senders subscribed to a member area', 'contact-manager'), 'width' => 18),
'sender_members_areas' => array('type' => 'text', 'modules' => array('membership'), 'name' => __('Senders members areas', 'contact-manager'), 'width' => 12, 'searchby' => __('the senders members areas', 'contact-manager')),
'sender_member_category_id' => array('type' => 'text', 'modules' => array('membership'), 'name' => __('Senders member category ID', 'contact-manager'), 'width' => 15),
'sender_member_status' => array('type' => 'text', 'modules' => array('membership'), 'name' => __('Senders member status', 'contact-manager'), 'width' => 15),
'membership_registration_confirmation_email_sent' => array('type' => 'text', 'modules' => array('membership'), 'name' => __('Registration confirmation email sent', 'contact-manager').' '.__('(Membership)', 'contact-manager'), 'width' => 15),
'membership_registration_notification_email_sent' => array('type' => 'text', 'modules' => array('membership'), 'name' => __('Registration notification email sent', 'contact-manager').' '.__('(Membership)', 'contact-manager'), 'width' => 15),
'sender_subscribed_as_a_user' => array('type' => 'text', 'modules' => array('wordpress'), 'name' => __('Senders subscribed as users', 'contact-manager'), 'width' => 18),
'sender_user_role' => array('type' => 'text', 'modules' => array('wordpress'), 'name' => __('Senders user role', 'contact-manager'), 'width' => 15),
'message_custom_instructions_executed' => array('type' => 'text', 'modules' => array('custom-instructions'), 'name' => __('Custom instructions executed', 'contact-manager'), 'width' => 15),
'message_custom_instructions' => array('type' => 'text', 'modules' => array('custom-instructions'), 'name' => __('PHP code of the custom instructions', 'contact-manager'), 'width' => 18),
'affiliation_enabled' => array('type' => 'text', 'modules' => array('affiliation', 'level-1-commission'), 'name' => __('Affiliation enabled', 'contact-manager'), 'width' => 12),
'commission_amount' => array('type' => 'text', 'modules' => array('affiliation', 'level-1-commission'), 'name' => __('Commission amount', 'contact-manager'), 'width' => 12, 'searchby' => __('the commission amount', 'contact-manager')),
'commission2_enabled' => array('type' => 'text', 'modules' => array('affiliation', 'level-2-commission'), 'name' => __('Commission enabled', 'contact-manager').' '.__('(level 2)', 'contact-manager'), 'width' => 12),
'commission2_amount' => array('type' => 'text', 'modules' => array('affiliation', 'level-2-commission'), 'name' => __('Commission amount', 'contact-manager').' '.__('(level 2)', 'contact-manager'), 'width' => 15, 'searchby' => __('the commission amount', 'contact-manager').' '.__('(level 2)', 'contact-manager')));

$tables['forms_categories'] = $tables['forms'];
foreach (array('maximum_messages_quantity_per_sender', 'displays_count', 'messages_count') as $field) { unset($tables['forms_categories'][$field]); }

$tables['messages'] = array(
'id' => array('type' => 'int', 'modules' => array('general-informations'), 'name' => __('ID', 'contact-manager'), 'width' => 5),
'form_id' => array('type' => 'int', 'modules' => array('general-informations'), 'name' => __('Form ID', 'contact-manager'), 'width' => 9),
'receiver' => array('type' => 'text', 'modules' => array('general-informations'), 'name' => __('Receiver', 'contact-manager'), 'width' => 15, 'searchby' => __('the receiver', 'contact-manager')),
'subject' => array('type' => 'text', 'modules' => array('general-informations'), 'name' => __('Subject', 'contact-manager'), 'width' => 15, 'searchby' => __('the subject', 'contact-manager')),
'content' => array('type' => 'text', 'modules' => array('general-informations'), 'name' => __('Content', 'contact-manager'), 'width' => 18, 'searchby' => __('the content', 'contact-manager')),
'keywords' => array('type' => 'text', 'modules' => array('general-informations'), 'name' => __('Keywords', 'contact-manager'), 'width' => 18, 'searchby' => __('the keywords', 'contact-manager')),
'date' => array('type' => 'datetime', 'modules' => array('general-informations'), 'name' => __('Date', 'contact-manager'), 'width' => 18, 'searchby' => __('the date', 'contact-manager')),
'date_utc' => array('type' => 'datetime', 'modules' => array('general-informations'), 'name' => __('Date (UTC)', 'contact-manager'), 'width' => 18, 'searchby' => __('the date (UTC)', 'contact-manager')),
'first_name' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('First name', 'contact-manager'), 'width' => 12, 'searchby' => __('the first name', 'contact-manager')),
'last_name' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Last name', 'contact-manager'), 'width' => 12, 'searchby' => __('the last name', 'contact-manager')),
'email_address' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Email address', 'contact-manager'), 'width' => 15, 'searchby' => __('the email address', 'contact-manager')),
'website_name' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Website', 'contact-manager'), 'width' => 15, 'searchby' => __('the website name', 'contact-manager')),
'website_url' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Website URL', 'contact-manager'), 'width' => 18, 'searchby' => __('the website URL', 'contact-manager')),
'address' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Address', 'contact-manager'), 'width' => 15, 'searchby' => __('the address', 'contact-manager')),
'postcode' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Postcode', 'contact-manager'), 'width' => 9, 'searchby' => __('the postcode', 'contact-manager')),
'town' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Town', 'contact-manager'), 'width' => 12, 'searchby' => __('the town', 'contact-manager')),
'country' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Country', 'contact-manager'), 'width' => 12, 'searchby' => __('the country', 'contact-manager')),
'phone_number' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Phone number', 'contact-manager'), 'width' => 12, 'searchby' => __('the phone number', 'contact-manager')),
'ip_address' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('IP address', 'contact-manager'), 'width' => 12, 'searchby' => __('the IP address', 'contact-manager')),
'user_agent' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('User agent', 'contact-manager'), 'width' => 24, 'searchby' => __('the user agent', 'contact-manager')),
'referring_url' => array('type' => 'text', 'modules' => array('sender'), 'name' => __('Referring URL', 'contact-manager'), 'width' => 18, 'searchby' => __('the referring URL', 'contact-manager')),
'referrer' => array('type' => 'text', 'modules' => array('affiliation', 'level-1-commission'), 'name' => __('Referrer', 'contact-manager'), 'width' => 12, 'searchby' => __('the referrer', 'contact-manager')),
'commission_amount' => array('type' => 'dec(12,2)', 'modules' => array('affiliation', 'level-1-commission'), 'name' => __('Commission amount', 'contact-manager'), 'width' => 12, 'searchby' => __('the commission amount', 'contact-manager')),
'commission_status' => array('type' => 'text', 'modules' => array('affiliation', 'level-1-commission'), 'name' => __('Commission status', 'contact-manager'), 'width' => 12),
'commission_payment_date' => array('type' => 'datetime', 'modules' => array('affiliation', 'level-1-commission'), 'name' => __('Commission\'s payment date', 'contact-manager'), 'width' => 18, 'searchby' => __('the commission\'s payment date', 'contact-manager')),
'commission_payment_date_utc' => array('type' => 'datetime', 'modules' => array('affiliation', 'level-1-commission'), 'name' => __('Commission\'s payment date (UTC)', 'contact-manager'), 'width' => 18, 'searchby' => __('the commission\'s payment date (UTC)', 'contact-manager')),
'referrer2' => array('type' => 'text', 'modules' => array('affiliation', 'level-2-commission'), 'name' => __('Referrer', 'contact-manager').' '.__('(level 2)', 'contact-manager'), 'width' => 15, 'searchby' => __('the referrer', 'contact-manager').' '.__('(level 2)', 'contact-manager')),
'commission2_amount' => array('type' => 'dec(12,2)', 'modules' => array('affiliation', 'level-2-commission'), 'name' => __('Commission amount', 'contact-manager').' '.__('(level 2)', 'contact-manager'), 'width' => 15, 'searchby' => __('the commission amount', 'contact-manager').' '.__('(level 2)', 'contact-manager')),
'commission2_status' => array('type' => 'text', 'modules' => array('affiliation', 'level-2-commission'), 'name' => __('Commission status', 'contact-manager').' '.__('(level 2)', 'contact-manager'), 'width' => 15),
'commission2_payment_date' => array('type' => 'datetime', 'modules' => array('affiliation', 'level-2-commission'), 'name' => __('Commission\'s payment date', 'contact-manager').' '.__('(level 2)', 'contact-manager'), 'width' => 18, 'searchby' => __('the commission\'s payment date', 'contact-manager').' '.__('(level 2)', 'contact-manager')),
'commission2_payment_date_utc' => array('type' => 'datetime', 'modules' => array('affiliation', 'level-2-commission'), 'name' => __('Commission\'s payment date (UTC)', 'contact-manager').' '.__('(level 2)', 'contact-manager'), 'width' => 18, 'searchby' => __('the commission\'s payment date (UTC)', 'contact-manager').' '.__('(level 2)', 'contact-manager')));