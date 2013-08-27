<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$attributes = array(
0 => 'email_address',
'accesskey' => '',
'class' => '',
'dir' => '',
'id' => '',
'onblur' => '',
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
'style' => '',
'title' => '',
'xmlns' => '');
$markup = '';
foreach ($attributes as $key => $value) {
if ((!isset($atts[$key])) || ($atts[$key] == '')) { $atts[$key] = $attributes[$key]; }
if ((is_string($key)) && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } }
$name = str_replace('-', '_', format_nice_name($atts[0]));
$content = '<label for="'.$prefix.$name.'"'.$markup.'>'.do_shortcode($content).'</label>';