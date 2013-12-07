<?php global $wpdb; $error = '';
$back_office_options = (array) get_option('contact_manager_back_office');
extract(contact_manager_pages_links_markups($back_office_options));
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$current_time = time();

if ((isset($_GET['id'])) && (isset($_GET['action'])) && ($_GET['action'] == 'delete')) {
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!contact_manager_user_can($back_office_options, 'manage')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
$message_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$_GET['id'], OBJECT);
$GLOBALS['message_data'] = (array) $message_data;
$GLOBALS['referrer'] = $GLOBALS['message_data']['referrer'];
$GLOBALS['contact_form_id'] = $GLOBALS['message_data']['form_id'];
$results = $wpdb->query("DELETE FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$_GET['id']);
$result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."contact_manager_messages ORDER BY id DESC LIMIT 1", OBJECT);
if (!$result) { $results = $wpdb->query("ALTER TABLE ".$wpdb->prefix."contact_manager_messages AUTO_INCREMENT = 1"); }
elseif ($result->id < $_GET['id']) {
$results = $wpdb->query("ALTER TABLE ".$wpdb->prefix."contact_manager_messages AUTO_INCREMENT = ".($result->id + 1)); }
if ($message_data->form_id > 0) {
$contact_form_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = ".$message_data->form_id, OBJECT);
$messages_count = $contact_form_data->messages_count - 1;
if ($messages_count < 0) { $messages_count = 0; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET messages_count = ".$messages_count." WHERE id = ".$contact_form_data->id); }
if ((!defined('CONTACT_MANAGER_DEMO')) || (CONTACT_MANAGER_DEMO == false)) {
if (contact_data('message_removal_custom_instructions_executed') == 'yes') {
eval(format_instructions(contact_data('message_removal_custom_instructions'))); } } } } ?>
<div class="wrap">
<div id="poststuff">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if (isset($_POST['submit'])) {
echo '<div class="updated"><p><strong>'.__('Message deleted.', 'contact-manager').'</strong></p></div>
<script type="text/javascript">setTimeout(\'window.location = "admin.php?page=contact-manager-messages"\', 2000);</script>'; } ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<div class="clear"></div>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php if (!isset($_POST['submit'])) { ?>
<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<div class="alignleft actions">
<?php _e('Do you really want to permanently delete this message?', 'contact-manager'); ?> 
<input type="submit" class="button-secondary" name="submit" id="submit" value="<?php _e('Yes', 'contact-manager'); ?>" />
</div>
<div class="clear"></div>
</form><?php } ?>
</div>
</div><?php }

else {
include CONTACT_MANAGER_PATH.'/admin-pages.php'; include CONTACT_MANAGER_PATH.'/tables.php';
foreach ($tables['messages'] as $key => $value) { if (!isset($_POST[$key])) { $_POST[$key] = ''; } }
if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
if (!contact_manager_user_can($back_office_options, 'manage')) { $_POST = array(); $error = __('You don\'t have sufficient permissions.', 'contact-manager'); }
else {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes(html_entity_decode(str_replace('&nbsp;', ' ', $value))); } }
$back_office_options = update_contact_manager_back_office($back_office_options, 'message');

