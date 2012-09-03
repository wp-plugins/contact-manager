<?php function contact_form($atts) {
global $post, $wpdb;
extract(shortcode_atts(array('focus' => '', 'id' => 0, 'redirection' => ''), $atts));
$focus = format_nice_name($focus);
$id = (int) $id;
if ($id == 0) { $id = $_GET['contact_form_id']; }
$prefix = 'contact_form'.$id.'_';
foreach (array('contact_form_id', 'contact_form_data') as $key) {
if (isset($_GET[$key])) { $original[$key] = $_GET[$key]; } }
$_GET['contact_form_id'] = $id;
if ($redirection == '#') { $redirection .= 'contact-form'.$id; }
foreach (array(
'strip_accents_js',
'format_email_address_js') as $function) { add_action('wp_footer', $function); }
$tags = array('captcha', 'input', 'label', 'option', 'select', 'textarea');
foreach ($tags as $tag) { add_shortcode($tag, 'contact_form_'.str_replace('-', '_', $tag)); }
if (!isset($_POST['referring_url'])) { $_POST['referring_url'] = htmlspecialchars($_SERVER['HTTP_REFERER']); }
if (isset($_POST[$prefix.'submit'])) {
foreach ($_POST as $key => $value) {
if (is_string($value)) {
$_POST[$key] = str_replace('\\&', '&', trim(mysql_real_escape_string(quotes_entities($value)))); } }
$_POST[$prefix.'first_name'] = format_name($_POST[$prefix.'first_name']);
$_POST[$prefix.'last_name'] = format_name($_POST[$prefix.'last_name']);
$_POST[$prefix.'email_address'] = format_email_address($_POST[$prefix.'email_address']);
$_POST[$prefix.'website_url'] = format_url($_POST[$prefix.'website_url']);
$_POST['referring_url'] = html_entity_decode($_POST['referring_url']); }
$maximum_messages_quantity_per_sender = contact_form_data('maximum_messages_quantity_per_sender');
if (is_numeric($maximum_messages_quantity_per_sender)) { $_GET[$prefix.'required_fields'] = array('email_address'); }
else { $_GET[$prefix.'required_fields'] = array(); }
$_GET[$prefix.'fields'] = $_GET[$prefix.'required_fields'];
$_GET[$prefix.'checkbox_fields'] = array();
foreach (array('invalid_email_address_message', 'unfilled_field_message') as $key) { $_GET[$prefix.$key] = contact_form_data($key); }
$code = contact_form_data('code');
foreach (array('fields', 'required_fields') as $array) { $_GET[$prefix.$array] = array_unique($_GET[$prefix.$array]); }

if (isset($_POST[$prefix.'submit'])) {
$_GET['form_error'] = '';
if (is_numeric($maximum_messages_quantity_per_sender)) {
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE email_address = '".$_POST[$prefix.'email_address']."' AND form_id = ".$id, OBJECT);
$messages_number = (int) $row->total;
if ($messages_number >= $maximum_messages_quantity_per_sender) {
$_GET[$prefix.'maximum_messages_quantity_reached_error'] = contact_form_data('maximum_messages_quantity_reached_message'); $_GET['form_error'] = 'yes'; } }
if (in_array('email_address', $_GET[$prefix.'fields'])) {
if ((!strstr($_POST[$prefix.'email_address'], '@')) || (!strstr($_POST[$prefix.'email_address'], '.'))) { $_GET['form_error'] = 'yes'; } }
foreach ($_GET[$prefix.'required_fields'] as $field) {
if ($_POST[$prefix.$field] == '') { $_GET[$prefix.'unfilled_fields_error'] = contact_form_data('unfilled_fields_message'); $_GET['form_error'] = 'yes'; } }
if (isset($_GET[$prefix.'recaptcha_js'])) {
$resp = recaptcha_check_answer(RECAPTCHA_PRIVATE_KEY, $_SERVER['REMOTE_ADDR'], $_POST['recaptcha_challenge_field'], $_POST['recaptcha_response_field']);
if (!$resp->is_valid) { $invalid_captcha = 'yes'; } }
elseif (in_array('captcha', $_GET[$prefix.'fields'])) {
if (hash('sha256', $_POST[$prefix.'captcha']) != $_POST[$prefix.'valid_captcha']) { $invalid_captcha = 'yes'; } }
if ($invalid_captcha == 'yes') { $_GET[$prefix.'invalid_captcha_error'] = contact_form_data('invalid_captcha_message'); $_GET['form_error'] = 'yes'; }
if ($_GET['form_error'] == '') {
foreach ($_POST as $key => $value) { $_POST[str_replace($prefix, '', $key)] = $value; }
$_POST['receiver'] = contact_form_data('message_notification_email_receiver');
$_POST['ip_address'] = $_SERVER['REMOTE_ADDR'];
$_POST['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
$_POST['form_id'] = $id;
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$_POST['date'] = date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET);
$_POST['date_utc'] = date('Y-m-d H:i:s');
$_GET['user_id'] = get_current_user_id();
if (function_exists('award_message_commission')) { award_message_commission(); }
if (function_exists('award_message_commission2')) { award_message_commission2(); }
foreach (array('message_id', 'message_data') as $key) {
if (isset($_GET[$key])) { $original[$key] = $_GET[$key]; unset($_GET[$key]); } }
$_GET['message_data'] = $_POST;
foreach (array('subject', 'content') as $field) {
if (!isset($_POST[$field])) { $_POST[$field] = contact_form_data('message_notification_email_'.($field == 'content' ? 'body' : $field)); } }
foreach (array('message_id', 'message_data') as $key) {
if (isset($original[$key])) { $_GET[$key] = $original[$key]; } }
$result = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix."contact_manager_messages WHERE email_address = '".$_POST['email_address']."' AND subject = '".$_POST['subject']."' AND content = '".$_POST['content']."'", OBJECT);
if (!$result) { add_message($_POST); }

if (($redirection != '') && (substr($redirection, 0, 1) != '#')) {
$redirection = format_url($redirection);
if (!headers_sent()) { header('Location: '.$redirection); exit; }
else { $content .= '<script type="text/javascript">window.location = \''.htmlspecialchars($redirection).'\';</script>'; } } } }

