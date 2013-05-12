<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
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
$markup = '';
foreach ($attributes as $key => $value) {
if ((!isset($atts[$key])) || ($atts[$key] == '')) { $atts[$key] = $attributes[$key]; } }

$name = str_replace('-', '_', format_nice_name($atts[0]));
$GLOBALS[$prefix.'fields'][] = $name;
if (in_array($name, $GLOBALS[$prefix.'required_fields'])) { $atts['required'] = 'yes'; }
if ($name == 'email_address') {
if ($atts['onmouseout'] == '') { $atts['onmouseout'] = "this.value = format_email_address(this.value);"; }
if (isset($_POST[$prefix.'submit'])) {
if ((!strstr($_POST[$prefix.$name], '@')) || (!strstr($_POST[$prefix.$name], '.'))) {
$GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'invalid_email_address_message']; } } }
if ((!isset($_POST[$prefix.'submit'])) && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))) { $_POST[$prefix.$name] = do_shortcode($content); }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if (((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == '')) && (isset($_GET[$key]))) { $_POST[$prefix.$name] = utf8_encode(htmlspecialchars($_GET[$key])); } }
if ((!isset($_POST[$prefix.'submit'])) && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))) {
include CONTACT_MANAGER_PATH.'/libraries/personal-informations.php';
if (in_array($name, $personal_informations)) {
if ((function_exists('affiliation_session')) && (affiliation_session())) { $_POST[$prefix.$name] = affiliate_data($name); }
elseif ((function_exists('commerce_session')) && (commerce_session())) { $_POST[$prefix.$name] = client_data($name); }
elseif ((function_exists('membership_session')) && (membership_session(''))) { $_POST[$prefix.$name] = member_data($name); }
elseif ((function_exists('is_user_logged_in')) && (is_user_logged_in()) && (function_exists('current_user_can')) && (!current_user_can('edit_pages'))) { $_POST[$prefix.$name] = contact_user_data($name); } } }
if ((isset($_POST[$prefix.'submit'])) && ($atts['required'] == 'yes') && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))) { $GLOBALS[$prefix.$name.'_error'] = $GLOBALS[$prefix.'unfilled_field_message']; }
if (((!isset($GLOBALS['form_focus'])) || ($GLOBALS['form_focus'] == '')) && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))) { $GLOBALS['form_focus'] = 'document.getElementById("'.$prefix.$name.'").focus();'; }
foreach ($attributes as $key => $value) {
switch ($key) {
case 'required': if ($atts['required'] == 'yes') { $GLOBALS[$prefix.'required_fields'][] = $name; } break;
default: if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } } }

if (isset($GLOBALS[$prefix.$name.'_error'])) { $GLOBALS['form_error'] = 'yes'; }
$content = '<textarea name="'.$prefix.$name.'" id="'.$prefix.$name.'"'.$markup.'>'.(isset($_POST[$prefix.$name]) ? $_POST[$prefix.$name] : '').'</textarea>';