if ($_POST['receiver'] == '') { $_POST['receiver'] = contact_form_data('message_notification_email_receiver'); }
foreach (array('subject', 'content') as $field) { $_POST[$field] = str_replace(array('[', ']'), array('&#91;', '&#93;'), $_POST[$field]); }
$keywords = explode(',', $_POST['keywords']);
$keywords_list = '';
for ($i = 0; $i < count($keywords); $i++) { $keywords[$i] = strtolower(trim($keywords[$i])); }
sort($keywords);
foreach ($keywords as $keyword) { if ($keyword != '') { if ($keyword != '') { $keywords_list .= $keyword.', '; } } }
$_POST['keywords'] = substr($keywords_list, 0, -2);
$_POST['email_address'] = format_email_address($_POST['email_address']);
$_POST['form_id'] = (int) $_POST['form_id'];
if ($_POST['form_id'] < 1) { $_POST['form_id'] = 1; }
$GLOBALS['contact_form_id'] = $_POST['form_id'];
$GLOBALS['contact_form_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_forms WHERE id = ".$_POST['form_id'], OBJECT);
$custom_fields = (array) $back_office_options['message_page_custom_fields'];
$item_custom_fields = array();
foreach ($custom_fields as $key => $value) {
if ((isset($_POST['custom_field_'.$key])) && ($_POST['custom_field_'.$key] != '')) { $item_custom_fields[$key] = $_POST['custom_field_'.$key]; } }
if ($item_custom_fields != array()) { $_POST['custom_fields'] = serialize($item_custom_fields); }
if ($_POST['referrer'] != '') {
if (is_numeric($_POST['referrer'])) {
$_POST['referrer'] = preg_replace('/[^0-9]/', '', $_POST['referrer']);
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE id = ".$_POST['referrer'], OBJECT);
if ($result) { $_POST['referrer'] = $result->login; } }
if (strstr($_POST['referrer'], '@')) {
$_POST['referrer'] = format_email_address($_POST['referrer']);
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE (email_address = '".$_POST['referrer']."' OR paypal_email_address = '".$_POST['referrer']."')", OBJECT);
if ($result) { $_POST['referrer'] = $result->login; } }
else {
$_POST['referrer'] = format_nice_name($_POST['referrer']);
if (is_numeric($_POST['referrer'])) { $_POST['referrer'] = ''; } } }
if (($_POST['referrer'] == '') || (strstr($_POST['referrer'], '@'))) {
$_POST['commission_amount'] = 0;
$_POST['commission_status'] = '';
$_POST['commission_payment_date'] = ''; }
else {
$GLOBALS['referrer'] = $_POST['referrer'];
$_POST['commission_amount'] = str_replace(array('?', ',', ';'), '.', $_POST['commission_amount']);
$_POST['commission_amount'] = round($_POST['commission_amount'], 2); if ($_POST['commission_amount'] <= 0) { $_POST['commission_amount'] = 0; }
if ($_POST['commission_amount'] == 0) {
$_POST['commission_status'] = '';
$_POST['commission_payment_date'] = ''; }
elseif ($_POST['commission_status'] == '') { $_POST['commission_status'] = 'unpaid'; }
if ($_POST['commission_status'] == 'paid') {
if ($_POST['commission_payment_date'] == '') {
$_POST['commission_payment_date'] = date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET);
$_POST['commission_payment_date_utc'] = date('Y-m-d H:i:s', $current_time); }
else {
$d = preg_split('#[^0-9]#', $_POST['commission_payment_date'], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$time = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
$_POST['commission_payment_date'] = date('Y-m-d H:i:s', $time);
$_POST['commission_payment_date_utc'] = date('Y-m-d H:i:s', $time - 3600*UTC_OFFSET); } }
else { $_POST['commission_payment_date'] = ''; } }
if (($_POST['referrer2'] == '') && ($_POST['referrer'] != '')) {
$result = $wpdb->get_row("SELECT referrer FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer']."'", OBJECT);
if ($result) { $_POST['referrer2'] = $result->referrer; } }
else {
if (is_numeric($_POST['referrer2'])) {
$_POST['referrer2'] = preg_replace('/[^0-9]/', '', $_POST['referrer2']);
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE id = ".$_POST['referrer2'], OBJECT);
if ($result) { $_POST['referrer2'] = $result->login; } }
if (strstr($_POST['referrer2'], '@')) {
$_POST['referrer2'] = format_email_address($_POST['referrer2']);
$result = $wpdb->get_row("SELECT login FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE (email_address = '".$_POST['referrer2']."' OR paypal_email_address = '".$_POST['referrer2']."')", OBJECT);
if ($result) { $_POST['referrer2'] = $result->login; } }
else {
$_POST['referrer2'] = format_nice_name($_POST['referrer2']);
if (is_numeric($_POST['referrer2'])) { $_POST['referrer2'] = ''; } } }
if (($_POST['referrer2'] == '') || (strstr($_POST['referrer2'], '@'))) {
$_POST['commission2_amount'] = 0;
$_POST['commission2_status'] = '';
$_POST['commission2_payment_date'] = ''; }
else {
$_POST['commission2_amount'] = str_replace(array('?', ',', ';'), '.', $_POST['commission2_amount']);
$_POST['commission2_amount'] = round($_POST['commission2_amount'], 2); if ($_POST['commission2_amount'] <= 0) { $_POST['commission2_amount'] = 0; }
if ($_POST['commission2_amount'] == 0) {
$_POST['commission2_status'] = '';
$_POST['commission2_payment_date'] = ''; }
elseif ($_POST['commission2_status'] == '') { $_POST['commission2_status'] = 'unpaid'; }
if ($_POST['commission2_status'] == 'paid') {
if ($_POST['commission2_payment_date'] == '') {
$_POST['commission2_payment_date'] = date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET);
$_POST['commission2_payment_date_utc'] = date('Y-m-d H:i:s', $current_time); }
else {
$d = preg_split('#[^0-9]#', $_POST['commission2_payment_date'], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$time = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
$_POST['commission2_payment_date'] = date('Y-m-d H:i:s', $time);
$_POST['commission2_payment_date_utc'] = date('Y-m-d H:i:s', $time - 3600*UTC_OFFSET); } }
else { $_POST['commission2_payment_date'] = ''; } }
if ($_POST['date'] == '') {
$_POST['date'] = date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET);
$_POST['date_utc'] = date('Y-m-d H:i:s', $current_time); }
else {
$d = preg_split('#[^0-9]#', $_POST['date'], 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$time = mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0]);
$_POST['date'] = date('Y-m-d H:i:s', $time);
$_POST['date_utc'] = date('Y-m-d H:i:s', $time - 3600*UTC_OFFSET); }

if (!isset($_GET['id'])) {
if ($_POST['referring_url'] == '') { $_POST['referring_url'] = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : ''); }
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
if ($_POST['referrer'] != '') {
$GLOBALS['referrer_data'] = (array) $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer']."'", OBJECT);
$GLOBALS['affiliate_data'] = $GLOBALS['referrer_data']; }
foreach ($add_message_fields as $field) { $_POST[$field] = contact_form_data($field); }
foreach (array('message', 'sender') as $tag) { add_shortcode($tag, 'message_data'); }
foreach ($add_message_fields as $field) {
$_POST[$field] = str_replace(array('{message id', '{sender id'), array('[message id', '[sender id'),
do_shortcode(str_replace(array('[message id', '[sender id'), array('{message id', '{sender id'), $_POST[$field]))); }
foreach (array('receiver', 'subject') as $field) { if ($_POST[$field] != '') { $_POST['message_notification_email_'.$field] = $_POST[$field]; } } }
else {
$members_areas = array_unique(preg_split('#[^0-9]#', $_POST['sender_members_areas'], 0, PREG_SPLIT_NO_EMPTY));
sort($members_areas, SORT_NUMERIC);
$members_areas_list = '';
foreach ($members_areas as $member_area) { if ($member_area != '') { $members_areas_list .= $member_area.', '; } }
$_POST['sender_members_areas'] = substr($members_areas_list, 0, -2);
$_POST['sender_members_areas_modifications'] = contact_manager_format_members_areas_modifications($_POST['sender_members_areas_modifications']);
if ($error == '') {
$result = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix."contact_manager_messages WHERE email_address = '".$_POST['email_address']."' AND subject = '".str_replace("'", "''", $_POST['subject'])."' AND content = '".str_replace("'", "''", $_POST['content'])."' AND date = '".$_POST['date']."'", OBJECT);
if (!$result) { $updated = true; add_message($_POST); } } } }