else {
$displays_count = contact_form_data('displays_count') + 1;
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET displays_count = ".$displays_count." WHERE id = ".$id); }

foreach ($_GET[$prefix.'required_fields'] as $field) {
$required_fields_js .= '
if '.(in_array($field, $_GET[$prefix.'checkbox_fields']) ? '(form.'.$prefix.$field.'.checked == false)' : '(form.'.$prefix.$field.'.value == "")').' {
if (document.getElementById("'.$prefix.$field.'_error")) { document.getElementById("'.$prefix.$field.'_error").innerHTML = "'.$_GET[$prefix.'unfilled_field_message'].'"; }
if (!error) { form.'.$prefix.$field.'.focus(); } error = true; }
else if (document.getElementById("'.$prefix.$field.'_error")) { document.getElementById("'.$prefix.$field.'_error").innerHTML = ""; }'; }
$form_js = '
<script type="text/javascript">
'.($focus == 'yes' ? $_GET['form_focus'] : '').'
function validate_contact_form'.$id.'(form) {
var error = false;
'.(in_array('email_address', $_GET[$prefix.'fields']) ? 'form.'.$prefix.'email_address.value = format_email_address(form.'.$prefix.'email_address.value);' : '').'
'.$required_fields_js.'
'.(in_array('email_address', $_GET[$prefix.'fields']) ? '
if (form.'.$prefix.'email_address.value != "") {
if ((form.'.$prefix.'email_address.value.indexOf("@") == -1) || (form.'.$prefix.'email_address.value.indexOf(".") == -1)) {
if (document.getElementById("'.$prefix.'email_address_error")) { document.getElementById("'.$prefix.'email_address_error").innerHTML = "'.$_GET[$prefix.'invalid_email_address_message'].'"; }
if (!error) { form.'.$prefix.'email_address.focus(); } error = true; }
else if (document.getElementById("'.$prefix.'email_address_error")) { document.getElementById("'.$prefix.'email_address_error").innerHTML = ""; } }' : '').'
return !error; }
</script>';

$tags = array_merge($tags, array('error', 'validation-content'));
foreach ($tags as $tag) { add_shortcode($tag, 'contact_form_'.str_replace('-', '_', $tag)); }
if (!stristr($code, '<form')) { $code = '<form id="contact-form'.$id.'" method="post" action="'.htmlspecialchars($_SERVER['REQUEST_URI']).(substr($redirection, 0, 1) == '#' ? $redirection : '').'" onsubmit="return validate_contact_form'.$id.'(this);">'.$code; }
if (!stristr($code, '</form>')) { $code .= '<div style="display: none;"><input type="hidden" name="referring_url" value="'.$_POST['referring_url'].'" /><input type="hidden" name="'.$prefix.'submit" value="yes" /></div></form>'; }
$code = str_replace(array("\\t", '\\'), array('	', ''), str_replace(array("\\r\\n", "\\n", "\\r"), '
', do_shortcode($code)));
$content .= $_GET[$prefix.'recaptcha_js'].$code.$form_js;

foreach (array('contact_form_id', 'contact_form_data') as $key) {
if (isset($original[$key])) { $_GET[$key] = $original[$key]; } }
foreach ($tags as $tag) { remove_shortcode($tag); }
return $content; }


function contact_form_captcha($atts) {
$form_id = $_GET['contact_form_id'];
$prefix = 'contact_form'.$form_id.'_';
$attributes = array(
'class' => 'captcha',
'dir' => '',
'onclick' => '',
'ondblclick' => '',
'onkeydown' => '',
'onkeypress' => '',
'onkeyup' => '',
'onmousedown' => '',
'onmousemove' => '',
'onmouseout' => '',
'onmouseover' => '',
'onmouseup' => '',
'style' => '',
'theme' => contact_data('default_recaptcha_theme'),
'title' => '',
'type' => contact_data('default_captcha_type'),
'xmlns' => '');
foreach ($attributes as $key => $value) {
if ($atts[$key] == '') { $atts[$key] = $attributes[$key]; }
if ((is_string($key)) && ($key != 'theme') && ($key != 'type') && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } }
if ($atts['type'] == 'recaptcha') {
$_GET[$prefix.'recaptcha_js'] = '<script type="text/javascript">var RecaptchaOptions = { lang: \''.strtolower(substr(WPLANG, 0, 2)).'\', theme: \''.$atts['theme'].'\' };</script>'."\n";
if (!function_exists('_recaptcha_qsencode')) { include_once dirname(__FILE__).'/libraries/recaptchalib.php'; }
foreach (array('public', 'private') as $string) {
if (!defined('RECAPTCHA_'.strtoupper($string).'_KEY')) {
$key = contact_data('recaptcha_'.$string.'_key');
if (($key == '') && (function_exists('commerce_data'))) { $key = commerce_data('recaptcha_'.$string.'_key'); }
define('RECAPTCHA_'.strtoupper($string).'_KEY', $key); } }
$content = str_replace(' frameborder="0"', '', recaptcha_get_html(RECAPTCHA_PUBLIC_KEY)); }
else {
switch ($atts['type']) {
case 'arithmetic':
$captchas_numbers = get_option('contact_manager_captchas_numbers');
$m = mt_rand(0, 15);
$n = mt_rand(0, 15);
$string = $captchas_numbers[$m].' + '.$captchas_numbers[$n];
$valid_captcha = $m + $n; break;
case 'reversed-string':
include dirname(__FILE__).'/libraries/captchas.php';
$n = mt_rand(5, 12);
for ($i = 0; $i < $n; $i++) { $string .= $captchas_letters[mt_rand(0, 25)]; }
$valid_captcha = strrev($string); break; }
$content = '<label for="'.$prefix.'captcha"><span'.$markup.'>'.$string.'</span></label>
<input type="hidden" name="'.$prefix.'valid_captcha" value="'.hash('sha256', $valid_captcha).'" />'; }
return $content; }


function contact_form_error($atts) {
$form_id = $_GET['contact_form_id'];
$prefix = 'contact_form'.$form_id.'_';
$attributes = array(
0 => 'email_address',
'class' => 'error',
'dir' => '',
'onclick' => '',
'ondblclick' => '',
'onkeydown' => '',
'onkeypress' => '',
'onkeyup' => '',
'onmousedown' => '',
'onmousemove' => '',
'onmouseout' => '',
'onmouseover' => '',
'onmouseup' => '',
'style' => '',
'title' => '',
'xmlns' => '');
foreach ($attributes as $key => $value) {
if ($atts[$key] == '') { $atts[$key] = $attributes[$key]; }
if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } }
$name = str_replace('-', '_', format_nice_name($atts[0]));
return '<span id="'.$prefix.$name.'_error"'.$markup.'>'.$_GET[$prefix.$name.'_error'].'</span>'; }


function contact_form_input($atts) {
$form_id = $_GET['contact_form_id'];
$prefix = 'contact_form'.$form_id.'_';
$attributes = array(
0 => 'submit',
'accept' => '',
'accesskey' => '',
'alt' => '',
'checked' => '',
'class' => '',
'dir' => '',
'disabled' => '',
'maxlength' => '',
'onblur' => '',
'onchange' => '',
'onclick' => '',
'ondblclick' => '',
'onfocus' => '',
'onkeydown' => '',
'onkeypress' => '',
'onkeyup' => '',
'onmousedown' => '',
'onmousemove' => '',
'onmouseout' => '',
'onmouseover' => '',
'onmouseup' => '',
'onselect' => '',
'readonly' => '',
'required' => 'no',
'size' => '',
'src' => '',
'style' => '',
'tabindex' => '',
'title' => '',
'type' => '',
'usemap' => '',
'value' => '',
'xmlns' => '');
foreach ($attributes as $key => $value) { if ($atts[$key] == '') { $atts[$key] = $attributes[$key]; } }

$name = str_replace('-', '_', format_nice_name($atts[0]));
$_GET[$prefix.'fields'][] = $name;
if (in_array($name, $_GET[$prefix.'required_fields'])) { $atts['required'] = 'yes'; }
switch ($name) {
case 'message_confirmation_email_sent': if ($atts['type'] == '') { $atts['type'] = 'checkbox'; } break;
case 'submit': if ($atts['type'] == '') { $atts['type'] = 'submit'; } break;
default: if ($atts['type'] == '') { $atts['type'] = 'text'; } }
switch ($atts['type']) {
case 'checkbox': $_GET[$prefix.'checkbox_fields'][] = $name; break;
case 'password': case 'text':
if ($atts['size'] == '') { $atts['size'] = '30'; }
$id_markup = ' id="'.$prefix.$name.'"'; break; }
if ($name == 'email_address') {
if ($atts['onmouseout'] == '') { $atts['onmouseout'] = "this.value = format_email_address(this.value);"; }
if (isset($_POST[$prefix.'submit'])) {
if ((!strstr($_POST[$prefix.$name], '@')) || (!strstr($_POST[$prefix.$name], '.'))) {
$_GET[$prefix.$name.'_error'] = $_GET[$prefix.'invalid_email_address_message']; } } }
if ($name != 'submit') {
if ($_POST[$prefix.$name] != '') { $atts['value'] = $_POST[$prefix.$name]; }
elseif ((isset($_POST[$prefix.'submit'])) && ($atts['required'] == 'yes')) { $_GET[$prefix.$name.'_error'] = $_GET[$prefix.'unfilled_field_message']; } }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if ($atts['value'] == '') { $atts['value'] = utf8_encode(htmlspecialchars($_GET[$key])); } }
if (($_GET['form_focus'] == '') && ($atts['value'] == '') && ($id_markup != '')) { $_GET['form_focus'] = 'document.getElementById("'.$prefix.$name.'").focus();'; }
if ((isset($_POST[$prefix.'submit'])) && ($atts['type'] == 'checkbox')) { $atts['checked'] = (isset($_POST[$prefix.$name]) ? 'checked' : ''); }
$atts['value'] = quotes_entities($atts['value']);
foreach ($attributes as $key => $value) {
switch ($key) {
case 'required': if (($name != 'submit') && ($atts['required'] == 'yes')) { $_GET[$prefix.'required_fields'][] = $name; } break;
default: if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } } }

return '<input name="'.$prefix.$name.'"'.$id_markup.$markup.' />'; }


function contact_form_label($atts, $content) {
$form_id = $_GET['contact_form_id'];
$prefix = 'contact_form'.$form_id.'_';
$attributes = array(
0 => 'email_address',
'accesskey' => '',
'class' => '',
'dir' => '',
'id' => '',
'onblur' => '',
'onclick' => '',
'ondblclick' => '',
'onfocus' => '',
'onkeydown' => '',
'onkeypress' => '',
'onkeyup' => '',
'onmousedown' => '',
'onmousemove' => '',
'onmouseout' => '',
'onmouseover' => '',
'onmouseup' => '',
'style' => '',
'title' => '',
'xmlns' => '');
foreach ($attributes as $key => $value) {
if ($atts[$key] == '') { $atts[$key] = $attributes[$key]; }
if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } }
$name = str_replace('-', '_', format_nice_name($atts[0]));
return '<label for="'.$prefix.$name.'"'.$markup.'>'.do_shortcode($content).'</label>'; }


