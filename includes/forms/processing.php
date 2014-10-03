<?php $GLOBALS[$prefix.'processed'] = 'yes';
if (!isset($GLOBALS['form_error'])) { $GLOBALS['form_error'] = ''; }
if ((is_numeric($maximum_messages_quantity_per_sender)) && (isset($_POST[$prefix.'email_address'])) && ($_POST[$prefix.'email_address'] != '')) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE email_address = '".$_POST[$prefix.'email_address']."' AND form_id = ".$id, OBJECT);
$messages_number = (int) (isset($row->total) ? $row->total : 0);
if ($messages_number >= $maximum_messages_quantity_per_sender) {
$GLOBALS[$prefix.'maximum_messages_quantity_reached_error'] = contact_form_data('maximum_messages_quantity_reached_message'); $GLOBALS['form_error'] = 'yes'; } }
foreach ($GLOBALS[$prefix.'required_fields'] as $field) {
if ((!isset($GLOBALS[$prefix.'unfilled_fields_error'])) || ($GLOBALS[$prefix.'unfilled_fields_error'] == '')) {
if ((isset($GLOBALS[$prefix.$field.'_error'])) && (strstr($GLOBALS[$prefix.$field.'_error'], 'unfilled'))) {
$GLOBALS[$prefix.'unfilled_fields_error'] = contact_form_data('unfilled_fields_message'); $GLOBALS['form_error'] = 'yes'; } } }
foreach ($GLOBALS[$prefix.'fields'] as $field) {
if ((!isset($GLOBALS[$prefix.'invalid_fields_error'])) || ($GLOBALS[$prefix.'invalid_fields_error'] == '')) {
if ((isset($GLOBALS[$prefix.$field.'_error'])) && (strstr($GLOBALS[$prefix.$field.'_error'], 'invalid'))) {
$GLOBALS[$prefix.'invalid_fields_error'] = contact_form_data('invalid_fields_message'); $GLOBALS['form_error'] = 'yes'; } } }
$invalid_captcha = '';
if (isset($GLOBALS[$prefix.'recaptcha_js'])) {
$resp = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
if (!$resp->is_valid) { $invalid_captcha = 'yes'; } }
elseif (in_array('captcha', $GLOBALS[$prefix.'fields'])) {
if (hash('sha256', $_POST[$prefix.'captcha']) != $_POST[$prefix.'valid_captcha']) { $invalid_captcha = 'yes'; } }
if ($invalid_captcha == 'yes') { $GLOBALS[$prefix.'invalid_captcha_error'] = contact_form_data('invalid_captcha_message'); $GLOBALS['form_error'] = 'yes'; }
if ($GLOBALS['form_error'] == '') {
foreach ($_POST as $key => $value) { if (strstr($key, $prefix)) {
$_POST[str_replace($prefix, $canonical_prefix, $key)] = $value;
$_POST[str_replace($prefix, '', $key)] = $value; } }
include CONTACT_MANAGER_PATH.'tables.php';
foreach ($tables['messages'] as $key => $value) {
if ((isset($_POST[$key])) && ($key != 'referring_url') && (!in_array($key, $GLOBALS[$prefix.'fields']))) { unset($_POST[$key]); } }
$custom_fields = array(); foreach ($_POST as $key => $value) {
if ((substr($key, 0, 13) == 'custom_field_') && (in_array($key, $GLOBALS[$prefix.'fields'])) && ($value != '')) { $custom_fields[substr($key, 13)] = str_replace('\\', '', quotes_entities_decode($value)); } }
$_POST['custom_fields'] = ($custom_fields == array() ? '' : serialize($custom_fields));
if ((!defined('CONTACT_MANAGER_DEMO')) || (CONTACT_MANAGER_DEMO == false)) {
if (contact_data('form_submission_custom_instructions_executed') == 'yes') {
eval(format_instructions(contact_data('form_submission_custom_instructions'))); } }
foreach (array('email_address', 'content', 'subject') as $field) {
if (!isset($_POST[$field])) { $_POST[$field] = ''; } }
$_POST['receiver'] = contact_form_data('message_notification_email_receiver');
$_POST['ip_address'] = $_SERVER['REMOTE_ADDR'];
$_POST['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
$_POST['form_id'] = $id;
date_default_timezone_set('UTC');
$current_time = time();
$_POST['date'] = date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET);
$_POST['date_utc'] = date('Y-m-d H:i:s', $current_time);
if (function_exists('award_message_commission')) { award_message_commission();
if (($_POST['commission_amount'] > 0) && (affiliation_data('overpayment_deducted') == 'yes')) {
$affiliate = $wpdb->get_row("SELECT id, overpayment_amount FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer']."'", OBJECT);
if ($affiliate->overpayment_amount > 0) {
if ($affiliate->overpayment_amount > $_POST['commission_amount']) {
$overpayment_amount = $affiliate->overpayment_amount - $_POST['commission_amount'];
$_POST['commission_amount'] = 0;
$_POST['commission_status'] = ''; }
else {
$overpayment_amount = 0;
$_POST['commission_amount'] = $_POST['commission_amount'] - $affiliate->overpayment_amount; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."affiliation_manager_affiliates SET overpayment_amount = ".$overpayment_amount." WHERE login = '".$_POST['referrer']."'");
foreach (array('affiliate', 'referrer', 'affiliate'.$affiliate->id) as $string) {
$GLOBALS[$string.'_data'] = (array) (isset($GLOBALS[$string.'_data']) ? $GLOBALS[$string.'_data'] : array());
if ((isset($GLOBALS[$string.'_data']['id'])) && ($GLOBALS[$string.'_data']['id'] == $affiliate->id)) { $GLOBALS[$string.'_data']['overpayment_amount'] = $overpayment_amount; } } } } }
if (function_exists('award_message_commission2')) { award_message_commission2();
if (($_POST['commission2_amount'] > 0) && (affiliation_data('overpayment_deducted') == 'yes')) {
$affiliate = $wpdb->get_row("SELECT id, overpayment_amount FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer2']."'", OBJECT);
if ($affiliate->overpayment_amount > 0) {
if ($affiliate->overpayment_amount > $_POST['commission2_amount']) {
$overpayment_amount = $affiliate->overpayment_amount - $_POST['commission2_amount'];
$_POST['commission2_amount'] = 0;
$_POST['commission2_status'] = ''; }
else {
$overpayment_amount = 0;
$_POST['commission2_amount'] = $_POST['commission2_amount'] - $affiliate->overpayment_amount; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."affiliation_manager_affiliates SET overpayment_amount = ".$overpayment_amount." WHERE login = '".$_POST['referrer2']."'");
$GLOBALS['affiliate'.$affiliate->id.'_data'] = (array) (isset($GLOBALS['affiliate'.$affiliate->id.'_data']) ? $GLOBALS['affiliate'.$affiliate->id.'_data'] : array());
$GLOBALS['affiliate'.$affiliate->id.'_data']['overpayment_amount'] = $overpayment_amount; } } }
foreach (array('message_id', 'message_data') as $key) {
if (isset($GLOBALS[$key])) { $original[$key] = $GLOBALS[$key]; unset($GLOBALS[$key]); } }
$GLOBALS['message_data'] = $_POST;
foreach (array('subject', 'content') as $field) {
if ((!isset($_POST[$field])) || ($_POST[$field] == '')) { $_POST[$field] = contact_form_data('message_notification_email_'.($field == 'content' ? 'body' : $field)); } }
foreach (array('message_id', 'message_data') as $key) {
if (isset($original[$key])) { $GLOBALS[$key] = $original[$key]; } }
$result = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix."contact_manager_messages WHERE email_address = '".$_POST['email_address']."' AND subject = '".str_replace("'", "''", $_POST['subject'])."' AND content = '".str_replace("'", "''", $_POST['content'])."'", OBJECT);
if (!$result) { $GLOBALS['user_id'] = get_current_user_id(); add_message($_POST); }

if (($redirection != '') && (substr($redirection, 0, 1) != '#')) {
$redirection = format_url($redirection);
if (!headers_sent()) { header('Location: '.$redirection); exit(); }
else { $content .= '<script type="text/javascript">window.location = \''.$redirection.'\';</script>'; } } }