if (isset($_GET['id'])) {
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
$messages_count = $contact_form_data->messages_count - 1;
if ($messages_count < 0) { $messages_count = 0; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET messages_count = ".$messages_count." WHERE id = ".$message_data->form_id); }

if ($_POST['form_id'] > 0) {
$displays_count = $GLOBALS['contact_form_data']['displays_count'];
$messages_count = $GLOBALS['contact_form_data']['messages_count'] + 1;
if ($displays_count < $messages_count) { $displays_count = $messages_count; }
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET
	displays_count = ".$displays_count.",
	messages_count = ".$messages_count." WHERE id = ".$_POST['form_id']); } } } } }

if (isset($_GET['id'])) {
$message_data = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix."contact_manager_messages WHERE id = ".$_GET['id'], OBJECT);
if ($message_data) {
$GLOBALS['message_data'] = (array) $message_data;
foreach ($message_data as $key => $value) { $_POST[$key] = $value; }
foreach (array('subject', 'content') as $field) { $_POST[$field] = str_replace(array('&#91;', '&#93;'), array('[', ']'), $_POST[$field]); } }
elseif (!headers_sent()) { header('Location: admin.php?page=contact-manager-messages'); exit(); }
else { echo '<script type="text/javascript">window.location = "admin.php?page=contact-manager-messages";</script>'; } }

foreach ($_POST as $key => $value) {
if (is_string($value)) {
$_POST[$key] = str_replace(array('&amp;amp;', '&amp;apos;', '&amp;quot;'), array('&amp;', '&apos;', '&quot;'), htmlspecialchars(stripslashes($value)));
if (($value == '0000-00-00 00:00:00') && ((substr($key, -4) == 'date') || (substr($key, -8) == 'date_utc'))) { $_POST[$key] = ''; } } }
$undisplayed_modules = (array) $back_office_options['message_page_undisplayed_modules'];
if (function_exists('commerce_data')) { $currency_code = commerce_data('currency_code'); }
else { $commerce_manager_options = array_merge((array) get_option('commerce_manager'), (array) get_option('commerce_manager_client_area'));
$currency_code = (isset($commerce_manager_options['currency_code']) ? do_shortcode($commerce_manager_options['currency_code']) : ''); } ?>

<div class="wrap">
<div id="poststuff">
<?php contact_manager_pages_top($back_office_options); ?>
<?php if ((isset($updated)) && ($updated)) {
echo '<div class="updated"><p><strong>'.(isset($_GET['id']) ? __('Message updated.', 'contact-manager') : __('Message saved.', 'contact-manager')).'</strong></p></div>
'.(isset($_GET['id']) ? '' : '<script type="text/javascript">setTimeout(\'window.location = "admin.php?page=contact-manager-messages"\', 2000);</script>'); } ?>
<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<div class="clear"></div>
<?php if ($error != '') { echo '<p style="color: #c00000;">'.$error.'</p>'; } ?>
<?php contact_manager_pages_summary($back_office_options); ?>

<div class="postbox" id="general-informations-module"<?php if (in_array('general-informations', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="general-informations"><strong><?php echo $modules['message']['general-informations']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<?php if (isset($_GET['id'])) { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="id">'.__('ID', 'contact-manager').'</label></strong></th>
<td><input type="text" name="id" id="id" size="10" value="'.$_GET['id'].'" disabled="disabled" /> <span class="description">'.__('The ID can not be changed.', 'contact-manager').'</span><br />
<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-message&amp;id='.$_GET['id'].'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a></td></tr>'; } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="form_id"><?php _e('Form ID', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="form_id" id="form_id" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/30)))+'em';" onblur="this.style.height = '1.75em';" cols="25"><?php echo $_POST['form_id']; ?></textarea>
<?php if (!isset($_GET['id'])) { echo '<span class="description" style="vertical-align: 25%;">'.__('Leave this field blank for 1.', 'contact-manager').'</span>'; }
else { $description = ($_POST['form_id'] == 0 ? __('No form', 'contact-manager') : htmlspecialchars(contact_excerpt(contact_form_data(array(0 => 'name', 'id' => $_POST['form_id'])), 50)));
if ($description != '') { echo '<span class="description" style="vertical-align: 25%;">('.$description.')</span>'; } } ?>
<?php if ($_POST['form_id'] > 0) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$_POST['form_id'], OBJECT);
$messages_number = (int) (isset($row->total) ? $row->total : 0);
echo '<br /><a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-form&amp;id='.$_POST['form_id'].'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-form&amp;id='.$_POST['form_id'].'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-statistics&amp;form_id='.$_POST['form_id'].'">'.__('Statistics', 'contact-manager').'</a>'
.($messages_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=contact-manager-messages&amp;form_id='.$_POST['form_id'].'&amp;start_date=0">'.__('Messages', 'contact-manager').' <span style="color: #808080;">('.$messages_number.')</span></a>'); } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="receiver" id="receiver" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['receiver']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="subject" id="subject" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="content"><?php _e('Content', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="content" id="content" rows="10" cols="75"><?php echo $_POST['content']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="keywords"><?php _e('Keywords', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="keywords" id="keywords" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['keywords']; ?></textarea><br />
<span class="description"><?php _e('Separate the keywords with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="date"><?php _e('Date', 'contact-manager'); ?></label></strong></th>
<td><input class="date-pick" style="margin-right: 0.5em;" type="text" name="date" id="date" size="20" value="<?php echo ($_POST['date'] != '' ? $_POST['date'] : date('Y-m-d H:i:s', $current_time + 3600*UTC_OFFSET)); ?>" /></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="sender-module"<?php if (in_array('sender', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="sender"><strong><?php echo $modules['message']['sender']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="first_name"><?php _e('First name', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="first_name" id="first_name" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['first_name']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="last_name"><?php _e('Last name', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="last_name" id="last_name" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['last_name']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="email_address"><?php _e('Email address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="email_address" id="email_address" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['email_address']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="website_name"><?php _e('Website name', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="website_name" id="website_name" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['website_name']; ?></textarea> 
<?php $url = htmlspecialchars(message_data(array(0 => 'website_url', 'part' => 1, 'id' => (isset($_GET['id']) ? $_GET['id'] : 0)))); if ($url != '') { ?><a style="vertical-align: 25%;" <?php echo $urls_fields_links_markup; ?> href="<?php echo $url; ?>"><?php _e('Link', 'contact-manager'); ?></a><?php } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="website_url"><?php _e('Website URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="website_url" id="website_url" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['website_url']; ?></textarea> 
<?php $url = htmlspecialchars(message_data(array(0 => 'website_url', 'part' => 1, 'id' => (isset($_GET['id']) ? $_GET['id'] : 0)))); if ($url != '') { ?><a style="vertical-align: 25%;" <?php echo $urls_fields_links_markup; ?> href="<?php echo $url; ?>"><?php _e('Link', 'contact-manager'); ?></a><?php } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="address"><?php _e('Address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="address" id="address" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['address']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="postcode"><?php _e('Postcode', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="postcode" id="postcode" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['postcode']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="town"><?php _e('Town', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="town" id="town" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['town']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="country"><?php _e('Country', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="country" id="country" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['country']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="phone_number"><?php _e('Phone number', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="phone_number" id="phone_number" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['phone_number']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="ip_address"><?php _e('IP address', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="ip_address" id="ip_address" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['ip_address']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="user_agent"><?php _e('User agent', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="user_agent" id="user_agent" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['user_agent']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="referring_url"><?php _e('Referring URL', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="referring_url" id="referring_url" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['referring_url']; ?></textarea> 
<?php $url = htmlspecialchars(message_data(array(0 => 'referring_url', 'part' => 1, 'id' => (isset($_GET['id']) ? $_GET['id'] : 0)))); if ($url != '') { ?><a style="vertical-align: 25%;" <?php echo $urls_fields_links_markup; ?> href="<?php echo $url; ?>"><?php _e('Link', 'contact-manager'); ?></a><?php } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="custom-fields-module"<?php if (in_array('custom-fields', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="custom-fields"><strong><?php echo $modules['message']['custom-fields']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager-back-office#message-page-custom-fields"><?php _e('Click here to add a new custom field.', 'contact-manager'); ?></a>
 <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-fields"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $custom_fields = (array) $back_office_options['message_page_custom_fields'];
$item_custom_fields = (array) unserialize(htmlspecialchars_decode($_POST['custom_fields']));
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = do_shortcode($value); }
asort($custom_fields); $content = ''; foreach ($custom_fields as $key => $value) {
$field_value = (isset($item_custom_fields[$key]) ? $item_custom_fields[$key] : '');
if ((strlen($field_value) > 75) || (strstr($field_value, '
'))) { $rows = 3; } else { $rows = 1; }
$content .= '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="custom_field_'.$key.'">'.htmlspecialchars($value).'</label></strong></th>
<td><textarea style="padding: 0 0.25em; '.($rows == 1 ? 'height: 1.75em; ' : '').'width: 75%;" name="custom_field_'.$key.'" id="custom_field_'.$key.'" rows="'.$rows.'" cols="75">'.htmlspecialchars($field_value).'</textarea>'
.(((!strstr($field_value, ' ')) && (substr($field_value, 0, 4) == 'http')) ? ' <a style="vertical-align: 25%;" '.$urls_fields_links_markup.' href="'.htmlspecialchars($field_value).'">'.__('Link', 'contact-manager').'</a>' : '').'</td></tr>'; }
echo $content; if ($content == '') { echo '<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td>'.__('You have no custom field currently.', 'contact-manager').'</td></tr>'; } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th><td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div></div>

<div class="postbox" id="affiliation-module"<?php if (in_array('affiliation', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h3 id="affiliation"><strong><?php echo $modules['message']['affiliation']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('affiliation_manager_admin_menu')) { ?>
<a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#affiliation"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a>
<?php } else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To use affiliation, you must have installed and activated <a href="http://www.kleor.com/affiliation-manager">Affiliation Manager</a>.', 'contact-manager')); } ?></span></td></tr>
</tbody></table>
<div id="level-1-commission-module"<?php if (in_array('level-1-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-1-commission"><strong><?php echo $modules['message']['affiliation']['modules']['level-1-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 1 commission is awarded to the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="referrer"><?php _e('Referrer', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="referrer" id="referrer" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/30)))+'em';" onblur="this.style.height = '1.75em';" cols="25"><?php echo $_POST['referrer']; ?></textarea>
<span class="description" style="vertical-align: 25%;"><?php _e('Affiliate who referred this message (ID, login name or email address)', 'contact-manager'); ?></span> 
<?php if ((function_exists('affiliation_manager_admin_menu')) && ($_POST['referrer'] != '')) {
if (strstr($_POST['referrer'], '@')) { $result = false; }
else { $result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer']."'", OBJECT); }
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."affiliation_manager_clicks WHERE referrer = '".$_POST['referrer']."'", OBJECT);
$clicks_number = (int) (isset($row->total) ? $row->total : 0);
if (!function_exists('commerce_manager_admin_menu')) { $orders_number = 0; $recurring_payments_number = 0; }
else {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."commerce_manager_orders WHERE referrer = '".$_POST['referrer']."'", OBJECT);
$orders_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."commerce_manager_recurring_payments WHERE referrer = '".$_POST['referrer']."'", OBJECT);
$recurring_payments_number = (int) (isset($row->total) ? $row->total : 0); }
echo '<br />'.($result ? '<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliate&amp;id='.$result->id.'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliate&amp;id='.$result->id.'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a> | ' : '')
.'<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-statistics&amp;referrer='.$_POST['referrer'].'">'.__('Statistics', 'contact-manager').'</a>'
.($clicks_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-clicks&amp;referrer='.$_POST['referrer'].'&amp;start_date=0">'.__('Clicks', 'contact-manager').' <span style="color: #808080;">('.$clicks_number.')</span></a>')
.($orders_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-orders&amp;referrer='.$_POST['referrer'].'&amp;start_date=0">'.__('Orders', 'contact-manager').' <span style="color: #808080;">('.$orders_number.')</span></a>')
.($recurring_payments_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-recurring-payments&amp;referrer='.$_POST['referrer'].'&amp;start_date=0">'.__('Recurring payments', 'contact-manager').' <span style="color: #808080;">('.$recurring_payments_number.')</span></a>'); } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission_amount" id="commission_amount" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/30)))+'em';" onblur="this.style.height = '1.75em';" cols="25"><?php echo $_POST['commission_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span> 
<?php if (!isset($_GET['id'])) { echo '<span class="description" style="vertical-align: 25%;">'.__('Leave this field blank for 0.', 'contact-manager').'</span>'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="commission_status" id="commission_status" onchange="if (this.value == 'paid') { document.getElementById('commission-payment-date').style.display = ''; } else { document.getElementById('commission-payment-date').style.display = 'none'; }">
<option value=""<?php if ($_POST['commission_status'] == '') { echo ' selected="selected"'; } ?>><?php _e('None', 'contact-manager'); ?></option>
<option value="unpaid"<?php if ($_POST['commission_status'] == 'unpaid') { echo ' selected="selected"'; } ?>><?php _e('Unpaid', 'contact-manager'); ?></option>
<option value="paid"<?php if ($_POST['commission_status'] == 'paid') { echo ' selected="selected"'; } ?>><?php _e('Paid', 'contact-manager'); ?></option>
</select><?php if (isset($_GET['id'])) { echo '<input type="hidden" name="old_commission_status" value="'.$_POST['commission_status'].'" />'; } ?></td></tr>
<tr id="commission-payment-date" style="<?php if ($_POST['commission_status'] != 'paid') { echo 'display: none; '; } ?>vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission_payment_date"><?php _e('Payment date', 'contact-manager'); ?></label></strong></th>
<td><input class="date-pick" style="margin-right: 0.5em;" type="text" name="commission_payment_date" id="commission_payment_date" size="20" value="<?php echo $_POST['commission_payment_date']; ?>" />
<?php if ($_POST['commission_payment_date'] == '') { echo '<br /><span class="description">'.__('Leave this field blank if the commission is not paid, or for the current date if the commission is paid.', 'contact-manager').'</span>'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div>
<div id="level-2-commission-module"<?php if (in_array('level-2-commission', $undisplayed_modules)) { echo ' style="display: none;"'; } ?>>
<h4 id="level-2-commission"><strong><?php echo $modules['message']['affiliation']['modules']['level-2-commission']['name']; ?></strong></h4>
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('The level 2 commission is awarded to the referrer of the affiliate who referred the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#commissions-levels"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="referrer2"><?php _e('Referrer', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="referrer2" id="referrer2" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/30)))+'em';" onblur="this.style.height = '1.75em';" cols="25"><?php echo $_POST['referrer2']; ?></textarea>
<?php if (!isset($_GET['id'])) { echo '<span class="description" style="vertical-align: 25%;">'.__('Leave this field blank for the referrer of the affiliate who referred this message.', 'contact-manager').'</span>'; } ?>
<?php if ((function_exists('affiliation_manager_admin_menu')) && ($_POST['referrer2'] != '')) {
if (strstr($_POST['referrer2'], '@')) { $result = false; }
else { $result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$_POST['referrer2']."'", OBJECT); }
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."affiliation_manager_clicks WHERE referrer = '".$_POST['referrer2']."'", OBJECT);
$clicks_number = (int) (isset($row->total) ? $row->total : 0);
if (!function_exists('commerce_manager_admin_menu')) { $orders_number = 0; $recurring_payments_number = 0; }
else {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."commerce_manager_orders WHERE referrer = '".$_POST['referrer2']."'", OBJECT);
$orders_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."commerce_manager_recurring_payments WHERE referrer = '".$_POST['referrer2']."'", OBJECT);
$recurring_payments_number = (int) (isset($row->total) ? $row->total : 0); }
echo '<br />'.($result ? '<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliate&amp;id='.$result->id.'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliate&amp;id='.$result->id.'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a> | ' : '')
.'<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-statistics&amp;referrer='.$_POST['referrer2'].'">'.__('Statistics', 'contact-manager').'</a>'
.($clicks_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-clicks&amp;referrer='.$_POST['referrer2'].'&amp;start_date=0">'.__('Clicks', 'contact-manager').' <span style="color: #808080;">('.$clicks_number.')</span></a>')
.($orders_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-orders&amp;referrer='.$_POST['referrer2'].'&amp;start_date=0">'.__('Orders', 'contact-manager').' <span style="color: #808080;">('.$orders_number.')</span></a>')
.($recurring_payments_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-recurring-payments&amp;referrer='.$_POST['referrer2'].'&amp;start_date=0">'.__('Recurring payments', 'contact-manager').' <span style="color: #808080;">('.$recurring_payments_number.')</span></a>'); } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_amount"><?php _e('Amount', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 25%;" name="commission2_amount" id="commission2_amount" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/30)))+'em';" onblur="this.style.height = '1.75em';" cols="25"><?php echo $_POST['commission2_amount']; ?></textarea> <span style="vertical-align: 25%;"><?php echo $currency_code; ?></span> 
<?php if (!isset($_GET['id'])) { echo '<span class="description" style="vertical-align: 25%;">'.__('Leave this field blank for 0.', 'contact-manager').'</span>'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="commission2_status" id="commission2_status" onchange="if (this.value == 'paid') { document.getElementById('commission2-payment-date').style.display = ''; } else { document.getElementById('commission2-payment-date').style.display = 'none'; }">
<option value=""<?php if ($_POST['commission2_status'] == '') { echo ' selected="selected"'; } ?>><?php _e('None', 'contact-manager'); ?></option>
<option value="unpaid"<?php if ($_POST['commission2_status'] == 'unpaid') { echo ' selected="selected"'; } ?>><?php _e('Unpaid', 'contact-manager'); ?></option>
<option value="paid"<?php if ($_POST['commission2_status'] == 'paid') { echo ' selected="selected"'; } ?>><?php _e('Paid', 'contact-manager'); ?></option>
</select><?php if (isset($_GET['id'])) { echo '<input type="hidden" name="old_commission2_status" value="'.$_POST['commission2_status'].'" />'; } ?></td></tr>
<tr id="commission2-payment-date" style="<?php if ($_POST['commission2_status'] != 'paid') { echo 'display: none; '; } ?>vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="commission2_payment_date"><?php _e('Payment date', 'contact-manager'); ?></label></strong></th>
<td><input class="date-pick" style="margin-right: 0.5em;" type="text" name="commission2_payment_date" id="commission2_payment_date" size="20" value="<?php echo $_POST['commission2_payment_date']; ?>" />
<?php if ($_POST['commission2_payment_date'] == '') { echo '<br /><span class="description">'.__('Leave this field blank if the commission is not paid, or for the current date if the commission is paid.', 'contact-manager').'</span>'; } ?></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php echo (isset($_GET['id']) ? __('Update', 'contact-manager') : __('Save', 'contact-manager')); ?>" /></td></tr>
</tbody></table>
</div>
</div></div>

<?php if (!isset($_GET['id'])) {
if (!isset($_POST['submit'])) {
$contact_manager_options = (array) get_option('contact_manager');
foreach ($contact_manager_options as $key => $value) {
if (is_string($value)) { $contact_manager_options[$key] = htmlspecialchars($value); } }
foreach ($add_message_fields as $field) { $_POST[$field] = (isset($contact_manager_options[$field]) ? $contact_manager_options[$field] : ''); }
$_POST['message_notification_email_subject'] = '[message subject]'; }
foreach ($add_message_fields as $field) { if (!isset($_POST[$field])) { $_POST[$field] = ''; } }
$value = false; foreach ($add_message_modules as $module) { if (!$value) { $value = (!in_array($module, $undisplayed_modules)); } }
if ($value) { ?><p class="submit" style="margin: 0 20%;"><input type="hidden" name="submit" value="true" />
<input type="submit" class="button-secondary" name="update_fields" value="<?php _e('Complete the fields below with the informations about the sender, the message and the form', 'contact-manager'); ?>" /></p><?php } ?>

<div id="add-message-modules">
<?php if (!in_array('message-confirmation-email', $undisplayed_modules)) {
if (!isset($_POST['submit'])) { $_POST['message_confirmation_email_body'] = htmlspecialchars(get_option('contact_manager_message_confirmation_email_body')); } ?>
<div class="postbox" id="message-confirmation-email-module">
<h3 id="message-confirmation-email"><strong><?php echo $modules['message']['message-confirmation-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#message-confirmation-email"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_confirmation_email_sent" id="message_confirmation_email_sent" value="yes"<?php if ((isset($_POST['update_fields'])) && ($_POST['message_confirmation_email_sent'] == 'yes')) { echo ' checked="checked"'; } ?> /> <?php _e('Send a message confirmation email', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_sender" id="message_confirmation_email_sender" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['message_confirmation_email_sender']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_receiver" id="message_confirmation_email_receiver" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['message_confirmation_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_confirmation_email_subject" id="message_confirmation_email_subject" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['message_confirmation_email_subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_confirmation_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_confirmation_email_body" id="message_confirmation_email_body" rows="15" cols="75"><?php echo $_POST['message_confirmation_email_body']; ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<?php if (!in_array('message-notification-email', $undisplayed_modules)) {
if (!isset($_POST['submit'])) { $_POST['message_notification_email_body'] = htmlspecialchars(get_option('contact_manager_message_notification_email_body')); } ?>
<div class="postbox" id="message-notification-email-module">
<h3 id="message-notification-email"><strong><?php echo $modules['message']['message-notification-email']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#message-notification-email"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_notification_email_sent" id="message_notification_email_sent" value="yes"<?php if ((isset($_POST['update_fields'])) && ($_POST['message_notification_email_sent'] == 'yes')) { echo ' checked="checked"'; } ?> /> <?php _e('Send a message notification email', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_sender"><?php _e('Sender', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_sender" id="message_notification_email_sender" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['message_notification_email_sender']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_receiver"><?php _e('Receiver', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_receiver" id="message_notification_email_receiver" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['message_notification_email_receiver']; ?></textarea><br />
<span class="description"><?php _e('You can enter several email addresses. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_subject"><?php _e('Subject', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 75%;" name="message_notification_email_subject" id="message_notification_email_subject" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/90)))+'em';" onblur="this.style.height = '1.75em';" cols="75"><?php echo $_POST['message_notification_email_subject']; ?></textarea></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_notification_email_body"><?php _e('Body', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_notification_email_body" id="message_notification_email_body" rows="15" cols="75"><?php echo $_POST['message_notification_email_body']; ?></textarea>
<span class="description"><?php _e('You can insert shortcodes into <em>Sender</em>, <em>Receiver</em>, <em>Subject</em> and <em>Body</em> fields to display informations about the sender, the message and the form.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#email-shortcodes"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<?php if ((!in_array('autoresponders', $undisplayed_modules)) && (isset($modules['message']['autoresponders']))) { ?>
<div class="postbox" id="autoresponders-module">
<h3 id="autoresponders"><strong><?php echo $modules['message']['autoresponders']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#autoresponders"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php _e('You must make some adjustments so that the subscription works with some autoresponders.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_autoresponder" id="sender_subscribed_to_autoresponder" value="yes"<?php if ($_POST['sender_subscribed_to_autoresponder'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Subscribe the sender to an autoresponder list', 'contact-manager'); ?></label></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder"><?php _e('Autoresponder', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_autoresponder" id="sender_autoresponder">
<?php include CONTACT_MANAGER_PATH.'/libraries/autoresponders.php';
$autoresponder = do_shortcode($_POST['sender_autoresponder']);
foreach ($autoresponders as $value) {
echo '<option value="'.$value.'"'.($autoresponder == $value ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_autoresponder_list"><?php _e('List', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_autoresponder_list" id="sender_autoresponder_list" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['sender_autoresponder_list']; ?></textarea><br />
<span class="description"><?php _e('For some autoresponders, you must enter the list ID.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#autoresponders"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<?php if (!in_array('registration-as-a-client', $undisplayed_modules)) { ?>
<div class="postbox" id="registration-as-a-client-module">
<h3 id="registration-as-a-client"><strong><?php echo $modules['message']['registration-as-a-client']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('commerce_manager_admin_menu')) { ?>
<a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#registration-as-a-client"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a>
<?php } else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To subscribe the senders as clients, you must have installed and activated <a href="http://www.kleor.com/commerce-manager">Commerce Manager</a>.', 'contact-manager')); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_as_a_client" id="sender_subscribed_as_a_client" value="yes"<?php if ($_POST['sender_subscribed_as_a_client'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender as a client', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#registration-as-a-client"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."commerce_manager_clients_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_category_id" id="sender_client_category_id">
<option value="0"<?php if ($_POST['sender_client_category_id'] == 0) { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_client_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if ((function_exists('commerce_manager_admin_menu')) && ($_POST['sender_client_category_id'] > 0)) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."commerce_manager_clients WHERE category_id = ".$_POST['sender_client_category_id'], OBJECT);
$clients_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."commerce_manager_clients_categories WHERE category_id = ".$_POST['sender_client_category_id'], OBJECT);
$categories_number = (int) (isset($row->total) ? $row->total : 0);
echo '<br /><a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-client-category&amp;id='.$_POST['sender_client_category_id'].'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-client-category&amp;id='.$_POST['sender_client_category_id'].'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a>'
.($clients_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-clients&amp;category_id='.$_POST['sender_client_category_id'].'&amp;start_date=0">'.__('Clients', 'contact-manager').' <span style="color: #808080;">('.$clients_number.')</span></a>')
.($categories_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=commerce-manager-clients-categories&amp;category_id='.$_POST['sender_client_category_id'].'&amp;start_date=0">'.__('Subcategories', 'contact-manager').' <span style="color: #808080;">('.$categories_number.')</span></a>'); } ?></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_client_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_client_status" id="sender_client_status">
<option value="active"<?php if ($_POST['sender_client_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_client_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/commerce-manager/documentation/#client-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="commerce_registration_confirmation_email_sent" id="commerce_registration_confirmation_email_sent" value="yes"<?php if ((isset($_POST['update_fields'])) && ($_POST['commerce_registration_confirmation_email_sent'] == 'yes')) { echo ' checked="checked"'; } ?> /> <?php _e('Send a registration confirmation email', 'contact-manager'); ?></label><br />
<span class="description"><?php _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="commerce_registration_notification_email_sent" id="commerce_registration_notification_email_sent" value="yes"<?php if ((isset($_POST['update_fields'])) && ($_POST['commerce_registration_notification_email_sent'] == 'yes')) { echo ' checked="checked"'; } ?> /> <?php _e('Send a registration notification email', 'contact-manager'); ?></label><br />
<span class="description"><?php _e('You can configure this email through the <em>Client Area</em> page of Commerce Manager.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<?php if (!in_array('registration-to-affiliate-program', $undisplayed_modules)) { ?>
<div class="postbox" id="registration-to-affiliate-program-module">
<h3 id="registration-to-affiliate-program"><strong><?php echo $modules['message']['registration-to-affiliate-program']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('affiliation_manager_admin_menu')) { ?>
<a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#registration-to-affiliate-program"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a>
<?php } else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To use affiliation, you must have installed and activated <a href="http://www.kleor.com/affiliation-manager">Affiliation Manager</a>.', 'contact-manager')); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_affiliate_program" id="sender_subscribed_to_affiliate_program" value="yes"<?php if ($_POST['sender_subscribed_to_affiliate_program'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender to affiliate program', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#registration-to-affiliate-program"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."affiliation_manager_affiliates_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_category_id" id="sender_affiliate_category_id">
<option value="0"<?php if ($_POST['sender_affiliate_category_id'] == 0) { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_affiliate_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if ((function_exists('affiliation_manager_admin_menu')) && ($_POST['sender_affiliate_category_id'] > 0)) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE category_id = ".$_POST['sender_affiliate_category_id'], OBJECT);
$affiliates_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."affiliation_manager_affiliates_categories WHERE category_id = ".$_POST['sender_affiliate_category_id'], OBJECT);
$categories_number = (int) (isset($row->total) ? $row->total : 0);
echo '<br /><a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliate-category&amp;id='.$_POST['sender_affiliate_category_id'].'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliate-category&amp;id='.$_POST['sender_affiliate_category_id'].'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a>'
.($affiliates_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliates&amp;category_id='.$_POST['sender_affiliate_category_id'].'&amp;start_date=0">'.__('Affiliates', 'contact-manager').' <span style="color: #808080;">('.$affiliates_number.')</span></a>')
.($categories_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=affiliation-manager-affiliates-categories&amp;category_id='.$_POST['sender_affiliate_category_id'].'&amp;start_date=0">'.__('Subcategories', 'contact-manager').' <span style="color: #808080;">('.$categories_number.')</span></a>'); } ?></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_affiliate_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_affiliate_status" id="sender_affiliate_status">
<option value="active"<?php if ($_POST['sender_affiliate_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_affiliate_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/affiliation-manager/documentation/#affiliate-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="affiliation_registration_confirmation_email_sent" id="affiliation_registration_confirmation_email_sent" value="yes"<?php if ((isset($_POST['update_fields'])) && ($_POST['affiliation_registration_confirmation_email_sent'] == 'yes')) { echo ' checked="checked"'; } ?> /> <?php _e('Send a registration confirmation email', 'contact-manager'); ?></label><br />
<span class="description"><?php _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="affiliation_registration_notification_email_sent" id="affiliation_registration_notification_email_sent" value="yes"<?php if ((isset($_POST['update_fields'])) && ($_POST['affiliation_registration_notification_email_sent'] == 'yes')) { echo ' checked="checked"'; } ?> /> <?php _e('Send a registration notification email', 'contact-manager'); ?></label><br />
<span class="description"><?php _e('You can configure this email through the <em>Options</em> page of Affiliation Manager.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<?php if (!in_array('membership', $undisplayed_modules)) { ?>
<div class="postbox" id="membership-module">
<h3 id="membership"><strong><?php echo $modules['message']['membership']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><?php if (function_exists('membership_manager_admin_menu')) { ?>
<a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#membership"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a>
<?php } else { echo str_replace('<a', '<a '.$documentations_links_markup, __('To use membership, you must have installed and activated <a href="http://www.kleor.com/membership-manager">Membership Manager</a>.', 'contact-manager')); } ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_to_members_areas" id="sender_subscribed_to_members_areas" value="yes"<?php if ($_POST['sender_subscribed_to_members_areas'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender to a member area', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#membership"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas"><?php _e('Members areas', 'contact-manager'); ?></label></strong></th>
<td><textarea style="padding: 0 0.25em; height: 1.75em; width: 50%;" name="sender_members_areas" id="sender_members_areas" rows="1" onfocus="this.style.height = (1.75*Math.min(5, 1 + Math.floor(this.value.length/60)))+'em';" onblur="this.style.height = '1.75em';" cols="50"><?php echo $_POST['sender_members_areas']; ?></textarea>
<?php if ((function_exists('membership_manager_admin_menu')) && (is_numeric($_POST['sender_members_areas'])) && ($_POST['sender_members_areas'] > 0)) { echo '<br />
<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=membership-manager-member-area&amp;id='.$_POST['sender_members_areas'].'">'.__('Edit', 'contact-manager').'</a> | 
<a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=membership-manager-member-area&amp;id='.$_POST['sender_members_areas'].'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a>'; } ?><br />
<span class="description"><?php _e('You can enter several members areas IDs. Separate them with commas.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_members_areas_modifications"><?php _e('Automatic modifications', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 50%;" name="sender_members_areas_modifications" id="sender_members_areas_modifications" rows="2" cols="50"><?php echo $_POST['sender_members_areas_modifications']; ?></textarea>
<span class="description"><?php _e('You can offer a temporary access, and automatically modify the members areas to which the member can access when a certain date is reached.', 'contact-manager'); ?>
 <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/membership-manager/documentation/#members-areas-modifications"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<?php $categories = $wpdb->get_results("SELECT id, name FROM ".$wpdb->prefix."membership_manager_members_categories ORDER BY name ASC", OBJECT);
if ($categories) { ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_category_id"><?php _e('Category', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_category_id" id="sender_member_category_id">
<option value="0"<?php if ($_POST['sender_member_category_id'] == 0) { echo ' selected="selected"'; } ?>><?php _e('None ', 'contact-manager'); ?></option>
<?php foreach ($categories as $category) {
echo '<option value="'.$category->id.'"'.($_POST['sender_member_category_id'] == $category->id ? ' selected="selected"' : '').'>'.do_shortcode($category->name).'</option>'."\n"; } ?>
</select>
<?php if ((function_exists('membership_manager_admin_menu')) && ($_POST['sender_member_category_id'] > 0)) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."membership_manager_members WHERE category_id = ".$_POST['sender_member_category_id'], OBJECT);
$members_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."membership_manager_members_categories WHERE category_id = ".$_POST['sender_member_category_id'], OBJECT);
$categories_number = (int) (isset($row->total) ? $row->total : 0);
echo '<br /><a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=membership-manager-member-category&amp;id='.$_POST['sender_member_category_id'].'">'.__('Edit', 'contact-manager').'</a>
 | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=membership-manager-member-category&amp;id='.$_POST['sender_member_category_id'].'&amp;action=delete" class="delete">'.__('Delete', 'contact-manager').'</a>'
.($members_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=membership-manager-members&amp;category_id='.$_POST['sender_member_category_id'].'&amp;start_date=0">'.__('Members', 'contact-manager').' <span style="color: #808080;">('.$members_number.')</span></a>')
.($categories_number == 0 ? '' : ' | <a style="text-decoration: none;" '.$ids_fields_links_markup.' href="admin.php?page=membership-manager-members-categories&amp;category_id='.$_POST['sender_member_category_id'].'&amp;start_date=0">'.__('Subcategories', 'contact-manager').' <span style="color: #808080;">('.$categories_number.')</span></a>'); } ?></td></tr>
<?php } ?>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_member_status"><?php _e('Status', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_member_status" id="sender_member_status">
<option value="active"<?php if ($_POST['sender_member_status'] == 'active') { echo ' selected="selected"'; } ?>><?php _e('Active', 'contact-manager'); ?></option>
<option value="inactive"<?php if ($_POST['sender_member_status'] == 'inactive') { echo ' selected="selected"'; } ?>><?php _e('Inactive', 'contact-manager'); ?></option>
</select>
<span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/membership-manager/documentation/#member-status"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="membership_registration_confirmation_email_sent" id="membership_registration_confirmation_email_sent" value="yes"<?php if ((isset($_POST['update_fields'])) && ($_POST['membership_registration_confirmation_email_sent'] == 'yes')) { echo ' checked="checked"'; } ?> /> <?php _e('Send a registration confirmation email', 'contact-manager'); ?></label><br />
<span class="description"><?php _e('You can configure this email through the interface of Membership Manager.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="membership_registration_notification_email_sent" id="membership_registration_notification_email_sent" value="yes"<?php if ((isset($_POST['update_fields'])) && ($_POST['membership_registration_notification_email_sent'] == 'yes')) { echo ' checked="checked"'; } ?> /> <?php _e('Send a registration notification email', 'contact-manager'); ?></label><br />
<span class="description"><?php _e('You can configure this email through the interface of Membership Manager.', 'contact-manager'); ?></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<?php if (!in_array('wordpress', $undisplayed_modules)) { ?>
<div class="postbox" id="wordpress-module">
<h3 id="wordpress"><strong><?php echo $modules['message']['wordpress']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#wordpress"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="sender_subscribed_as_a_user" id="sender_subscribed_as_a_user" value="yes"<?php if ($_POST['sender_subscribed_as_a_user'] == 'yes') { echo ' checked="checked"'; } ?> /> 
<?php _e('Subscribe the sender as a user', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#wordpress"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="sender_user_role"><?php _e('Role', 'contact-manager'); ?></label></strong></th>
<td><select name="sender_user_role" id="sender_user_role">
<?php foreach (contact_manager_users_roles() as $role => $name) {
echo '<option value="'.$role.'"'.($_POST['sender_user_role'] == $role ? ' selected="selected"' : '').'>'.$name.'</option>'."\n"; } ?>
</select></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>

<?php if (!in_array('custom-instructions', $undisplayed_modules)) {
if (!isset($_POST['submit'])) { $_POST['message_custom_instructions'] = htmlspecialchars(get_option('contact_manager_message_custom_instructions')); } ?>
<div class="postbox" id="custom-instructions-module">
<h3 id="custom-instructions"><strong><?php echo $modules['message']['custom-instructions']['name']; ?></strong></h3>
<div class="inside">
<table class="form-table"><tbody>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><span class="description"><a <?php echo $default_options_links_markup; ?> href="admin.php?page=contact-manager#custom-instructions"><?php _e('Click here to configure the default options.', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><label><input type="checkbox" name="message_custom_instructions_executed" id="message_custom_instructions_executed" value="yes"<?php if ($_POST['message_custom_instructions_executed'] == 'yes') { echo ' checked="checked"'; } ?> /> <?php _e('Execute custom instructions', 'contact-manager'); ?></label> <span class="description"><a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"><strong><label for="message_custom_instructions"><?php _e('PHP code', 'contact-manager'); ?></label></strong></th>
<td><textarea style="float: left; margin-right: 1em; width: 75%;" name="message_custom_instructions" id="message_custom_instructions" rows="10" cols="75"><?php echo $_POST['message_custom_instructions']; ?></textarea>
<span class="description"><?php _e('You can add custom instructions that will be executed just after the sending of the message.', 'contact-manager'); ?> <a <?php echo $documentations_links_markup; ?> href="http://www.kleor.com/contact-manager/#custom-instructions"><?php _e('More informations', 'contact-manager'); ?></a></span></td></tr>
<tr style="vertical-align: top;"><th scope="row" style="width: 20%;"></th>
<td><input type="submit" class="button-secondary" name="submit" value="<?php _e('Save', 'contact-manager'); ?>" /></td></tr>
</tbody></table>
</div></div>
<?php } ?>
</div>

<?php } ?>
<p class="submit" style="margin: 0 20%;"><input type="submit" class="button-primary" name="submit" id="submit" value="<?php (isset($_GET['id']) ? _e('Save Changes', 'contact-manager') : _e('Save Message', 'contact-manager')); ?>" /></p>
<?php contact_manager_pages_module($back_office_options, 'message-page', $undisplayed_modules); ?>
</form>
</div>
</div>

<script type="text/javascript">
var anchor = window.location.hash;
<?php foreach ($modules['message'] as $key => $value) {
echo "if (anchor == '#".$key."') { document.getElementById('".$key."-module').style.display = 'block'; }\n";
if (isset($value['modules'])) { foreach ($value['modules'] as $module_key => $module_value) {
echo "if (anchor == '#".$module_key."') {
document.getElementById('".$key."-module').style.display = 'block';
document.getElementById('".$module_key."-module').style.display = 'block'; }\n"; } } } ?>
</script>

<?php if (isset($_POST['update_fields'])) { ?>
<script type="text/javascript">window.location = '#add-message-modules';</script>
<?php } }