function contact_form_option($atts, $content) {
$form_id = $_GET['contact_form_id'];
$prefix = 'contact_form'.$form_id.'_';
$attributes = array(
'class' => '',
'dir' => '',
'disabled' => '',
'id' => '',
'label' => '',
'onclick' => '',
'ondblclick' => '',
'onkeydown' => '',
'onkeypress' => '',
'onkeyup' => '',
'onmousedown' => '',
'onmousemove' => '',
'onmouseout' => '',
'onmouseover' => '',
'onmouseup' => '',
'selected' => '',
'style' => '',
'title' => '',
'value' => '',
'xmlns' => '');
foreach ($attributes as $key => $value) { if ($atts[$key] == '') { $atts[$key] = $attributes[$key]; } }

$content = do_shortcode($content);
$name = $_GET['contact_field_name'];
if ($atts['value'] == '') { $atts['value'] = $content; }
if ((isset($_POST[$prefix.'submit'])) || ($atts['selected'] == '')) { $atts['selected'] = ($_POST[$prefix.$name] == $atts['value'] ? 'selected' : ''); }
$atts['value'] = quotes_entities($atts['value']);
foreach ($attributes as $key => $value) { if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } }

return '<option'.$markup.'>'.$content.'</option>'; }


function contact_form_select($atts, $content) {
$form_id = $_GET['contact_form_id'];
$prefix = 'contact_form'.$form_id.'_';
$attributes = array(
0 => 'country',
'class' => '',
'dir' => '',
'disabled' => '',
'multiple' => '',
'onblur' => '',
'onchange' => '',
'onclick' => '',
'ondblclick' => '',
'onfocus' => '',
'onkeydown' => '',
'onkeypress' => '',
'onkeyup' => '',
'onmousedown' => '',
'onmousemove' => '',
'onmouseout' => '',
'onmouseover' => '',
'onmouseup' => '',
'required' => 'no',
'size' => '',
'style' => '',
'tabindex' => '',
'title' => '',
'xmlns' => '');
foreach ($attributes as $key => $value) { if ($atts[$key] == '') { $atts[$key] = $attributes[$key]; } }

$name = str_replace('-', '_', format_nice_name($atts[0]));
$_GET['contact_field_name'] = $name;
$_GET[$prefix.'fields'][] = $name;
if (in_array($name, $_GET[$prefix.'required_fields'])) { $atts['required'] = 'yes'; }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if ($_POST[$prefix.$name] == '') { $_POST[$prefix.$name] = utf8_encode(htmlspecialchars($_GET[$key])); } }
if ((isset($_POST[$prefix.'submit'])) && ($atts['required'] == 'yes') && ($_POST[$prefix.$name] == '')) { $_GET[$prefix.$name.'_error'] = $_GET[$prefix.'unfilled_field_message']; }
if (($_GET['form_focus'] == '') && ($_POST[$prefix.$name] == '')) { $_GET['form_focus'] = 'document.getElementById("'.$prefix.$name.'").focus();'; }
foreach ($attributes as $key => $value) {
switch ($key) {
case 'required': if ($atts['required'] == 'yes') { $_GET[$prefix.'required_fields'][] = $name; } break;
default: if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } } }

