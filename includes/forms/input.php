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
$main_name = (((substr($name, 0, 8) == 'confirm_') && (in_array(substr($name, 8), $GLOBALS[$prefix.'fields']))) ? substr($name, 8) : $name);
if ($main_name != $name) { $GLOBALS[$prefix.'confirmed_fields'][] = $main_name; }
if ((in_array($name, $GLOBALS[$prefix.'required_fields'])) && ($atts['required'] != 'required')) { $atts['required'] = 'yes'; }
switch ($main_name) {
case 'email_address': if ($atts['type'] == '') { $atts['type'] = 'email'; } break;
case 'message_confirmation_email_sent': case 'subscribed_to_autoresponder': if ($atts['type'] == '') { $atts['type'] = 'checkbox'; } break;
case 'password': if ($atts['type'] == '') { $atts['type'] = 'password'; } break;
case 'phone_number': if ($atts['type'] == '') { $atts['type'] = 'tel'; } break;
case 'submit': if ($atts['type'] == '') { $atts['type'] = 'submit'; } break;
case 'website_url': if ($atts['type'] == '') { $atts['type'] = 'url'; } break;
default: if ($atts['type'] == '') { $atts['type'] = 'text'; } }
$id_markup = ' id="'.$prefix.$name.'"';
switch ($atts['type']) {
case 'checkbox': $id_markup = ''; $GLOBALS[$prefix.'checkbox_fields'][] = $name; break;
case 'radio': $id_markup = ''; $GLOBALS[$prefix.'radio_fields'][] = $name; break;
case 'email': case 'password': case 'tel': case 'text': if ($atts['size'] == '') { $atts['size'] = '30'; } break;
case 'url': if ($atts['size'] == '') { $atts['size'] = '40'; } }
if ($atts['type'] == 'file') {
$extensions = array_unique(preg_split('#[^a-z0-9]#', strtolower($atts['extensions']), 0, PREG_SPLIT_NO_EMPTY));
$maxsize = round(1024*$atts['maxsize']);
if (isset($_POST[$prefix.'submit'])) {
$extension = strtolower(substr(strrchr($_FILES[$prefix.$name]['name'], '.'), 1));
if ($_FILES[$prefix.$name]['error'] == 4) { if (in_array($atts['required'], array('required', 'yes'))) { $GLOBALS[$prefix.$name.'_error'] = 'unfilled_field'; } }
elseif ((in_array($extension, array('php', 'php3', 'phtml'))) || ((count($extensions) > 0) && (!in_array($extension, $extensions)))) { $GLOBALS[$prefix.$name.'_error'] = 'unauthorized_extension'; }
elseif ((($maxsize > 0) && (filesize($_FILES[$prefix.$name]['tmp_name']) > $maxsize))
 || ($_FILES[$prefix.$name]['error'] == 1) || ($_FILES[$prefix.$name]['error'] == 2)) { $GLOBALS[$prefix.$name.'_error'] = 'too_large_file'; }
elseif ($_FILES[$prefix.$name]['error'] != 0) { $GLOBALS[$prefix.$name.'_error'] = 'failed_upload'; } } }
else {
if ($name == 'email_address') {
if ($atts['onmouseout'] == '') { $atts['onmouseout'] = "this.value = format_email_address(this.value);"; }
if (isset($_POST[$prefix.'submit'])) {
if ((isset($_POST[$prefix.$name])) && ($_POST[$prefix.$name] != '') && ((!strstr($_POST[$prefix.$name], '@')) || (!strstr($_POST[$prefix.$name], '.')))) {
$GLOBALS[$prefix.$name.'_error'] = 'invalid_email_address'; } } }
if ($name != 'submit') {
if ((isset($_POST[$prefix.'submit'])) && ($name != $main_name) && (isset($_POST[$prefix.$name])) && (isset($_POST[$prefix.$main_name]))
 && ($_POST[$prefix.$name] != $_POST[$prefix.$main_name])) { $GLOBALS[$prefix.$name.'_error'] = 'invalid_field'; }
if ((isset($_POST[$prefix.$name])) && ($_POST[$prefix.$name] != '')) {
if ((isset($_POST[$prefix.'submit'])) && (isset($atts['pattern'])) && ($atts['pattern'] != '')
 && (!in_array($_POST[$prefix.$name], preg_grep('#'.str_replace('#', '\#', $atts['pattern']).'#', array($_POST[$prefix.$name]))))) { $GLOBALS[$prefix.$name.'_error'] = 'invalid_field'; }
if ($atts['type'] == 'radio') { $atts['checked'] = ($_POST[$prefix.$name] == $atts['value'] ? 'checked' : ''); }
else { $atts['value'] = $_POST[$prefix.$name]; } }
elseif ((isset($_POST[$prefix.'submit'])) && (in_array($atts['required'], array('required', 'yes')))) { $GLOBALS[$prefix.$name.'_error'] = 'unfilled_field'; } }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if (($atts['value'] == '') && (isset($_GET[$key]))) { $atts['value'] = htmlspecialchars($_GET[$key]); } }
if ((!isset($_POST[$prefix.'submit'])) && ($atts['value'] == '')
 && (function_exists('current_user_can')) && (!current_user_can('edit_pages')) && (!current_user_can('manage_options'))) {
include CONTACT_MANAGER_PATH.'libraries/personal-informations.php';
if (in_array($name, $personal_informations)) {
if ((function_exists('affiliation_session')) && (affiliation_session()) && (affiliate_data($name) != '')) { $atts['value'] = affiliate_data($name); }
elseif ((function_exists('commerce_session')) && (commerce_session()) && (client_data($name) != '')) { $atts['value'] = client_data($name); }
elseif ((function_exists('membership_session')) && (membership_session()) && (member_data($name) != '')) { $atts['value'] = member_data($name); }
elseif ((function_exists('is_user_logged_in')) && (is_user_logged_in())) { $atts['value'] = contact_user_data($name); } } }
if (((!isset($GLOBALS['form_focus'])) || ($GLOBALS['form_focus'] == '')) && ($atts['value'] == '') && ($id_markup != '')) { $GLOBALS['form_focus'] = $prefix.$name; }
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