<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$atts = contact_shortcode_atts(array(0 => 'email_address', 'class' => 'error', 'style' => ''), $atts);
$markup = '';
$name = str_replace('-', '_', format_nice_name($atts[0]));
if ((!isset($GLOBALS[$prefix.$name.'_error'])) && (!stristr($atts['style'], 'display:')) && (!stristr($atts['style'], 'display :'))) { $atts['style'] = 'display: none; '.$atts['style']; }
foreach ($atts as $key => $value) {
if (($key != 'id') && (is_string($key)) && ($value != '')) { $c = (strstr($value, '"') ? "'" : '"'); $markup .= ' '.$key.'='.$c.$value.$c; } }
$error = (isset($GLOBALS[$prefix.$name.'_error']) ? $GLOBALS[$prefix.$name.'_error'] : '');
if ($error == '') { $message = ''; }
elseif (in_array($name, array('invalid_captcha', 'invalid_fields', 'maximum_messages_quantity_reached', 'unfilled_fields'))) {
$message = (isset($atts['data-'.str_replace('_', '-', $name).'-message']) ? $atts['data-'.str_replace('_', '-', $name).'-message'] : '');
if ($message == '') { $message = $error; } }
else {
$message = (isset($atts['data-'.str_replace('_', '-', $error).'-message']) ? $atts['data-'.str_replace('_', '-', $error).'-message'] : '');
if ($message == '') { $message = (isset($GLOBALS[$prefix.$error.'_message']) ? $GLOBALS[$prefix.$error.'_message'] : ''); } }
$content = '<span id="'.$prefix.$name.'_error"'.$markup.'>'.$message.'</span>';