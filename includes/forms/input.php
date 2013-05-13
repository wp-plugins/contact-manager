<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$attributes = array(
0 => 'submit',
'accept' => '',
'accesskey' => '',
'alt' => '',
'checked' => '',
'class' => '',
'dir' => '',
'disabled' => '',
'extensions' => '',
'maxlength' => '',
'maxsize' => 0,
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
$markup = '';
foreach ($attributes as $key => $value) {
if ((!isset($atts[$key])) || ($atts[$key] == '')) { $atts[$key] = $attributes[$key]; } }

$name = str_replace('-', '_', format_nice_name($atts[0]));
$GLOBALS[$prefix.'fields'][] = $name;
if (in_array($name, $GLOBALS[$prefix.'required_fields'])) { $atts['required'] = 'yes'; }
switch ($name) {
case 'message_confirmation_email_sent': if ($atts['type'] == '') { $atts['type'] = 'checkbox'; } break;
case 'submit': if ($atts['type'] == '') { $atts['type'] = 'submit'; } break;
default: if ($atts['type'] == '') { $atts['type'] = 'text'; } }
$id_markup = '';
switch ($atts['type']) {
case 'checkbox': $GLOBALS[$prefix.'checkbox_fields'][] = $name; break;
case 'radio': $GLOBALS[$prefix.'radio_fields'][] = $name; break;
case 'password': case 'text': if ($atts['size'] == '') { $atts['size'] = '30'; }
case 'password': case 'text': case 'file': $id_markup = ' id="'.$prefix.$name.'"'; }
if ($atts['type'] == 'file') {
$extensions = array_unique(preg_split('#[^a-z0-9]#', strtolower($atts['extensions']), 0, PREG_SPLIT_NO_EMPTY));
$maxsize = round(1024*$atts['maxsize']);
if (isset($_POST[$prefix.'submit'])) {
$extension = strtolower(substr(strrchr($_FILES[$prefix.$name]['name'], '.'), 1));
if ($_FILES[$prefix.$name]['error'] == 4) { if ($atts['required'] == 'yes') { $GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'unfilled_field_message']; } }
elseif ((in_array($extension, array('php', 'php3', 'phtml'))) || ((count($extensions) > 0) && (!in_array($extension, $extensions)))) { $GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'unauthorized_extension_message']; }
elseif ((($maxsize > 0) && (filesize($_FILES[$prefix.$name]['tmp_name']) > $maxsize))
 || ($_FILES[$prefix.$name]['error'] == 1) || ($_FILES[$prefix.$name]['error'] == 2)) { $GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'too_large_file_message']; }
elseif ($_FILES[$prefix.$name]['error'] != 0) { $GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'failed_upload_message']; } } }
else {
if ($name == 'email_address') {
if ($atts['onmouseout'] == '') { $atts['onmouseout'] = "this.value = format_email_address(this.value);"; }
if (isset($_POST[$prefix.'submit'])) {
if ((!strstr($_POST[$prefix.$name], '@')) || (!strstr($_POST[$prefix.$name], '.'))) {
$GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'invalid_email_address_message']; } } }
if ($name != 'submit') {
if ((isset($_POST[$prefix.$name])) && ($_POST[$prefix.$name] != '')) {
if ($atts['type'] == 'radio') { $atts['checked'] = ($_POST[$prefix.$name] == $atts['value'] ? 'checked' : ''); }
else { $atts['value'] = $_POST[$prefix.$name]; } }
elseif ((isset($_POST[$prefix.'submit'])) && ($atts['required'] == 'yes')) { $GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'unfilled_field_message']; } }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if (($atts['value'] == '') && (isset($_GET[$key]))) { $atts['value'] = utf8_encode(htmlspecialchars($_GET[$key])); } }
if ((!isset($_POST[$prefix.'submit'])) && ($atts['value'] == '')) {
include CONTACT_MANAGER_PATH.'/libraries/personal-informations.php';
if (in_array($name, $personal_informations)) {
if ((function_exists('affiliation_session')) && (affiliation_session())) { $atts['value'] = affiliate_data($name); }
elseif ((function_exists('commerce_session')) && (commerce_session())) { $atts['value'] = client_data($name); }
elseif ((function_exists('membership_session')) && (membership_session(''))) { $atts['value'] = member_data($name); }
elseif ((function_exists('is_user_logged_in')) && (is_user_logged_in()) && (function_exists('current_user_can')) && (!current_user_can('edit_pages'))) { $atts['value'] = contact_user_data($name); } } }
if (((!isset($GLOBALS['form_focus'])) || ($GLOBALS['form_focus'] == '')) && ($atts['value'] == '') && ($id_markup != '')) { $GLOBALS['form_focus'] = 'document.getElementById("'.$prefix.$name.'").focus();'; }
if ((isset($_POST[$prefix.'submit'])) && ($atts['type'] == 'checkbox')) { $atts['checked'] = (isset($_POST[$prefix.$name]) ? 'checked' : ''); }
$atts['value'] = quotes_entities($atts['value']); }
foreach ($attributes as $key => $value) {
switch ($key) {
case 'extensions': case 'maxsize': break;
case 'required': if (($name != 'submit') && ($atts['required'] == 'yes')) { $GLOBALS[$prefix.'required_fields'][] = $name; } break;
default: if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } } }

if (isset($GLOBALS[$prefix.$name.'_error'])) { $GLOBALS['form_error'] = 'yes'; }
$content = '<input name="'.$prefix.$name.'"'.$id_markup.$markup.' />';