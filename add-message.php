<?php if ((isset($message)) && (is_array($message))) {
global $wpdb;
foreach (array('admin-pages.php', 'tables.php') as $file) { include dirname(__FILE__).'/'.$file; }
foreach ($tables['messages'] as $key => $value) { if (!isset($message[$key])) { $message[$key] = ''; } }
$_GET['contact_form_id'] = (int) $message['form_id'];
if (function_exists('add_affiliate')) {
$_GET['affiliate_id'] = 0;
if ((!is_admin()) && (affiliation_session())) { $_GET['affiliate_id'] = (int) affiliate_data('id'); }
if ($_GET['affiliate_id'] == 0) {
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE email_address = '".$message['email_address']."'", OBJECT);
if (!$result) { $result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE paypal_email_address = '".$message['email_address']."'", OBJECT); }
if ($result) { $_GET['affiliate_data'] = (array) $result; $_GET['affiliate_id'] = $result->id; } } }
if (function_exists('add_client')) {
$_GET['client_id'] = 0;
if ((!is_admin()) && (commerce_session())) { $_GET['client_id'] = (int) client_data('id'); }
if ($_GET['client_id'] == 0) {
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."commerce_manager_clients WHERE email_address = '".$message['email_address']."'", OBJECT);
if (!$result) { $result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."commerce_manager_clients WHERE paypal_email_address = '".$message['email_address']."'", OBJECT); }
if ($result) { $_GET['client_data'] = (array) $result; $_GET['client_id'] = $result->id; } } }
if (function_exists('add_member')) {
$_GET['member_id'] = 0;
if ((!is_admin()) && (membership_session(''))) { $_GET['member_id'] = (int) member_data('id'); }
if ($_GET['member_id'] == 0) {
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."membership_manager_members WHERE email_address = '".$message['email_address']."'", OBJECT);
if ($result) { $_GET['member_data'] = (array) $result; $_GET['member_id'] = $result->id; } } }
$_GET['user_id'] = (int) (isset($_GET['user_id']) ? $_GET['user_id'] : 0);
if ($_GET['user_id'] == 0) {
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->base_prefix."users WHERE user_email = '".$message['email_address']."'", OBJECT);
if ($result) { $_GET['user_data'] = (array) $result; $_GET['user_id'] = $result->ID; } }
if ((is_admin()) || (contact_form_data('messages_registration_enabled') == 'yes')) {
$sql = contact_sql_array($tables['messages'], $message);
$keys_list = ''; $values_list = '';
foreach ($tables['messages'] as $key => $value) { if ($key != 'id') { $keys_list .= $key.","; $values_list .= $sql[$key].","; } }
$result = $wpdb->query("INSERT INTO ".$wpdb->prefix."contact_manager_messages (".substr($keys_list, 0, -1).") VALUES(".substr($values_list, 0, -1).")");
$result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_messages ORDER BY id DESC LIMIT 1", OBJECT);
$message['id'] = $result->id;
if (!is_admin()) {
$maximum_messages_quantity = contact_data('maximum_messages_quantity');
if (is_numeric($maximum_messages_quantity)) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages", OBJECT);
$messages_quantity = (int) (isset($row->total) ? $row->total : 0);
$n = $messages_quantity - $maximum_messages_quantity;
if ($n > 0) { $results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_messages ORDER BY date ASC LIMIT $n"); } }
$maximum_messages_quantity = contact_form_data('maximum_messages_quantity');
if (is_numeric($maximum_messages_quantity)) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$message['form_id'], OBJECT);
$messages_quantity = (int) (isset($row->total) ? $row->total : 0);
$n = $messages_quantity - $maximum_messages_quantity;
if ($n > 0) { $results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$message['form_id']." ORDER BY date ASC LIMIT $n"); } } } }
$_GET['message_data'] = $message;
$original_message = $message;
if ($message['referrer'] != '') {
$_GET['affiliate_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$message['referrer']."'", OBJECT);
$_GET['referrer_data'] = $_GET['affiliate_data']; }
foreach ($add_message_fields as $field) {
if (is_admin()) { $message[$field] = (isset($message[$field]) ? stripslashes(do_shortcode($message[$field])) : ''); }
else { $message[$field] = contact_form_data($field); } }

if ($message['form_id'] > 0) {
$displays_count = contact_form_data('displays_count');
$messages_count = contact_form_data('messages_count') + 1;
if ($displays_count < $messages_count) { $displays_count = $messages_count; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET
	displays_count = ".$displays_count.",
	messages_count = ".$messages_count." WHERE id = ".$message['form_id']); }

if ((function_exists('add_affiliate')) && ($message['sender_subscribed_to_affiliate_program'] == 'yes')) {
if ($_GET['affiliate_id'] > 0) {
if ($message['sender_affiliate_category_id'] > 0) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."affiliation_manager_affiliates SET category_id = ".$message['sender_affiliate_category_id']." WHERE id = ".$_GET['affiliate_id']); } }
elseif ($message['email_address'] != '') {
$affiliate = $message;
if (!isset($affiliate['login'])) { $affiliate['login'] = $affiliate['email_address']; }
$array = explode('@', $affiliate['login']);
$affiliate['login'] = format_nice_name($array[0]);
if (is_numeric($affiliate['login'])) { $affiliate['login'] .= '-'; }
$login = $affiliate['login']; $result = true; $i = 1; while ($result) {
$result = $wpdb->get_results("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$affiliate['login']."'", OBJECT);
if ($result) { $affiliate['login'] = $login.$i; $i = $i + 1; } }
if (!isset($affiliate['password'])) { $affiliate['password'] = substr(md5(mt_rand()), 0, 8); }
if (!isset($affiliate['paypal_email_address'])) { $affiliate['paypal_email_address'] = $affiliate['email_address']; }
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$affiliate['referrer']."' AND status = 'active'", OBJECT);
if (!$result) { $affiliate['referrer'] = ''; }
foreach (array('category_id', 'status') as $field) {
$affiliate[$field] = $message['sender_affiliate_'.$field];
if ($affiliate[$field] == '') { $affiliate[$field] = affiliation_data('affiliates_initial_'.$field); } }
foreach (array('confirmation', 'notification') as $action) {
$affiliate['registration_'.$action.'_email_sent'] = $message['affiliation_registration_'.$action.'_email_sent'];
if ((!is_admin()) && ($affiliate['registration_'.$action.'_email_sent'] == '')) {
$affiliate['registration_'.$action.'_email_sent'] = affiliation_data('registration_'.$action.'_email_sent'); } }
$affiliate['registration_without_form'] = 'yes';
add_affiliate($affiliate); } }

if ((function_exists('add_client')) && ($message['sender_subscribed_as_a_client'] == 'yes')) {
if ($_GET['client_id'] > 0) {
if ($message['sender_client_category_id'] > 0) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."commerce_manager_clients SET category_id = ".$message['sender_client_category_id']." WHERE id = ".$_GET['client_id']); } }
elseif ($message['email_address'] != '') {
if (isset($affiliate)) { $client = $affiliate; }
else { $client = $message; }
if (!isset($client['login'])) { $client['login'] = $client['email_address']; }
$login = $client['login']; $result = true; $i = 1; while ($result) {
$result = $wpdb->get_results("SELECT login FROM ".$wpdb->prefix."commerce_manager_clients WHERE login = '".$client['login']."'", OBJECT);
if ($result) { $client['login'] = $login.$i; $i = $i + 1; } }
if (!isset($client['password'])) { $client['password'] = substr(md5(mt_rand()), 0, 8); }
if (function_exists('add_affiliate')) {
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$client['referrer']."' AND status = 'active'", OBJECT);
if (!$result) { $client['referrer'] = ''; } }
else { $client['referrer'] = ''; }
foreach (array('category_id', 'status') as $field) {
$client[$field] = $message['sender_client_'.$field];
if ($client[$field] == '') { $client[$field] = commerce_data('clients_initial_'.$field); } }
foreach (array('confirmation', 'notification') as $action) {
$client['registration_'.$action.'_email_sent'] = $message['commerce_registration_'.$action.'_email_sent'];
if ((!is_admin()) && ($client['registration_'.$action.'_email_sent'] == '')) {
$client['registration_'.$action.'_email_sent'] = commerce_data('registration_'.$action.'_email_sent'); } }
$client['registration_without_form'] = 'yes';
add_client($client); } }

