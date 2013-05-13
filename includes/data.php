<?php global $contact_manager_options;
if (is_string($atts)) { $field = $atts; $decimals = ''; $default = ''; $filter = ''; $part = 0; }
else {
$field = (isset($atts[0]) ? $atts[0] : '');
foreach (array('decimals', 'default', 'filter') as $key) { $$key = (isset($atts[$key]) ? $atts[$key] : ''); }
$part = (int) (isset($atts['part']) ? $atts['part'] : 0); }
$field = str_replace('-', '_', format_nice_name($field));
if (($field == 'code') || (substr($field, -10) == 'email_body') || (substr($field, -19) == 'custom_instructions')) {
$data = get_option(substr('contact_manager_'.$field, 0, 64)); }
else { $data = (isset($contact_manager_options[$field]) ? $contact_manager_options[$field] : ''); }
if ($part > 0) { $data = explode(',', $data); $data = (isset($data[$part - 1]) ? trim($data[$part - 1]) : ''); }
$data = (string) do_shortcode($data);
if ($data == '') { $data = $default; }
$data = contact_format_data($field, $data);
$data = contact_filter_data($filter, $data);
$data = contact_decimals_data($decimals, $data);