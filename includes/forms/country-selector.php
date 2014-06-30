<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$atts = contact_shortcode_atts(array('required' => 'no'), $atts);
$markup = '';
$name = 'country_code';
$GLOBALS[$prefix.'fields'][] = $name;
$GLOBALS[$prefix.'fields'][] = 'country';
if ((in_array($name, $GLOBALS[$prefix.'required_fields'])) && ($atts['required'] != 'required')) { $atts['required'] = 'yes'; }
foreach (array($name, str_replace('_', '-', $name)) as $key) {
if (((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == '')) && (isset($_GET[$key]))) { $_POST[$prefix.$name] = htmlspecialchars($_GET[$key]); } }
if (!isset($_POST[$prefix.$name])) {
if (((!isset($_POST[$prefix.'country'])) || ($_POST[$prefix.'country'] == '')) && (isset($_GET['country']))) { $_POST[$prefix.'country'] = htmlspecialchars($_GET['country']); }
if ((!isset($_POST[$prefix.'submit'])) && ((!isset($_POST[$prefix.'country'])) || ($_POST[$prefix.'country'] == ''))
 && (function_exists('current_user_can')) && (!current_user_can('edit_pages')) && (!current_user_can('manage_options'))) {
if ((function_exists('affiliation_session')) && (affiliation_session()) && (affiliate_data('country') != '')) { $_POST[$prefix.'country'] = affiliate_data('country'); }
elseif ((function_exists('commerce_session')) && (commerce_session()) && (client_data('country') != '')) { $_POST[$prefix.'country'] = client_data('country'); }
elseif ((function_exists('membership_session')) && (membership_session()) && (member_data('country') != '')) { $_POST[$prefix.'country'] = member_data('country'); }
elseif ((function_exists('is_user_logged_in')) && (is_user_logged_in())) { $_POST[$prefix.'country'] = contact_user_data('country'); } } }
include CONTACT_MANAGER_PATH.'languages/countries/countries.php';
$countries_list = '<option value="">--</option>'."\n";
foreach ($countries as $country_code => $country) {
if ((isset($_POST[$prefix.$name])) && ($_POST[$prefix.$name] == $country_code)) { $_POST[$prefix.'country'] = $country; }
elseif ((isset($_POST[$prefix.'country'])) && ($_POST[$prefix.'country'] == $country)) { $_POST[$prefix.$name] = $country_code; }
$countries_list .= '<option value="'.$country_code.'"'.(((isset($_POST[$prefix.$name])) && ($_POST[$prefix.$name] == $country_code)) ? ' selected="selected"' : '').'>'.$country.'</option>'."\n"; }
if ((isset($_POST[$prefix.'submit'])) && (in_array($atts['required'], array('required', 'yes'))) && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))) { $GLOBALS[$prefix.'country_error'] = 'unfilled_field'; }
if (((!isset($GLOBALS['form_focus'])) || ($GLOBALS['form_focus'] == '')) && ((!isset($_POST[$prefix.$name])) || ($_POST[$prefix.$name] == ''))) { $GLOBALS['form_focus'] = $prefix.$name; }
foreach ($atts as $key => $value) {
switch ($key) {
case 'required': if (in_array($value, array('required', 'yes'))) {
$GLOBALS[$prefix.'required_fields'][] = $name; if ($value == $key) { $markup .= ' '.$key.'="'.$key.'"'; } } break;
default: if ((!in_array($key, array('id', 'name'))) && (is_string($key)) && ($value != '')) { $c = (strstr($value, '"') ? "'" : '"'); $markup .= ' '.$key.'='.$c.$value.$c; } } }
if (isset($GLOBALS[$prefix.'country_error'])) { $GLOBALS['form_error'] = 'yes'; }
$content = '<select name="'.$prefix.$name.'" id="'.$prefix.'country"'.$markup.'>'.$countries_list.'</select>';