if ((function_exists('add_member')) && ($message['sender_subscribed_to_members_areas'] == 'yes')) {
if ($_GET['member_id'] > 0) {
update_member_members_areas($_GET['member_id'], $message['sender_members_areas'], 'add');
if (function_exists('update_member_members_areas_modifications')) {
update_member_members_areas_modifications($_GET['member_id'], $message['sender_members_areas_modifications'], 'add'); }
if ($message['sender_member_category_id'] > 0) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."membership_manager_members SET category_id = ".$message['sender_member_category_id']." WHERE id = ".$_GET['member_id']); } }
elseif ($message['email_address'] != '') {
if (isset($affiliate)) { $member = $affiliate; }
elseif (isset($client)) { $member = $client; }
else { $member = $message; }
$member['members_areas'] = $message['sender_members_areas'];
$members_areas = array_unique(preg_split('#[^0-9]#', $member['members_areas'], 0, PREG_SPLIT_NO_EMPTY));
if (count($members_areas) == 1) { $_GET['member_area_id'] = (int) $members_areas[0]; }
elseif (isset($_GET['member_area_id'])) { unset($_GET['member_area_id']); }
$member['members_areas_modifications'] = $message['sender_members_areas_modifications'];
if (!isset($member['login'])) { $member['login'] = $member['email_address']; }
$login = $member['login']; $result = true; $i = 1; while ($result) {
$result = $wpdb->get_results("SELECT login FROM ".$wpdb->prefix."membership_manager_members WHERE login = '".$member['login']."'", OBJECT);
if ($result) { $member['login'] = $login.$i; $i = $i + 1; } }
if (!isset($member['password'])) { $member['password'] = substr(md5(mt_rand()), 0, 8); }
foreach (array('category_id', 'status') as $field) {
$member[$field] = $message['sender_member_'.$field];
if ($member[$field] == '') { $member[$field] = member_area_data('members_initial_'.$field); } }
foreach (array('confirmation', 'notification') as $action) {
$member['registration_'.$action.'_email_sent'] = $message['membership_registration_'.$action.'_email_sent'];
if ((!is_admin()) && ($member['registration_'.$action.'_email_sent'] == '')) {
$member['registration_'.$action.'_email_sent'] = member_area_data('registration_'.$action.'_email_sent'); } }
$member['registration_without_form'] = 'yes';
add_member($member); } }

