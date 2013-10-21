<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$atts = contact_shortcode_atts(array('selected' => ''), $atts);
$content = do_shortcode($content);
if (!isset($atts['value'])) { $atts['value'] = esc_attr($content); }
$markup = '';
$name = $GLOBALS['contact_field_name'];
if ((isset($_POST[$prefix.$name])) && ((isset($_POST[$prefix.'submit'])) || ($atts['selected'] == ''))) { $atts['selected'] = ($_POST[$prefix.$name] == $atts['value'] ? 'selected' : ''); }
foreach ($atts as $key => $value) { if ((is_string($key)) && (($key == 'value') || ($value != ''))) { $c = (strstr($value, '"') ? "'" : '"'); $markup .= ' '.$key.'='.$c.$value.$c; } }
$content = '<option'.$markup.'>'.$content.'</option>';