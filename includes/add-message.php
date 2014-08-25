<?php if ((isset($message)) && (is_array($message))) {
global $wpdb;
foreach (array('admin-pages.php', 'tables.php') as $file) { include CONTACT_MANAGER_PATH.$file; }
foreach ($tables['messages'] as $key => $value) { if (!isset($message[$key])) { $message[$key] = ''; } }
$GLOBALS['contact_form_id'] = (int) $message['form_id'];
if (function_exists('add_affiliate')) {
$GLOBALS['affiliate_id'] = 0;
if ((!is_admin()) && (affiliation_session())) { $GLOBALS['affiliate_id'] = (int) affiliate_data('id'); }
if (($GLOBALS['affiliate_id'] == 0) && ($message['email_address'] != '')) {
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE (email_address = '".$message['email_address']."' OR paypal_email_address = '".$message['email_address']."')", OBJECT);
if ($result) { $GLOBALS['affiliate_data'] = (array) $result; $GLOBALS['affiliate_id'] = $result->id; } } }
if (function_exists('add_client')) {
$GLOBALS['client_id'] = 0;
if ((!is_admin()) && (commerce_session())) { $GLOBALS['client_id'] = (int) client_data('id'); }
if (($GLOBALS['client_id'] == 0) && ($message['email_address'] != '')) {
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."commerce_manager_clients WHERE email_address = '".$message['email_address']."'", OBJECT);
if ($result) { $GLOBALS['client_data'] = (array) $result; $GLOBALS['client_id'] = $result->id; } } }
if (function_exists('add_member')) {
$GLOBALS['member_id'] = 0;
if ((!is_admin()) && (membership_session())) { $GLOBALS['member_id'] = (int) member_data('id'); }
if (($GLOBALS['member_id'] == 0) && ($message['email_address'] != '')) {
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."membership_manager_members WHERE email_address = '".$message['email_address']."'", OBJECT);
if ($result) { $GLOBALS['member_data'] = (array) $result; $GLOBALS['member_id'] = $result->id; } } }
$GLOBALS['user_id'] = (int) (isset($GLOBALS['user_id']) ? $GLOBALS['user_id'] : 0);
if (($GLOBALS['user_id'] == 0) && ($message['email_address'] != '')) {
$result = $wpdb->get_row("SELECT * FROM ".$wpdb->users." WHERE user_email = '".$message['email_address']."'", OBJECT);
if ($result) { $GLOBALS['user_data'] = (array) $result; $GLOBALS['user_id'] = $result->ID; } }
$original_custom_fields = $message['custom_fields'];
if (is_serialized($message['custom_fields'])) {
$custom_fields = (array) unserialize(stripslashes($message['custom_fields']));
$back_office_options = (array) get_option('contact_manager_back_office');
$message_page_custom_fields = (array) $back_office_options['message_page_custom_fields'];
$message_custom_fields = array();
foreach ($message_page_custom_fields as $key => $value) {
if ((isset($custom_fields[$key])) && ($custom_fields[$key] != '')) { $message_custom_fields[$key] = $custom_fields[$key]; } }
$message['custom_fields'] = serialize($message_custom_fields); }
if ((is_admin()) || (contact_form_data('messages_registration_enabled') == 'yes')) {
$sql = contact_sql_array($tables['messages'], $message);
$keys_list = ''; $values_list = '';
foreach ($tables['messages'] as $key => $value) { if ($key != 'id') { $keys_list .= $key.","; $values_list .= $sql[$key].","; } }
$result = $wpdb->query("INSERT INTO ".$wpdb->prefix."contact_manager_messages (".substr($keys_list, 0, -1).") VALUES(".substr($values_list, 0, -1).")");
$result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_messages ORDER BY id DESC LIMIT 1", OBJECT);
$GLOBALS['message_id'] = $result->id;
$message['id'] = $result->id;
if (!is_admin()) {
$maximum_messages_quantity = contact_form_data('maximum_messages_quantity');
if (is_numeric($maximum_messages_quantity)) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$message['form_id'], OBJECT);
$messages_quantity = (int) (isset($row->total) ? $row->total : 0);
$n = $messages_quantity - $maximum_messages_quantity;
if ($n > 0) { $results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$message['form_id']." ORDER BY date ASC LIMIT $n"); } }
$maximum_messages_quantity = contact_data('maximum_messages_quantity');
if (is_numeric($maximum_messages_quantity)) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages", OBJECT);
$messages_quantity = (int) (isset($row->total) ? $row->total : 0);
$n = $messages_quantity - $maximum_messages_quantity;
if ($n > 0) { $results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_messages ORDER BY date ASC LIMIT $n"); } } } }
$message['custom_fields'] = $original_custom_fields;
$GLOBALS['message_data'] = $message;
if (($message['referrer'] != '') && (function_exists('affiliate_data'))) {
$GLOBALS['affiliate_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$message['referrer']."'", OBJECT);
$GLOBALS['referrer_data'] = $GLOBALS['affiliate_data']; }
foreach ($add_message_fields as $field) {
$original[$field] = (isset($message[$field]) ? $message[$field] : '');
if (is_admin()) { $message[$field] = (isset($message[$field]) ? stripslashes(do_shortcode($message[$field])) : ''); }
else { $message[$field] = contact_form_data($field); } }

if ($message['form_id'] > 0) {
$displays_count = contact_form_data('displays_count');
$messages_count = contact_form_data('messages_count') + 1;
if ($displays_count < $messages_count) { $displays_count = $messages_count; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET
	displays_count = ".$displays_count.",
	messages_count = ".$messages_count." WHERE id = ".$message['form_id']);
foreach (array('', $GLOBALS['contact_form_id']) as $string) {
$GLOBALS['contact_form'.$string.'_data'] = (array) (isset($GLOBALS['contact_form'.$string.'_data']) ? $GLOBALS['contact_form'.$string.'_data'] : array());
foreach (array('displays_count', 'messages_count') as $field) { $GLOBALS['contact_form'.$string.'_data'][$field] = $$field; } } }

if ((function_exists('add_affiliate')) && ($message['sender_subscribed_to_affiliate_program'] == 'yes')) {
if ($GLOBALS['affiliate_id'] > 0) {
if ($message['sender_affiliate_category_id'] > 0) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."affiliation_manager_affiliates SET category_id = ".$message['sender_affiliate_category_id']." WHERE id = ".$GLOBALS['affiliate_id']); }
if (function_exists('update_affiliate_custom_fields')) { update_affiliate_custom_fields($GLOBALS['affiliate_id'], $message['custom_fields']); } }
elseif ($message['email_address'] != '') {
$affiliate = $message;
foreach (array('login', 'password') as $field) {
if ((isset($affiliate[$field])) && ($affiliate[$field] != '')) {
if ($field == 'login') { $affiliate[$field] = format_nice_name($affiliate[$field]);
if (($affiliate[$field] == '') || (is_numeric($affiliate[$field]))) { $affiliate[$field] .= '-'; } }
$length = strlen($affiliate[$field]);
foreach (array('maximum', 'minimum') as $string) { $$string = affiliation_data($string.'_'.$field.'_length'); }
if ($length < $minimum) { $affiliate[$field] .= substr(md5(mt_rand()), 0, $minimum - $length); }
elseif ($length > $maximum) { $affiliate[$field] = substr($affiliate[$field], 0, $maximum); } } }
if ((!isset($affiliate['login'])) || ($affiliate['login'] == '')) { $affiliate['login'] = $affiliate['email_address']; }
$array = explode('@', $affiliate['login']);
$affiliate['login'] = format_nice_name($array[0]);
if (($affiliate['login'] == '') || (is_numeric($affiliate['login']))) { $affiliate['login'] .= '-'; }
$login = $affiliate['login']; $result = true; $i = 1; while ($result) {
$result = $wpdb->get_results("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$affiliate['login']."'", OBJECT);
if ($result) { $affiliate['login'] = $login.$i; $i = $i + 1; } }
if ((!isset($affiliate['password'])) || ($affiliate['password'] == '')) { $affiliate['password'] = substr(md5(mt_rand()), 0, affiliation_data('automatically_generated_password_length')); }
$affiliate['paypal_email_address'] = $affiliate['email_address'];
foreach (array('category_id', 'status') as $field) {
$affiliate[$field] = $message['sender_affiliate_'.$field];
if ($affiliate[$field] == '') { $affiliate[$field] = affiliation_data('affiliates_initial_'.$field); } }
foreach (array('date', 'date_utc') as $field) {
if ($affiliate['status'] == 'active') { $affiliate['activation_'.$field] = $affiliate[$field]; }
else { $affiliate['activation_'.$field] = ''; } }
foreach (array('confirmation', 'notification') as $action) {
$affiliate['registration_'.$action.'_email_sent'] = $message['affiliation_registration_'.$action.'_email_sent'];
if ((!is_admin()) && ($affiliate['registration_'.$action.'_email_sent'] == '')) {
$affiliate['registration_'.$action.'_email_sent'] = affiliation_data('registration_'.$action.'_email_sent'); } }
$affiliate['registration_without_form'] = 'yes';
add_affiliate($affiliate); } }

if ((function_exists('add_client')) && ($message['sender_subscribed_as_a_client'] == 'yes')) {
if ($GLOBALS['client_id'] > 0) {
if ($message['sender_client_category_id'] > 0) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."commerce_manager_clients SET category_id = ".$message['sender_client_category_id']." WHERE id = ".$GLOBALS['client_id']); }
if (function_exists('update_client_custom_fields')) { update_client_custom_fields($GLOBALS['client_id'], $message['custom_fields']); } }
elseif ($message['email_address'] != '') {
if (isset($affiliate)) { $client = $affiliate; }
else {
$client = $message;
foreach (array('login', 'password') as $field) {
if ((isset($client[$field])) && ($client[$field] != '')) {
if ($field == 'login') { $client[$field] = format_email_address($client[$field]);
if (($client[$field] == '') || (is_numeric($client[$field]))) { $client[$field] .= '-'; } }
$length = strlen($client[$field]);
foreach (array('maximum', 'minimum') as $string) { $$string = commerce_data($string.'_'.$field.'_length'); }
if ($length < $minimum) { $client[$field] .= substr(md5(mt_rand()), 0, $minimum - $length); }
elseif ($length > $maximum) { $client[$field] = substr($client[$field], 0, $maximum); } } } }
if ((!isset($client['login'])) || ($client['login'] == '')) { $client['login'] = $client['email_address']; }
$login = $client['login']; $result = true; $i = 1; while ($result) {
$result = $wpdb->get_results("SELECT login FROM ".$wpdb->prefix."commerce_manager_clients WHERE login = '".$client['login']."'", OBJECT);
if ($result) { $client['login'] = $login.$i; $i = $i + 1; } }
if ((!isset($client['password'])) || ($client['password'] == '')) { $client['password'] = substr(md5(mt_rand()), 0, commerce_data('automatically_generated_password_length')); }
foreach (array('category_id', 'status') as $field) {
$client[$field] = $message['sender_client_'.$field];
if ($client[$field] == '') { $client[$field] = commerce_data('clients_initial_'.$field); } }
foreach (array('date', 'date_utc') as $field) {
if ($client['status'] == 'active') { $client['activation_'.$field] = $client[$field]; }
else { $client['activation_'.$field] = ''; } }
foreach (array('confirmation', 'notification') as $action) {
$client['registration_'.$action.'_email_sent'] = $message['commerce_registration_'.$action.'_email_sent'];
if ((!is_admin()) && ($client['registration_'.$action.'_email_sent'] == '')) {
$client['registration_'.$action.'_email_sent'] = commerce_data('registration_'.$action.'_email_sent'); } }
$client['registration_without_form'] = 'yes';
add_client($client); } }

