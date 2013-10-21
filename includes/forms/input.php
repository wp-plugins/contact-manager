<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$atts = contact_shortcode_atts(array(
0 => 'submit',
'class' => '',
'extensions' => '',
'maxsize' => 0,
'onchange' => '',
'onmouseout' => '',
'required' => 'no',
'size' => '',
'type' => '',
'value' => ''), $atts);
$markup = '';
$name = str_replace('-', '_', format_nice_name($atts[0]));
$GLOBALS[$prefix.'fields'][] = $name;
if ((in_array($name, $GLOBALS[$prefix.'required_fields'])) && ($atts['required'] != 'required')) { $atts['required'] = 'yes'; }
switch ($name) {
case 'message_confirmation_email_sent': if ($atts['type'] == '') { $atts['type'] = 'checkbox'; } break;
case 'password': if ($atts['type'] == '') { $atts['type'] = 'password'; } break;
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
if ($_FILES[$prefix.$name]['error'] == 4) { if (in_array($atts['required'], array('required', 'yes'))) { $GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'unfilled_field_message']; } }
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
elseif ((isset($_POST[$prefix.'submit'])) && (in_array($atts['required'], array('required', 'yes')))) { $GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'unfilled_field_message']; } }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if (($atts['value'] == '') && (isset($_GET[$key]))) { $atts['value'] = utf8_encode(htmlspecialchars($_GET[$key])); } }
if ((!isset($_POST[$prefix.'submit'])) && ($atts['value'] == '')) {
include CONTACT_MANAGER_PATH.'/libraries/personal-informations.php';
if (in_array($name, $personal_informations)) {
if ((function_exists('affiliation_session')) && (affiliation_session())) { $atts['value'] = affiliate_data($name); }
elseif ((function_exists('commerce_session')) && (commerce_session())) { $atts['value'] = client_data($name); }
elseif ((function_exists('membership_session')) && (membership_session())) { $atts['value'] = member_data($name); }
elseif ((function_exists('is_user_logged_in')) && (is_user_logged_in()) && (function_exists('current_user_can')) && (!current_user_can('edit_pages')) && (!current_user_can('manage_options'))) { $atts['value'] = contact_user_data($name); } } }
if (((!isset($GLOBALS['form_focus'])) || ($GLOBALS['form_focus'] == '')) && ($atts['value'] == '') && ($id_markup != '')) { $GLOBALS['form_focus'] = 'document.getElementById("'.$prefix.$name.'").focus();'; }
if ((isset($_POST[$prefix.'submit'])) && ($atts['type'] == 'checkbox')) { $atts['checked'] = (isset($_POST[$prefix.$name]) ? 'checked' : ''); } }
foreach ($atts as $key => $value) {
switch ($key) {
case 'extensions': case 'maxsize': break;
case 'required': if (in_array($value, array('required', 'yes'))) {
if ($name != 'submit') { $GLOBALS[$prefix.'required_fields'][] = $name; } if ($value == $key) { $markup .= ' '.$key.'="'.$key.'"'; } } break;
default: if ($key == 'value') { $value = esc_attr($value); }
if ((!in_array($key, array('id', 'name'))) && (is_string($key)) && ($value != '')) { $c = (strstr($value, '"') ? "'" : '"'); $markup .= ' '.$key.'='.$c.$value.$c; } } }
if (isset($GLOBALS[$prefix.$name.'_error'])) { $GLOBALS['form_error'] = 'yes'; }
$content = '<input name="'.$prefix.$name.'"'.$id_markup.$markup.' />';