return '<select name="'.$prefix.$name.'" id="'.$prefix.$name.'"'.$markup.'>'.do_shortcode($content).'</select>'; }


function contact_form_textarea($atts, $content) {
$form_id = $_GET['contact_form_id'];
$prefix = 'contact_form'.$form_id.'_';
$attributes = array(
0 => 'content',
'accesskey' => '',
'class' => '',
'cols' => '60',
'dir' => '',
'disabled' => '',
'onblur' => '',
'onchange' => '',
'onclick' => '',
'ondblclick' => '',
'onfocus' => '',
'onkeydown' => '',
'onkeypress' => '',
'onkeyup' => '',
'onmousedown' => '',
'onmousemove' => '',
'onmouseout' => '',
'onmouseover' => '',
'onmouseup' => '',
'onselect' => '',
'readonly' => '',
'required' => 'no',
'rows' => '15',
'style' => '',
'tabindex' => '',
'title' => '',
'xmlns' => '');
foreach ($attributes as $key => $value) { if ($atts[$key] == '') { $atts[$key] = $attributes[$key]; } }

$name = str_replace('-', '_', format_nice_name($atts[0]));
$_GET[$prefix.'fields'][] = $name;
if (in_array($name, $_GET[$prefix.'required_fields'])) { $atts['required'] = 'yes'; }
if ($name == 'email_address') {
if ($atts['onmouseout'] == '') { $atts['onmouseout'] = "this.value = format_email_address(this.value);"; }
if (isset($_POST[$prefix.'submit'])) {
if ((!strstr($_POST[$prefix.$name], '@')) || (!strstr($_POST[$prefix.$name], '.'))) {
$_GET[$prefix.$name.'_error'] = $_GET[$prefix.'invalid_email_address_message']; } } }
if ((!isset($_POST[$prefix.'submit'])) && ($_POST[$prefix.$name] == '')) { $_POST[$prefix.$name] = do_shortcode($content); }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if ($_POST[$prefix.$name] == '') { $_POST[$prefix.$name] = utf8_encode(htmlspecialchars($_GET[$key])); } }
if ((isset($_POST[$prefix.'submit'])) && ($atts['required'] == 'yes') && ($_POST[$prefix.$name] == '')) { $_GET[$prefix.$name.'_error'] = $_GET[$prefix.'unfilled_field_message']; }
if (($_GET['form_focus'] == '') && ($_POST[$prefix.$name] == '')) { $_GET['form_focus'] = 'document.getElementById("'.$prefix.$name.'").focus();'; }
foreach ($attributes as $key => $value) {
switch ($key) {
case 'required': if ($atts['required'] == 'yes') { $_GET[$prefix.'required_fields'][] = $name; } break;
default: if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } } }

return '<textarea name="'.$prefix.$name.'" id="'.$prefix.$name.'"'.$markup.'>'.$_POST[$prefix.$name].'</textarea>'; }


function contact_form_validation_content($atts, $content) {
$form_id = $_GET['contact_form_id'];
if (isset($_POST['contact_form'.$form_id.'_submit'])) {
$content = explode('[other]', do_shortcode($content));
if ($_GET['form_error'] == 'yes') { $n = 1; } else { $n = 0; }
return $content[$n]; } }