if ((function_exists('add_member')) && ($message['sender_subscribed_to_members_areas'] == 'yes')) {
if ($GLOBALS['member_id'] > 0) {
if ($message['sender_member_category_id'] > 0) {
$results = $wpdb->query("UPDATE ".$wpdb->prefix."membership_manager_members SET category_id = ".$message['sender_member_category_id']." WHERE id = ".$GLOBALS['member_id']); }
if (function_exists('update_member_custom_fields')) { update_member_custom_fields($GLOBALS['member_id'], $message['custom_fields']); }
update_member_members_areas_modifications($GLOBALS['member_id'], $message['sender_members_areas_modifications'], 'add',
update_member_members_areas($GLOBALS['member_id'], $message['sender_members_areas'], 'add')); }
elseif ($message['email_address'] != '') {
if (isset($affiliate)) { $member = $affiliate; }
elseif (isset($client)) { $member = $client; }
else {
$member = $message;
foreach (array('login', 'password') as $field) {
if ((isset($member[$field])) && ($member[$field] != '')) {
if ($field == 'login') { $member[$field] = format_email_address($member[$field]);
if (($member[$field] == '') || (is_numeric($member[$field]))) { $member[$field] .= '-'; } }
$length = strlen($member[$field]);
foreach (array('maximum', 'minimum') as $string) { $$string = membership_data($string.'_'.$field.'_length'); }
if ($length < $minimum) { $member[$field] .= substr(md5(mt_rand()), 0, $minimum - $length); }
elseif ($length > $maximum) { $member[$field] = substr($member[$field], 0, $maximum); } } } }
$member['members_areas'] = $message['sender_members_areas'];
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $member['members_areas'], 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); }
$member['members_areas_modifications'] = $message['sender_members_areas_modifications'];
if ((!isset($member['login'])) || ($member['login'] == '')) { $member['login'] = $member['email_address']; }
$login = $member['login']; $result = true; $i = 1; while ($result) {
$result = $wpdb->get_results("SELECT login FROM ".$wpdb->prefix."membership_manager_members WHERE login = '".$member['login']."'", OBJECT);
if ($result) { $member['login'] = $login.$i; $i = $i + 1; } }
if ((!isset($member['password'])) || ($member['password'] == '')) { $member['password'] = substr(md5(mt_rand()), 0, membership_data('automatically_generated_password_length')); }
foreach (array('category_id', 'status') as $field) {
$member[$field] = $message['sender_member_'.$field];
if ($member[$field] == '') { $member[$field] = member_area_data('members_initial_'.$field); } }
foreach (array('date', 'date_utc') as $field) {
if ($member['status'] == 'active') { $member['activation_'.$field] = $member[$field]; }
else { $member['activation_'.$field] = ''; } }
foreach (array('confirmation', 'notification') as $action) {
$member['registration_'.$action.'_email_sent'] = $message['membership_registration_'.$action.'_email_sent'];
if ((!is_admin()) && ($member['registration_'.$action.'_email_sent'] == '')) {
$member['registration_'.$action.'_email_sent'] = member_area_data('registration_'.$action.'_email_sent'); } }
$member['registration_without_form'] = 'yes';
add_member($member); } }