if ((!defined('CONTACT_MANAGER_DEMO')) || (CONTACT_MANAGER_DEMO == false)) {
if (($_GET['user_id'] == 0) && ($message['sender_subscribed_as_a_user'] == 'yes') && ($message['email_address'] != '')) {
if (isset($affiliate)) { $user = $affiliate; }
elseif (isset($client)) { $user = $client; }
elseif (isset($member)) { $user = $member; }
else { $user = $message; }
$user['role'] = $message['sender_user_role'];
if (!isset($user['login'])) { $user['login'] = $user['email_address']; }
$login = $user['login']; $result = true; $i = 1; while ($result) {
$result = $wpdb->get_results("SELECT user_login FROM ".$wpdb->base_prefix."users WHERE user_login = '".$user['login']."'", OBJECT);
if ($result) { $user['login'] = $login.$i; $i = $i + 1; } }
if (!isset($user['password'])) { $user['password'] = substr(md5(mt_rand()), 0, 8); }
if (isset($user['ID'])) { unset($user['ID']); }
$user['user_login'] = $user['login'];
$user['user_pass'] = $user['password'];
$user['user_email'] = $user['email_address'];
$user['user_url'] = $user['website_url'];
$user['user_registered'] = $user['date_utc'];
$user['display_name'] = $user['first_name'];
$user['ID'] = wp_insert_user($user);
$_GET['user_id'] = $user['ID'];
$_GET['user_data'] = $user; }

foreach ($add_message_fields as $field) {
if (is_admin()) { $message[$field] = (isset($original_message[$field]) ? stripslashes(do_shortcode($original_message[$field])) : ''); }
else { $message[$field] = contact_form_data($field); } }

if (!is_admin()) {
$form_id = $message['form_id'];
if (in_array('message_confirmation_email_sent', $_GET['contact_form'.$form_id.'_fields'])) {
$message['message_confirmation_email_sent'] = (isset($_POST['message_confirmation_email_sent']) ? 'yes' : 'no'); } }

foreach (array('confirmation', 'notification') as $action) {
foreach (array('sent', 'sender', 'receiver', 'subject', 'body') as $field) {
$$field = str_replace(array("\\t", '\\', '&#91;', '&#93;'), array('	', '', '[', ']'), str_replace(array("\\r\\n", "\\n", "\\r"), '
', $message['message_'.$action.'_email_'.$field])); }
if ($sent == 'yes') { wp_mail($receiver, $subject, $body, 'From: '.$sender); } }

if ((function_exists('referrer_data')) && ($message['referrer'] != '') && (!strstr($message['referrer'], '@'))) {
if (affiliation_data('message_notification_email_disabled') != 'yes') {
$_GET['referrer'] = $message['referrer'];
if (referrer_data('status') == 'active') {
$sent = referrer_data('message_notification_email_sent');
if (($sent == 'yes') || (($sent == 'if commission') && ($message['commission_amount'] > 0))) {
foreach (array('sender', 'receiver', 'subject', 'body') as $field) {
$$field = str_replace(array('&#91;', '&#93;'), array('[', ']'), affiliation_data('message_notification_email_'.$field)); }
wp_mail($receiver, $subject, $body, 'From: '.$sender); } } } }

if (($message['sender_subscribed_to_autoresponder'] == 'yes') && ($message['email_address'] != '')) {
if (!function_exists('subscribe_to_autoresponder')) { include_once dirname(__FILE__).'/libraries/autoresponders-functions.php'; }
subscribe_to_autoresponder($message['sender_autoresponder'], $message['sender_autoresponder_list'], $message); }

if ($message['message_custom_instructions_executed'] == 'yes') {
eval(format_instructions($message['message_custom_instructions'])); } } }