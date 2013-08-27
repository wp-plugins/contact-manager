<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
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
$markup = '';
foreach ($attributes as $key => $value) {
if ((!isset($atts[$key])) || ($atts[$key] == '')) { $atts[$key] = $attributes[$key]; } }
$name = str_replace('-', '_', format_nice_name($atts[0]));
if ((!isset($GLOBALS[$prefix.$name.'_error'])) && (!stristr($atts['style'], 'display:')) && (!stristr($atts['style'], 'display :'))) { $atts['style'] = 'display: none; '.$atts['style']; }
foreach ($attributes as $key => $value) {
if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } }
$content = '<span id="'.$prefix.$name.'_error"'.$markup.'>'.(isset($GLOBALS[$prefix.$name.'_error']) ? $GLOBALS[$prefix.$name.'_error'] : '').'</span>';