if ((!defined('CONTACT_MANAGER_DEMO')) || (CONTACT_MANAGER_DEMO == false)) {
if (($GLOBALS['user_id'] == 0) && ($message['sender_subscribed_as_a_user'] == 'yes') && ($message['email_address'] != '')) {
if (isset($affiliate)) { $user = $affiliate; }
elseif (isset($client)) { $user = $client; }
elseif (isset($member)) { $user = $member; }
else { $user = $message; }
$user['role'] = $message['sender_user_role'];
if ((!isset($user['login'])) || ($user['login'] == '')) { $user['login'] = $user['email_address']; }
$login = $user['login']; $result = true; $i = 1; while ($result) {
$result = $wpdb->get_results("SELECT user_login FROM ".$wpdb->users." WHERE user_login = '".$user['login']."'", OBJECT);
if ($result) { $user['login'] = $login.$i; $i = $i + 1; } }
if ((!isset($user['password'])) || ($user['password'] == '')) { $user['password'] = substr(md5(mt_rand()), 0, 8); }
if (isset($user['ID'])) { unset($user['ID']); }
$user['user_login'] = $user['login'];
$user['user_pass'] = $user['password'];
$_POST['pass1'] = $user['password'];
$user['user_email'] = $user['email_address'];
$user['user_url'] = $user['website_url'];
$user['user_registered'] = $user['date_utc'];
$user['display_name'] = $user['first_name'];
$user['ID'] = wp_insert_user($user);
$GLOBALS['user_id'] = $user['ID'];
$GLOBALS['user_data'] = $user; }

foreach ($add_message_fields as $field) {
if (is_admin()) { $message[$field] = stripslashes(do_shortcode($original[$field])); }
else { $message[$field] = contact_form_data($field); } }

if (!is_admin()) {
$prefix = $GLOBALS['contact_form_prefix'];
if (in_array('message_confirmation_email_sent', $GLOBALS[$prefix.'fields'])) {
$message['message_confirmation_email_sent'] = (((isset($_POST['message_confirmation_email_sent'])) && ($_POST['message_confirmation_email_sent'] != 'no')) ? 'yes' : 'no'); }
if (in_array('subscribed_to_autoresponder', $GLOBALS[$prefix.'fields'])) {
$message['sender_subscribed_to_autoresponder'] = (((isset($_POST['subscribed_to_autoresponder'])) && ($_POST['subscribed_to_autoresponder'] != 'no')) ? 'yes' : 'no'); }
if ((in_array('autoresponder_list', $GLOBALS[$prefix.'fields']))
 && (isset($_POST['autoresponder_list'])) && ($_POST['autoresponder_list'] != '')) {
$message['sender_autoresponder_list'] = $_POST['autoresponder_list']; } }

$upload_dir = wp_upload_dir();
$folder = $upload_dir['basedir'].'/temp';
if (!is_dir($folder)) { mkdir($folder, 0777); }
$files = array();
foreach ($_FILES as $key => $value) {
if ($value['error'] == 0) {
$extension = strtolower(substr(strrchr($value['name'], '.'), 1));
if (!in_array($extension, array('php', 'php3', 'phtml'))) {
$file = $folder.'/'.basename($value['name']);
move_uploaded_file($value['tmp_name'], $file);
$files[] = $file; } } }

foreach (array('confirmation', 'notification') as $action) {
foreach (array('sent', 'sender', 'receiver', 'subject', 'body') as $field) {
$$field = str_replace(array("\\t", '\\', '&#91;', '&#93;'), array('	', '', '[', ']'), str_replace(array("\\r\\n", "\\n", "\\r"), '
', $message['message_'.$action.'_email_'.$field])); }
if ($action == 'confirmation') { $attachments = array(); } else { $attachments = $files; }
if ($sent == 'yes') { wp_mail($receiver, $subject, $body, 'From: '.$sender.(((strstr($body, '</')) || (strstr($body, '/>'))) ? "\r\nContent-type: text/html" : ""), $attachments); } }

if ((function_exists('referrer_data')) && ($message['referrer'] != '') && (!strstr($message['referrer'], '@'))) {
if (affiliation_data('message_notification_email_disabled') != 'yes') {
$GLOBALS['referrer'] = $message['referrer'];
if (referrer_data('status') == 'active') {
$sent = referrer_data('message_notification_email_sent');
if (($sent == 'yes') || (($sent == 'if commission') && ($message['commission_amount'] > 0))) {
foreach (array('sender', 'receiver', 'subject', 'body') as $field) {
$$field = str_replace(array('&#91;', '&#93;'), array('[', ']'), affiliation_data('message_notification_email_'.$field)); }
wp_mail($receiver, $subject, $body, 'From: '.$sender.(((strstr($body, '</')) || (strstr($body, '/>'))) ? "\r\nContent-type: text/html" : "")); } } } }

if (($message['sender_subscribed_to_autoresponder'] == 'yes') && ($message['email_address'] != '')) {
if (!function_exists('subscribe_to_autoresponder')) { include_once CONTACT_MANAGER_PATH.'libraries/autoresponders-functions.php'; }
subscribe_to_autoresponder($message['sender_autoresponder'], $message['sender_autoresponder_list'], $message); }

if ($message['message_custom_instructions_executed'] == 'yes') {
eval(format_instructions($message['message_custom_instructions'])); }

foreach ($files as $file) { chmod($file, 0777); unlink($file); } } }