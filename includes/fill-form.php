<?php switch ($admin_page) {
case 'form': case 'form_category':
if ($is_category) { $table_slug = 'forms_categories'; $attribute = 'category'; }
else { $table_slug = 'forms'; $attribute = 'id'; }
foreach ($tables[$table_slug] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
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
$n = count($keywords); for ($i = 0; $i < $n; $i++) { $keywords[$i] = strtolower(trim($keywords[$i])); }
sort($keywords);
foreach ($keywords as $keyword) { if ($keyword != '') { $keywords_list .= $keyword.', '; } }
$_POST['keywords'] = (string) substr($keywords_list, 0, -2);
if (!$is_category) {
switch (strtolower($_POST['maximum_messages_quantity_per_sender'])) { case '': case 'i': case 'infinite': case 'u': case 'unlimited': $_POST['maximum_messages_quantity_per_sender'] = (isset($_POST['submit']) ? 'unlimited' : ''); } }
switch (strtolower($_POST['maximum_messages_quantity'])) { case 'i': case 'infinite': case 'u': case 'unlimited': $_POST['maximum_messages_quantity'] = (isset($_POST['submit']) ? 'unlimited' : 'i'); }
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $_POST['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
sort($members_areas, SORT_NUMERIC);
$members_areas_list = '';
foreach ($members_areas as $member_area) { $members_areas_list .= $member_area.', '; }
$_POST['sender_members_areas'] = (string) substr($members_areas_list, 0, -2);
$_POST['sender_members_areas_modifications'] = contact_manager_format_members_areas_modifications($_POST['sender_members_areas_modifications']);
if ($_POST['date'] == '') {
$_POST['date'] = $current_date;
$_POST['date_utc'] = $current_date_utc; }
else {
$d = preg_split('#[^0-9]#', $_POST['date'], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$time = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
$_POST['date'] = date('Y-m-d H:i:s', $time);
$_POST['date_utc'] = date('Y-m-d H:i:s', $time - 3600*UTC_OFFSET); }
$custom_fields = (array) $back_office_options[$admin_page.'_page_custom_fields'];
$item_custom_fields = array();
foreach ($custom_fields as $key => $value) {
$_POST['custom_field_'.$key] = (isset($_POST['custom_field_'.$key]) ? str_replace('\\', '', $_POST['custom_field_'.$key]) : '');
if ($_POST['custom_field_'.$key] != '') { $item_custom_fields[$key] = $_POST['custom_field_'.$key]; } }
if ($item_custom_fields != array()) { $_POST['custom_fields'] = serialize($item_custom_fields); }
if (!$is_category) {
if ($_POST['displays_count'] < $_POST['messages_count']) { $_POST['displays_count'] = $_POST['messages_count']; } }

if (!isset($_GET['id'])) {
if ($_POST['name'] == '') { $error .= ' '.__('Please fill out the required fields.', 'contact-manager'); }
elseif ($is_category) {
$result = $wpdb->get_results("SELECT name FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE name = '".str_replace("'", "''", $_POST['name'])."'", OBJECT);
if ($result) { $_POST['name_error'] = __('This name is not available.', 'contact-manager'); $error .= ' '.$_POST['name_error']; } }
if (($error == '') && (isset($_POST['submit']))) {
if ($is_category) { $result = false; }
else { $result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_forms WHERE name = '".str_replace("'", "''", $_POST['name'])."' AND date = '".$_POST['date']."'", OBJECT); }
if (!$result) {
$updated = true;
$sql = contact_sql_array($tables[$table_slug], $_POST);
$keys_list = ''; $values_list = '';
foreach ($tables[$table_slug] as $key => $value) { if ($key != 'id') { $keys_list .= $key.","; $values_list .= $sql[$key].","; } }
$results = $wpdb->query("INSERT INTO ".$wpdb->prefix."contact_manager_".$table_slug." (".substr($keys_list, 0, -1).") VALUES(".substr($values_list, 0, -1).")"); } } }

if (isset($_GET['id'])) {
if (isset($_POST['submit'])) {
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
	messages_count = ".$messages_count." WHERE id = ".$form->id); } } } }
if ($_POST['name'] != '') {
if ((!$is_category) && (isset($_POST['submit']))) { $results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_".$table_slug." SET name = '".str_replace("'", "''", $_POST['name'])."' WHERE id = ".$_GET['id']); }
else {
$result = $wpdb->get_results("SELECT name FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE name = '".str_replace("'", "''", $_POST['name'])."' AND id != ".$_GET['id'], OBJECT);
if ($result) { $_POST['name_error'] = __('This name is not available.', 'contact-manager'); $error .= ' '.$_POST['name_error']; }
elseif (isset($_POST['submit'])) { $results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms_categories SET name = '".str_replace("'", "''", $_POST['name'])."' WHERE id = ".$_GET['id']); } } }
if (isset($_POST['submit'])) {
$sql = contact_sql_array($tables[$table_slug], $_POST);
$list = '';
foreach ($tables[$table_slug] as $key => $value) { switch ($key) {
case 'id': case 'name': break;
default: $list .= $key." = ".$sql[$key].","; } }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_".$table_slug." SET ".substr($list, 0, -1)." WHERE id = ".$_GET['id']); } }

if (!isset($_POST['submit'])) {
$fields = array(); $default_options = array();
foreach ($tables[$table_slug] as $key => $value) { $fields[] = $key; }
foreach ($custom_fields as $key => $value) { $fields[] = 'custom_field_'.$key; }
foreach ($fields as $field) {
if (!in_array($field, array('id', 'category_id', 'date', 'date_utc', 'custom_fields', 'name'))) {
$default_options[$field] = contact_form_category_data(array(0 => $field, 'formatting' => 'no', 'id' => $_POST['category_id']));
if (($default_options[$field] === 'unlimited') && ($field == 'maximum_messages_quantity')) { $default_options[$field] = 'i'; } } }
foreach ($default_options as $key => $value) { $_POST[$key.'_default_value'] = $value; }

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

foreach ($default_options_select_fields as $field) { if (isset($default_options[$field])) { $_POST[$field.'_default_option_content'] = contact_manager_pages_selector_default_option_content($field, $default_options[$field]); } }

foreach ($ids_fields as $field) {
$applied_value = ($_POST[$field] === '' ? (isset($default_options[$field]) ? $default_options[$field] : '') : $_POST[$field]);
$_POST[$field.'_description'] = contact_manager_pages_field_description($field, $applied_value);
$_POST[$field.'_links'] = contact_manager_pages_field_links($back_office_options, $field, $applied_value); }

foreach ($modules[$admin_page] as $key => $value) {
$_POST[str_replace('-', '_', $key).'_module_description'] = contact_manager_pages_module_description($back_office_options, $key);
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) {
$_POST[str_replace('-', '_', $module_key).'_module_description'] = contact_manager_pages_module_description($back_office_options, $module_key); } } } }
break;


case 'message':
foreach ($tables['messages'] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
$_POST['form_id'] = (int) $_POST['form_id'];
if ($_POST['form_id'] < 1) {
$result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_forms ORDER BY messages_count DESC LIMIT 1", OBJECT);
if ($result) { $_POST['form_id'] = $result->id; } else { $_POST['form_id'] = 1; } }
$GLOBALS['contact_form_id'] = $_POST['form_id'];
$GLOBALS['contact_form_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = ".$_POST['form_id'], OBJECT);
if ($_POST['receiver'] == '') { $_POST['receiver'] = contact_form_data('message_notification_email_receiver'); }
if (isset($_POST['submit'])) { foreach (array('subject', 'content') as $field) { $_POST[$field] = str_replace(array('[', ']'), array('&#91;', '&#93;'), $_POST[$field]); } }
$keywords = explode(',', $_POST['keywords']);
$keywords_list = '';
$n = count($keywords); for ($i = 0; $i < $n; $i++) { $keywords[$i] = strtolower(trim($keywords[$i])); }
sort($keywords);
foreach ($keywords as $keyword) { if ($keyword != '') { if ($keyword != '') { $keywords_list .= $keyword.', '; } } }
$_POST['keywords'] = (string) substr($keywords_list, 0, -2);
if ($_POST['date'] == '') {
$_POST['date'] = $current_date;
$_POST['date_utc'] = $current_date_utc; }
else {
$d = preg_split('#[^0-9]#', $_POST['date'], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$time = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
$_POST['date'] = date('Y-m-d H:i:s', $time);
$_POST['date_utc'] = date('Y-m-d H:i:s', $time - 3600*UTC_OFFSET); }
$_POST['email_address'] = format_email_address($_POST['email_address']);
$custom_fields = (array) $back_office_options['message_page_custom_fields'];
$item_custom_fields = array();
foreach ($custom_fields as $key => $value) {
$_POST['custom_field_'.$key] = (isset($_POST['custom_field_'.$key]) ? str_replace('\\', '', $_POST['custom_field_'.$key]) : '');
if ($_POST['custom_field_'.$key] != '') { $item_custom_fields[$key] = $_POST['custom_field_'.$key]; } }
if ($item_custom_fields != array()) { $_POST['custom_fields'] = serialize($item_custom_fields); }
if ($_POST['referrer'] != '') {
if (is_numeric($_POST['referrer'])) {
$_POST['referrer'] = preg_replace('/[^0-9]/', '', $_POST['referrer']);
if (get_option('affiliation_manager')) {
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE id = ".$_POST['referrer'], OBJECT);
if ($result) { $_POST['referrer'] = $result->login; } } }
if (strstr($_POST['referrer'], '@')) {
$_POST['referrer'] = format_email_address($_POST['referrer']);
if (get_option('affiliation_manager')) {
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE (email_address = '".$_POST['referrer']."' OR paypal_email_address = '".$_POST['referrer']."')", OBJECT);
if ($result) { $_POST['referrer'] = $result->login; } } }
else {
$_POST['referrer'] = format_nice_name($_POST['referrer']);
if (is_numeric($_POST['referrer'])) { $_POST['referrer'] = ''; } } }
if (($_POST['referrer'] == '') || (strstr($_POST['referrer'], '@'))) {
$_POST['commission_amount'] = 0;
$_POST['commission_status'] = '';
if (isset($_POST['submit'])) { $_POST['commission_payment_date'] = ''; } }
else {
$GLOBALS['referrer'] = $_POST['referrer'];
if ((function_exists('award_commission')) && ($_POST['commission_amount'] == '')) {
if (contact_form_data('affiliation_enabled') == 'no') { $_POST['commission_amount'] = 0; }
else {
$GLOBALS['affiliate_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer']."'", OBJECT);
if ((!isset($GLOBALS['affiliate_data']['status'])) || ($GLOBALS['affiliate_data']['status'] != 'active')) { $_POST['commission_amount'] = 0; }
else { $_POST['commission_amount'] = contact_form_data('commission_amount'); } } }
else { $_POST['commission_amount'] = str_replace(array('?', ',', ';'), '.', $_POST['commission_amount']); }
$_POST['commission_amount'] = round($_POST['commission_amount'], 2); if ($_POST['commission_amount'] <= 0) { $_POST['commission_amount'] = 0; }
if ($_POST['commission_amount'] == 0) {
$_POST['commission_status'] = '';
if (isset($_POST['submit'])) { $_POST['commission_payment_date'] = ''; } }
elseif ($_POST['commission_status'] == '') { $_POST['commission_status'] = 'unpaid'; }
if ($_POST['commission_status'] == 'paid') {
if ($_POST['commission_payment_date'] == '') {
$_POST['commission_payment_date'] = (isset($_GET['id']) ? $current_date : $_POST['date']);
$_POST['commission_payment_date_utc'] = (isset($_GET['id']) ? $current_date_utc : $_POST['date_utc']); }
else {
$d = preg_split('#[^0-9]#', $_POST['commission_payment_date'], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$time = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
$_POST['commission_payment_date'] = date('Y-m-d H:i:s', $time);
$_POST['commission_payment_date_utc'] = date('Y-m-d H:i:s', $time - 3600*UTC_OFFSET); } }
elseif (isset($_POST['submit'])) { $_POST['commission_payment_date'] = ''; } }
if (($_POST['referrer2'] == '') && ($_POST['referrer2_emptied'] != 'yes') && ($_POST['referrer'] != '') && (get_option('affiliation_manager'))) {
$result = $wpdb->get_row("SELECT referrer FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer']."'", OBJECT);
if ($result) { $_POST['referrer2'] = $result->referrer; } }
else {
if (is_numeric($_POST['referrer2'])) {
$_POST['referrer2'] = preg_replace('/[^0-9]/', '', $_POST['referrer2']);
if (get_option('affiliation_manager')) {
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE id = ".$_POST['referrer2'], OBJECT);
if ($result) { $_POST['referrer2'] = $result->login; } } }
if (strstr($_POST['referrer2'], '@')) {
$_POST['referrer2'] = format_email_address($_POST['referrer2']);
if (get_option('affiliation_manager')) {
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE (email_address = '".$_POST['referrer2']."' OR paypal_email_address = '".$_POST['referrer2']."')", OBJECT);
if ($result) { $_POST['referrer2'] = $result->login; } } }
else {
$_POST['referrer2'] = format_nice_name($_POST['referrer2']);
if (is_numeric($_POST['referrer2'])) { $_POST['referrer2'] = ''; } } }
if (($_POST['referrer2'] == '') || (strstr($_POST['referrer2'], '@'))) {
$_POST['commission2_amount'] = 0;
$_POST['commission2_status'] = '';
if (isset($_POST['submit'])) { $_POST['commission2_payment_date'] = ''; } }
else {
if ((function_exists('award_commission')) && ($_POST['commission2_amount'] == '')) {
if ((contact_form_data('affiliation_enabled') == 'no') || (contact_form_data('commission2_enabled') == 'no')) { $_POST['commission2_amount'] = 0; }
else {
if (isset($GLOBALS['affiliate_data'])) { $original['affiliate_data'] = $GLOBALS['affiliate_data']; }
$GLOBALS['affiliate_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer2']."'", OBJECT);
if ((!isset($GLOBALS['affiliate_data']['status'])) || ($GLOBALS['affiliate_data']['status'] != 'active')) { $_POST['commission2_amount'] = 0; }
else { $_POST['commission2_amount'] = contact_form_data('commission2_amount'); }
if (isset($original['affiliate_data'])) { $GLOBALS['affiliate_data'] = $original['affiliate_data']; } } }
else { $_POST['commission2_amount'] = str_replace(array('?', ',', ';'), '.', $_POST['commission2_amount']); }
$_POST['commission2_amount'] = round($_POST['commission2_amount'], 2); if ($_POST['commission2_amount'] <= 0) { $_POST['commission2_amount'] = 0; }
if ($_POST['commission2_amount'] == 0) {
$_POST['commission2_status'] = '';
if (isset($_POST['submit'])) { $_POST['commission2_payment_date'] = ''; } }
elseif ($_POST['commission2_status'] == '') { $_POST['commission2_status'] = 'unpaid'; }
if ($_POST['commission2_status'] == 'paid') {
if ($_POST['commission2_payment_date'] == '') {
$_POST['commission2_payment_date'] = (isset($_GET['id']) ? $current_date : $_POST['date']);
$_POST['commission2_payment_date_utc'] = (isset($_GET['id']) ? $current_date_utc : $_POST['date_utc']); }
else {
$d = preg_split('#[^0-9]#', $_POST['commission2_payment_date'], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$time = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
$_POST['commission2_payment_date'] = date('Y-m-d H:i:s', $time);
$_POST['commission2_payment_date_utc'] = date('Y-m-d H:i:s', $time - 3600*UTC_OFFSET); } }
elseif (isset($_POST['submit'])) { $_POST['commission2_payment_date'] = ''; } }
foreach ($tables['messages'] as $key => $value) { if ((isset($value['type'])) && ($value['type'] == 'dec(12,2)')) { $_POST[$key] = number_format((float) $_POST[$key], 2, '.', ''); } }

if (!isset($_GET['id'])) {
if (($_POST['referring_url'] == '') && ($_POST['referring_url_emptied'] != 'yes')) { $_POST['referring_url'] = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''); }
if (isset($_POST['update_fields'])) {
foreach ($_POST as $key => $value) { $GLOBALS['message_data'][$key] = $value; }
foreach (array(
'affiliate',
'affiliation-activation-url',
'client',
'commerce-activation-url',
'member',
'membership-activation-url',
'message',
'message-commission',
'sender',
'user') as $tag) { remove_shortcode($tag); }
if (($_POST['referrer'] != '') && (function_exists('affiliate_data'))) {
$GLOBALS['referrer_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer']."'", OBJECT);
$GLOBALS['affiliate_data'] = $GLOBALS['referrer_data']; }
foreach ($add_message_fields as $field) { if ((isset($_POST['submit'])) || (!isset($_POST[$field]))) { $_POST[$field] = contact_form_data($field); } }
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $_POST['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
sort($members_areas, SORT_NUMERIC);
$members_areas_list = '';
foreach ($members_areas as $member_area) { $members_areas_list .= $member_area.', '; }
$_POST['sender_members_areas'] = (string) substr($members_areas_list, 0, -2);
$_POST['sender_members_areas_modifications'] = contact_manager_format_members_areas_modifications($_POST['sender_members_areas_modifications']);
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
foreach (array('category_id', 'status') as $field) {
if (($_POST['sender_client_'.$field] == '') && (function_exists('commerce_data'))) { $_POST['sender_client_'.$field] = commerce_data('clients_initial_'.$field); }
if (($_POST['sender_affiliate_'.$field] == '') && (function_exists('affiliation_data'))) { $_POST['sender_affiliate_'.$field] = affiliation_data('affiliates_initial_'.$field); }
if (($_POST['sender_member_'.$field] == '') && (function_exists('member_area_data'))) { $_POST['sender_member_'.$field] = member_area_data('members_initial_'.$field); } }
foreach (array('confirmation', 'notification') as $action) {
if ($_POST['sender_subscribed_as_a_client'] != 'yes') { $_POST['commerce_registration_'.$action.'_email_sent'] = 'no'; }
elseif (($_POST['commerce_registration_'.$action.'_email_sent'] == '') && (function_exists('commerce_data'))) { $_POST['commerce_registration_'.$action.'_email_sent'] = commerce_data('registration_'.$action.'_email_sent'); }
if ($_POST['sender_subscribed_to_affiliate_program'] != 'yes') { $_POST['affiliation_registration_'.$action.'_email_sent'] = 'no'; }
elseif (($_POST['affiliation_registration_'.$action.'_email_sent'] == '') && (function_exists('affiliation_data'))) { $_POST['affiliation_registration_'.$action.'_email_sent'] = affiliation_data('registration_'.$action.'_email_sent'); }
if ($_POST['sender_subscribed_to_members_areas'] != 'yes') { $_POST['membership_registration_'.$action.'_email_sent'] = 'no'; }
elseif (($_POST['membership_registration_'.$action.'_email_sent'] == '') && (function_exists('member_area_data'))) { $_POST['membership_registration_'.$action.'_email_sent'] = member_area_data('registration_'.$action.'_email_sent'); } }
foreach (array('message', 'sender') as $tag) { add_shortcode($tag, 'message_data'); }
foreach ($add_message_fields as $field) {
$_POST[$field] = str_replace(array('{message id', '{sender id'), array('[message id', '[sender id'),
do_shortcode(str_replace(array('[message id', '[sender id'), array('{message id', '{sender id'), $_POST[$field]))); }
foreach (array('receiver', 'subject') as $field) { if ($_POST[$field] != '') { $_POST['message_notification_email_'.$field] = $_POST[$field]; } } }
elseif (isset($_POST['submit'])) {
$fields = array();
if (!isset($_POST['sender_client_status'])) { $fields = array_merge($fields, array(
'sender_subscribed_as_a_client',
'sender_client_category_id',
'sender_client_status')); }
if (!isset($_POST['sender_affiliate_status'])) { $fields = array_merge($fields, array(
'sender_subscribed_to_affiliate_program',
'sender_affiliate_category_id',
'sender_affiliate_status')); }
if (!isset($_POST['sender_member_status'])) { $fields = array_merge($fields, array(
'sender_subscribed_to_members_areas',
'sender_members_areas',
'sender_members_areas_modifications',
'sender_member_category_id',
'sender_member_status')); }
if (!isset($_POST['sender_user_role'])) { $fields = array_merge($fields, array(
'sender_subscribed_as_a_user',
'sender_user_role')); }
if (!isset($_POST['message_custom_instructions'])) { $fields = array_merge($fields, array(
'message_custom_instructions_executed',
'message_custom_instructions')); }
foreach ($fields as $field) { $_POST[$field] = contact_form_data($field); }
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $_POST['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
sort($members_areas, SORT_NUMERIC);
$members_areas_list = '';
foreach ($members_areas as $member_area) { $members_areas_list .= $member_area.', '; }
$_POST['sender_members_areas'] = (string) substr($members_areas_list, 0, -2);
$_POST['sender_members_areas_modifications'] = contact_manager_format_members_areas_modifications($_POST['sender_members_areas_modifications']);
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
foreach (array('category_id', 'status') as $field) {
if (($_POST['sender_client_'.$field] == '') && (function_exists('commerce_data'))) { $_POST['sender_client_'.$field] = commerce_data('clients_initial_'.$field); }
if (($_POST['sender_affiliate_'.$field] == '') && (function_exists('affiliation_data'))) { $_POST['sender_affiliate_'.$field] = affiliation_data('affiliates_initial_'.$field); }
if (($_POST['sender_member_'.$field] == '') && (function_exists('member_area_data'))) { $_POST['sender_member_'.$field] = member_area_data('members_initial_'.$field); } }
if ($error == '') {
$result = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix."contact_manager_messages WHERE email_address = '".$_POST['email_address']."' AND subject = '".str_replace("'", "''", $_POST['subject'])."' AND content = '".str_replace("'", "''", $_POST['content'])."' AND date = '".$_POST['date']."'", OBJECT);
if (!$result) { $updated = true; add_message($_POST); } } } }

if (isset($_GET['id'])) {
if (isset($_POST['submit'])) {
$updated = true;
$message_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$_GET['id'], OBJECT);
$sql = contact_sql_array($tables['messages'], $_POST);
$list = '';
foreach ($tables['messages'] as $key => $value) { switch ($key) {
case 'id': break;
default: $list .= $key." = ".$sql[$key].","; } }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_messages SET ".substr($list, 0, -1)." WHERE id = ".$_GET['id']);

if ($_POST['form_id'] != $message_data->form_id) {
if ($message_data->form_id > 0) {
$contact_form_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = ".$message_data->form_id, OBJECT);
$GLOBALS['contact_form'.$message_data->form_id.'_data'] = (array) $contact_form_data;
$messages_count = $contact_form_data->messages_count - 1;
if ($messages_count < 0) { $messages_count = 0; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET messages_count = ".$messages_count." WHERE id = ".$message_data->form_id);
$GLOBALS['contact_form'.$message_data->form_id.'_data']['messages_count'] = $messages_count; }

if ($_POST['form_id'] > 0) {
$displays_count = $GLOBALS['contact_form_data']['displays_count'];
$messages_count = $GLOBALS['contact_form_data']['messages_count'] + 1;
if ($displays_count < $messages_count) { $displays_count = $messages_count; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET
	displays_count = ".$displays_count.",
	messages_count = ".$messages_count." WHERE id = ".$_POST['form_id']);
foreach (array('', $GLOBALS['contact_form_id']) as $string) {
$GLOBALS['contact_form'.$string.'_data'] = (array) (isset($GLOBALS['contact_form'.$string.'_data']) ? $GLOBALS['contact_form'.$string.'_data'] : array());
foreach (array('displays_count', 'messages_count') as $field) { $GLOBALS['contact_form'.$string.'_data'][$field] = $$field; } } } } } }

if (!isset($_POST['submit'])) { foreach ($ids_fields as $field) {
$_POST[$field.'_description'] = contact_manager_pages_field_description($field, $_POST[$field]);
$_POST[$field.'_links'] = contact_manager_pages_field_links($back_office_options, $field, $_POST[$field]); } }
break;


case 'options':
include CONTACT_MANAGER_PATH.'initial-options.php';
foreach ($initial_options[''] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
foreach (array(
'affiliation_enabled',
'automatic_display_enabled',
'automatic_display_only_on_single_post_pages',
'commission2_enabled',
'form_submission_custom_instructions_executed',
'message_confirmation_email_sent',
'message_custom_instructions_executed',
'message_notification_email_sent',
'message_removal_custom_instructions_executed',
'messages_registration_enabled',
'sender_subscribed_as_a_client',
'sender_subscribed_as_a_user',
'sender_subscribed_to_affiliate_program',
'sender_subscribed_to_autoresponder',
'sender_subscribed_to_members_areas') as $field) { if ($_POST[$field] != 'yes') { $_POST[$field] = 'no'; } }
foreach (array(
'automatic_display_form_id') as $field) { $_POST[$field] = (int) $_POST[$field]; if ($_POST[$field] < 1) { $_POST[$field] = $initial_options[''][$field]; } }
foreach (array(
'commission_amount',
'commission2_amount',
'encrypted_urls_validity_duration') as $field) { $_POST[$field] = str_replace(array('?', ',', ';'), '.', $_POST[$field]); }
foreach (array(
'automatic_display_maximum_forms_quantity',
'maximum_messages_quantity') as $field) {
switch (strtolower($_POST[$field])) { case '0': case '': case 'i': case 'infinite': case 'u': case 'unlimited': $_POST[$field] = 'unlimited'; } }
if ((is_numeric($_POST['maximum_messages_quantity'])) && (isset($_POST['submit']))) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages", OBJECT);
$messages_quantity = (int) (isset($row->total) ? $row->total : 0);
$n = $messages_quantity - $_POST['maximum_messages_quantity'];
if ($n > 0) {
$results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_messages ORDER BY date ASC LIMIT $n");
$result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_messages ORDER BY id DESC LIMIT 1", OBJECT);
if (!$result) { $results = $wpdb->query("ALTER TABLE ".$wpdb->prefix."contact_manager_messages AUTO_INCREMENT = 1"); }
else { $results = $wpdb->query("ALTER TABLE ".$wpdb->prefix."contact_manager_messages AUTO_INCREMENT = ".($result->id + 1)); } } }
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $_POST['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
sort($members_areas, SORT_NUMERIC);
$members_areas_list = '';
foreach ($members_areas as $member_area) { $members_areas_list .= $member_area.', '; }
$_POST['sender_members_areas'] = (string) substr($members_areas_list, 0, -2);
$_POST['sender_members_areas_modifications'] = contact_manager_format_members_areas_modifications($_POST['sender_members_areas_modifications']);
foreach ($initial_options[''] as $key => $value) { if ($_POST[$key] == '') { $_POST[$key] = $value; } $options[$key] = $_POST[$key]; }
foreach (array(
'automatic_display_maximum_forms_quantity',
'maximum_messages_quantity') as $field) { if ((!isset($_POST['submit'])) && ($_POST[$field] === 'unlimited')) { $_POST[$field] = ''; } }
if (isset($_POST['submit'])) { update_option('contact_manager', $options); }
foreach ($other_options as $field) {
if ((!isset($_POST[$field])) || ($_POST[$field] == '')) { $_POST[$field] = $initial_options[$field]; }
if (isset($_POST['submit'])) { update_option(substr('contact_manager_'.$field, 0, 64), $_POST[$field]); } }

if (!isset($_POST['submit'])) {
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
foreach ($fields as $field) { $_POST[$field.'_default_option_content'] = contact_manager_pages_selector_default_option_content($field, $default_options[$field]); }

foreach ($ids_fields as $field) {
$applied_value = ($options[$field] === '' ? (isset($default_options[$field]) ? $default_options[$field] : '') : $options[$field]);
$_POST[$field.'_description'] = contact_manager_pages_field_description($field, $applied_value);
$_POST[$field.'_links'] = contact_manager_pages_field_links($back_office_options, $field, $applied_value); } }
break; }