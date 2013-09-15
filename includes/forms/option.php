<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
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
$markup = '';
foreach ($attributes as $key => $value) {
if ($key == 'value') { if (!isset($atts[$key])) { $atts[$key] = $content; } }
elseif ((!isset($atts[$key])) || ($atts[$key] == '')) { $atts[$key] = $attributes[$key]; } }

$content = do_shortcode($content);
$name = $GLOBALS['contact_field_name'];
if ((isset($_POST[$prefix.$name])) && ((isset($_POST[$prefix.'submit'])) || ($atts['selected'] == ''))) { $atts['selected'] = ($_POST[$prefix.$name] == $atts['value'] ? 'selected' : ''); }
$atts['value'] = quotes_entities($atts['value']);
foreach ($attributes as $key => $value) { if ((is_string($key)) && (($key == 'value') || ($atts[$key] != ''))) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } }

$content = '<option'.$markup.'>'.$content.'</option>';