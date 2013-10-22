<?php function contact_form($atts) {
global $post, $wpdb;
$content = '';
$atts = array_map('contact_do_shortcode', (array) $atts);
extract(shortcode_atts(array('focus' => '', 'id' => 0, 'redirection' => ''), $atts));
$focus = format_nice_name($focus);
$id = (int) $id;
if ($id == 0) { $id = (int) (isset($GLOBALS['contact_form_id']) ? $GLOBALS['contact_form_id'] : 0); }
if (($id == 0) && ((!function_exists('current_user_can')) || (!current_user_can('edit_pages')))) { $id = 1; }
if (($id == 0) || ($id != contact_form_data(array(0 => 'id', 'id' => $id)))) {
if ((function_exists('current_user_can')) && (current_user_can('edit_pages'))) {
load_plugin_textdomain('contact-manager', false, 'contact-manager/languages');
$content = sprintf(__('You did not complete correctly the %1$s attribute of the %2$s shortcode.', 'contact-manager'), 'id', '[contact-form]'); } }
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
$tags = array('captcha', 'country-selector', 'input', 'label', 'option', 'select', 'textarea');
foreach ($tags as $tag) { remove_shortcode($tag); add_shortcode($tag, 'contact_form_'.str_replace('-', '_', $tag)); }
if (!isset($_POST['referring_url'])) { $_POST['referring_url'] = (isset($_GET['referring_url']) ? $_GET['referring_url'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')); }
if (isset($_POST[$prefix.'submit'])) {
foreach ($_POST as $key => $value) {
if (is_string($value)) {
$value = str_replace(array('[', ']'), array('&#91;', '&#93;'), quotes_entities($value));
$_POST[$key] = str_replace('\\&', '&', trim(mysql_real_escape_string($value))); } }
if (isset($_POST[$prefix.'country_code'])) {
include CONTACT_MANAGER_PATH.'/languages/countries/countries.php';
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
$GLOBALS[$prefix.'checkbox_fields'] = array();
$GLOBALS[$prefix.'radio_fields'] = array();
foreach (array(
'failed_upload_message',
'invalid_email_address_message',
'too_large_file_message',
'unauthorized_extension_message',
'unfilled_field_message') as $key) { $GLOBALS[$prefix.$key] = contact_form_data($key); }
$code = contact_form_data('code');
foreach (array('checkbox_fields', 'fields', 'radio_fields', 'required_fields') as $array) { $GLOBALS[$prefix.$array] = array_unique($GLOBALS[$prefix.$array]); }

if ((isset($_POST[$prefix.'submit'])) && (!isset($GLOBALS[$prefix.'processed']))) { include CONTACT_MANAGER_PATH.'/includes/forms/processing.php'; }
elseif ($GLOBALS[$canonical_prefix.'number'] == 1) {
$displays_count = contact_form_data('displays_count') + 1;
$results = $wpdb->query("UPDATE ".$wpdb->prefix."contact_manager_forms SET displays_count = ".$displays_count." WHERE id = ".$id); }

$required_fields_js = '';
foreach ($GLOBALS[$prefix.'required_fields'] as $field) {
$required_fields_js .= '
'.(in_array($field, $GLOBALS[$prefix.'radio_fields']) ? 'var '.$prefix.$field.'_checked = false;
for (i = 0; i < form.'.$prefix.$field.'.length; i++) { if (form.'.$prefix.$field.'[i].checked == true) { '.$prefix.$field.'_checked = true; } }
if (!'.$prefix.$field.'_checked)' : (in_array($field, $GLOBALS[$prefix.'checkbox_fields']) ? 'if (form.'.$prefix.$field.'.checked == false)' : 'if (form.'.$prefix.$field.'.value == "")')).' {
if (document.getElementById("'.$prefix.str_replace('country_code', 'country', $field).'_error")) {
document.getElementById("'.$prefix.str_replace('country_code', 'country', $field).'_error").style.display = "inline";
document.getElementById("'.$prefix.str_replace('country_code', 'country', $field).'_error").innerHTML = "'.$GLOBALS[$prefix.'unfilled_field_message'].'"; }
'.(in_array($field, $GLOBALS[$prefix.'radio_fields']) ? '' : 'if (!error) { form.'.$prefix.$field.'.focus(); } ').'error = true; }
else if (document.getElementById("'.$prefix.str_replace('country_code', 'country', $field).'_error")) {
document.getElementById("'.$prefix.str_replace('country_code', 'country', $field).'_error").style.display = "none";
document.getElementById("'.$prefix.str_replace('country_code', 'country', $field).'_error").innerHTML = ""; }'; }
$form_js = '
<script type="text/javascript">
'.($focus == 'yes' ? (isset($GLOBALS['form_focus']) ? str_replace($canonical_prefix, $prefix, $GLOBALS['form_focus']).$GLOBALS['form_focus'] : '') : '').'
function validate_'.substr($prefix, 0, -1).'(form) {
var error = false;
'.(in_array('email_address', $GLOBALS[$prefix.'fields']) ? 'form.'.$prefix.'email_address.value = format_email_address(form.'.$prefix.'email_address.value);' : '').'
'.$required_fields_js.'
'.(in_array('email_address', $GLOBALS[$prefix.'fields']) ? '
if (form.'.$prefix.'email_address.value != "") {
if ((form.'.$prefix.'email_address.value.indexOf("@") == -1) || (form.'.$prefix.'email_address.value.indexOf(".") == -1)) {
if (document.getElementById("'.$prefix.'email_address_error")) {
document.getElementById("'.$prefix.'email_address_error").style.display = "inline";
document.getElementById("'.$prefix.'email_address_error").innerHTML = "'.$GLOBALS[$prefix.'invalid_email_address_message'].'"; }
if (!error) { form.'.$prefix.'email_address.focus(); } error = true; }
else if (document.getElementById("'.$prefix.'email_address_error")) {
document.getElementById("'.$prefix.'email_address_error").style.display = "none";
document.getElementById("'.$prefix.'email_address_error").innerHTML = ""; } }' : '').'
return !error; }
</script>';

$tags = array_merge($tags, array('error', 'validation-content'));
foreach ($tags as $tag) { remove_shortcode($tag); add_shortcode($tag, 'contact_form_'.str_replace('-', '_', $tag)); }
if (!stristr($code, '<form')) { $code = '<form id="'.str_replace('_', '-', substr($prefix, 0, -1)).'" method="post" enctype="multipart/form-data" action="'.esc_attr($_SERVER['REQUEST_URI']).(substr($redirection, 0, 1) == '#' ? $redirection : '').'" onsubmit="return validate_'.substr($prefix, 0, -1).'(this);">'.$code; }
if (!stristr($code, '</form>')) { $code .= '<div style="display: none;"><input type="hidden" name="referring_url" value="'.htmlspecialchars($_POST['referring_url']).'" /><input type="hidden" name="'.$prefix.'submit" value="yes" /></div></form>'; }
$code = str_replace(array("\\t", '\\'), array('	', ''), str_replace(array("\\r\\n", "\\n", "\\r"), '
', do_shortcode($code)));
$content .= (isset($GLOBALS[$prefix.'recaptcha_js']) ? $GLOBALS[$prefix.'recaptcha_js'] : '').$code.$form_js;

foreach (array('contact_form_id', 'contact_form_data') as $key) {
if (isset($original[$key])) { $GLOBALS[$key] = $original[$key]; } }
foreach ($tags as $tag) { remove_shortcode($tag); } }
return $content; }


function contact_form_captcha($atts) { include CONTACT_MANAGER_PATH.'/includes/forms/captcha.php'; return $content; }


function contact_form_error($atts) { include CONTACT_MANAGER_PATH.'/includes/forms/error.php'; return $content; }


function contact_form_input($atts) { include CONTACT_MANAGER_PATH.'/includes/forms/input.php'; return $content; }


function contact_form_label($atts, $content) { include CONTACT_MANAGER_PATH.'/includes/forms/label.php'; return $content; }


function contact_form_option($atts, $content) { include CONTACT_MANAGER_PATH.'/includes/forms/option.php'; return $content; }


function contact_form_select($atts, $content) { include CONTACT_MANAGER_PATH.'/includes/forms/select.php'; return $content; }


function contact_form_textarea($atts, $content) { include CONTACT_MANAGER_PATH.'/includes/forms/textarea.php'; return $content; }


function contact_form_validation_content($atts, $content) { include CONTACT_MANAGER_PATH.'/includes/forms/validation-content.php'; return $content; }


function contact_form_country_selector($atts) { include CONTACT_MANAGER_PATH.'/includes/forms/country-selector.php'; return $content; }