<?php function contact_form($atts) {
$atts = array_map('contact_do_shortcode', (array) $atts);
extract(shortcode_atts(array('focus' => '', 'id' => '', 'redirection' => ''), $atts));
global $post, $wpdb;
$content = '';
$focus = format_nice_name($focus);
$id = (int) preg_replace('/[^0-9]/', '', $id);
if ($id == 0) { $id = (int) (isset($GLOBALS['contact_form_id']) ? $GLOBALS['contact_form_id'] : 0); }
if (($id == 0) && ((!function_exists('current_user_can')) || (!current_user_can('edit_pages')))) { $id = 1; }
if (($id == 0) || ($id != contact_form_data(array(0 => 'id', 'id' => $id)))) {
if ((function_exists('current_user_can')) && (current_user_can('edit_pages'))) {
load_plugin_textdomain('contact-manager', false, CONTACT_MANAGER_FOLDER.'/languages');
$content = sprintf(__('You did not complete correctly the %1$s attribute of the %2$s shortcode.', 'contact-manager'), 'id', '&#91;contact-form]')
.' '.sprintf(__('(<a href="%1$s">More informations</a>)', 'contact-manager'), 'http://www.kleor.com/contact-manager/#forms'); } }
else {
foreach (array('contact_form_id', 'contact_form_data') as $key) {
if (isset($GLOBALS[$key])) { $original[$key] = $GLOBALS[$key]; } }
$GLOBALS['contact_form_id'] = $id;
$canonical_prefix = 'contact_form'.$id.'_';
if (!isset($GLOBALS[$canonical_prefix.'number'])) { $GLOBALS[$canonical_prefix.'number'] = 1; }
else { $GLOBALS[$canonical_prefix.'number'] = $GLOBALS[$canonical_prefix.'number'] + 1; }
$deduplicator = ($GLOBALS[$canonical_prefix.'number'] == 1 ? '' : ($GLOBALS[$canonical_prefix.'number'] - 1).'_');
$prefix = $canonical_prefix.$deduplicator;
$GLOBALS['contact_form_prefix'] = $prefix;
if ($redirection == '#') { $redirection .= str_replace('_', '-', substr($prefix, 0, -1)); }
foreach (array(
'strip_accents_js',
'format_email_address_js') as $function) { add_action('wp_footer', $function); }
foreach (array('captcha', 'country-selector', 'error', 'input', 'label', 'option', 'select', 'textarea', 'validation-content') as $tag) { remove_shortcode($tag); }
$tags = array('captcha', 'country-selector', 'input', 'label', 'option', 'select', 'textarea');
foreach ($tags as $tag) { add_shortcode($tag, 'contact_form_'.str_replace('-', '_', $tag)); }
if (!isset($_POST['referring_url'])) { $_POST['referring_url'] = (isset($_GET['referring_url']) ? $_GET['referring_url'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')); }
if (isset($_POST[$prefix.'submit'])) {
if ((function_exists('mysqli_connect')) && (function_exists('mysqli_real_escape_string'))) { $link = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME); }
foreach ($_POST as $key => $value) {
if (($key != $prefix.'password') && (is_string($value))) {
$value = str_replace(array('[', ']'), array('&#91;', '&#93;'), quotes_entities($value));
$_POST[$key] = str_replace('\\&', '&', trim((((isset($link)) && ($link)) ? mysqli_real_escape_string($link, $value) : mysql_real_escape_string($value)))); } }
if (isset($_POST[$prefix.'country_code'])) {
include CONTACT_MANAGER_PATH.'languages/countries/countries.php';
$key = $_POST[$prefix.'country_code'];
if (isset($countries[$key])) { $_POST[$prefix.'country'] = $countries[$key]; } }
if (isset($_POST[$prefix.'email_address'])) { $_POST[$prefix.'email_address'] = format_email_address($_POST[$prefix.'email_address']); }
if (isset($_POST[$prefix.'first_name'])) { $_POST[$prefix.'first_name'] = format_name($_POST[$prefix.'first_name']); }
if (isset($_POST[$prefix.'last_name'])) { $_POST[$prefix.'last_name'] = format_name($_POST[$prefix.'last_name']); }
if (isset($_POST[$prefix.'website_url'])) { $_POST[$prefix.'website_url'] = format_url($_POST[$prefix.'website_url']); }
$_POST['referring_url'] = html_entity_decode($_POST['referring_url']);
if (str_replace('-', '_', format_nice_name($redirection)) == 'referring_url') { $redirection = $_POST['referring_url'];
if ((substr($redirection, 0, 4) == 'http') && (substr($redirection, 0, strlen(HOME_URL)) != HOME_URL)) { $redirection = ''; } } }
$maximum_messages_quantity_per_sender = contact_form_data('maximum_messages_quantity_per_sender');
if (is_numeric($maximum_messages_quantity_per_sender)) { $GLOBALS[$prefix.'required_fields'] = array('email_address'); }
else { $GLOBALS[$prefix.'required_fields'] = array(); }
$GLOBALS[$prefix.'fields'] = $GLOBALS[$prefix.'required_fields'];
$GLOBALS[$prefix.'confirmed_fields'] = array();
$GLOBALS[$prefix.'checkbox_fields'] = array();
$GLOBALS[$prefix.'radio_fields'] = array();
foreach (array(
'failed_upload_message',
'invalid_email_address_message',
'invalid_field_message',
'too_large_file_message',
'unauthorized_extension_message',
'unfilled_field_message') as $key) { $GLOBALS[$prefix.$key] = contact_form_data($key); }
$code = contact_form_data('code');
foreach (array('checkbox_fields', 'confirmed_fields', 'fields', 'radio_fields', 'required_fields') as $array) { $GLOBALS[$prefix.$array] = array_unique($GLOBALS[$prefix.$array]); }

if ((isset($_POST[$prefix.'submit'])) && (!isset($GLOBALS[$prefix.'processed']))) { include CONTACT_MANAGER_PATH.'includes/forms/processing.php'; }
elseif ($GLOBALS[$canonical_prefix.'number'] == 1) {
$displays_count = contact_form_data('displays_count') + 1;
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET displays_count = ".$displays_count." WHERE id = ".$id);
foreach (array('', $GLOBALS['contact_form_id']) as $string) {
$GLOBALS['contact_form'.$string.'_data'] = (array) (isset($GLOBALS['contact_form'.$string.'_data']) ? $GLOBALS['contact_form'.$string.'_data'] : array());
$GLOBALS['contact_form'.$string.'_data']['displays_count'] = $displays_count; } }

$required_fields_js = '';
foreach ($GLOBALS[$prefix.'required_fields'] as $field) {
$required_fields_js .= '
var element = document.getElementById("'.$prefix.str_replace('country_code', 'country', $field).'_error");
'.(in_array($field, $GLOBALS[$prefix.'radio_fields']) ? 'var '.$prefix.$field.'_checked = false;
for (i = 0, n = form.'.$prefix.$field.'.length; i < n; i++) { if (form.'.$prefix.$field.'[i].checked == true) { '.$prefix.$field.'_checked = true; } }
if (!'.$prefix.$field.'_checked)' : (in_array($field, $GLOBALS[$prefix.'checkbox_fields']) ? 'if (form.'.$prefix.$field.'.checked == false)' : 'if (form.'.$prefix.$field.'.value === "")')).' {
if (element) {
var message = element.getAttribute("data-unfilled-field-message");
if (!message) { message = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $GLOBALS[$prefix.'unfilled_field_message']).'"; }
element.style.display = "inline"; element.innerHTML = message; }
'.(in_array($field, $GLOBALS[$prefix.'radio_fields']) ? '' : 'if (!error) { form.'.$prefix.$field.'.focus(); } ').'error = true; }
else if (element) { element.style.display = "none"; element.innerHTML = ""; }'; }
$confirmed_fields_js = '';
foreach ($GLOBALS[$prefix.'confirmed_fields'] as $field) {
$confirmed_fields_js .= '
var element = document.getElementById("'.$prefix.'confirm_'.$field.'_error");
if (form.'.$prefix.'confirm_'.$field.'.value !== form.'.$prefix.$field.'.value) {
if (element) {
var message = element.getAttribute("data-invalid-field-message");
if (!message) { message = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $GLOBALS[$prefix.'invalid_field_message']).'"; }
element.style.display = "inline"; element.innerHTML = message; }
if (!error) { form.'.$prefix.'confirm_'.$field.'.focus(); } error = true; }
else if (element) { element.display = "none"; element.innerHTML = ""; }'; }
if (isset($GLOBALS['form_focus'])) { $form_focus = format_nice_name(strval($GLOBALS['form_focus'])); }
$form_js = '
<script type="text/javascript">
'.((($focus == 'yes') && (isset($form_focus))) ? ($deduplicator == '' ? 'element = document.getElementById("'.$form_focus.'"); if (element) { element.focus(); }'
 : 'element = document.getElementById("'.str_replace($prefix, $canonical_prefix, $form_focus).'"); if (element) { element.focus(); }
else { element = document.getElementById("'.str_replace($canonical_prefix, $prefix, $form_focus).'"); if (element) { element.focus(); } }')."\n" : '').
'function validate_'.substr($prefix, 0, -1).'(form) {
var error = false;
'.(in_array('email_address', $GLOBALS[$prefix.'fields']) ? 'form.'.$prefix.'email_address.value = format_email_address(form.'.$prefix.'email_address.value);'."\n" : '')
.$required_fields_js.$confirmed_fields_js.'
'.(in_array('email_address', $GLOBALS[$prefix.'fields']) ? '
if (form.'.$prefix.'email_address.value !== "") {
var element = document.getElementById("'.$prefix.'email_address_error");
if ((form.'.$prefix.'email_address.value.indexOf("@") == -1) || (form.'.$prefix.'email_address.value.indexOf(".") == -1)) {
if (element) {
var message = element.getAttribute("data-invalid-email-address-message");
if (!message) { message = "'.str_replace(array('\\', '"', "\r", "\n", 'script'), array('\\\\', '\"', "\\r", "\\n", 'scr"+"ipt'), $GLOBALS[$prefix.'invalid_email_address_message']).'"; }
element.style.display = "inline"; element.innerHTML = message; }
if (!error) { form.'.$prefix.'email_address.focus(); } error = true; }
else if (element) { element.style.display = "none"; element.innerHTML = ""; } }' : '').'
return !error; }
</script>';

$tags = array_merge($tags, array('error', 'validation-content'));
foreach ($tags as $tag) { add_shortcode($tag, 'contact_form_'.str_replace('-', '_', $tag)); }
if (!stristr($code, '<form')) { $code = '<form id="'.str_replace('_', '-', substr($prefix, 0, -1)).'" method="post" enctype="multipart/form-data" action="'.esc_attr($_SERVER['REQUEST_URI']).(substr($redirection, 0, 1) == '#' ? $redirection : '').'" onsubmit="return validate_'.substr($prefix, 0, -1).'(this);">'.$code; }
if (!stristr($code, '</form>')) { $code .= '<div style="display: none;"><input type="hidden" name="referring_url" value="'.htmlspecialchars($_POST['referring_url']).'" /><input type="hidden" name="'.$prefix.'submit" value="yes" /></div></form>'; }
$code = str_replace(array("\\t", '\\'), array('	', ''), str_replace(array("\\r\\n", "\\n", "\\r"), '
', do_shortcode($code)));
$content .= (isset($GLOBALS[$prefix.'recaptcha_js']) ? $GLOBALS[$prefix.'recaptcha_js'] : '').$code;
add_action('wp_footer', create_function('', 'echo \''.str_replace("'", "\'", $form_js).'\';'));

foreach (array('contact_form_id', 'contact_form_data') as $key) {
if (isset($original[$key])) { $GLOBALS[$key] = $original[$key]; } }
foreach ($tags as $tag) { remove_shortcode($tag); } }
return $content; }


function contact_form_captcha($atts) { include CONTACT_MANAGER_PATH.'includes/forms/captcha.php'; return $content; }


function contact_form_error($atts) { include CONTACT_MANAGER_PATH.'includes/forms/error.php'; return $content; }


function contact_form_input($atts) { include CONTACT_MANAGER_PATH.'includes/forms/input.php'; return $content; }


function contact_form_label($atts, $content) { include CONTACT_MANAGER_PATH.'includes/forms/label.php'; return $content; }


function contact_form_option($atts, $content) { include CONTACT_MANAGER_PATH.'includes/forms/option.php'; return $content; }


function contact_form_select($atts, $content) { include CONTACT_MANAGER_PATH.'includes/forms/select.php'; return $content; }


function contact_form_textarea($atts, $content) { include CONTACT_MANAGER_PATH.'includes/forms/textarea.php'; return $content; }


function contact_form_validation_content($atts, $content) { include CONTACT_MANAGER_PATH.'includes/forms/validation-content.php'; return $content; }


function contact_form_country_selector($atts) { include CONTACT_MANAGER_PATH.'includes/forms/country-selector.php'; return $content; }