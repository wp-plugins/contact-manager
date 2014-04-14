<?php global $contact_manager_options;
if (empty($contact_manager_options)) { $contact_manager_options = (array) get_option('contact_manager'); }
if (is_string($atts)) { $field = $atts; $decimals = ''; $default = ''; $filter = ''; $part = 0; }
else {
$atts = array_map('contact_do_shortcode', (array) $atts);
$field = (isset($atts[0]) ? $atts[0] : '');
foreach (array('decimals', 'default', 'filter') as $key) {
$$key = (isset($atts[$key]) ? $atts[$key] : '');
if (isset($atts[$key])) { unset($atts[$key]); } }
$part = (int) (isset($atts['part']) ? preg_replace('/[^0-9]/', '', $atts['part']) : 0); }
$field = str_replace('-', '_', format_nice_name($field));
if (($field == 'code') || (substr($field, -10) == 'email_body') || (substr($field, -19) == 'custom_instructions')) {
$data = get_option(substr('contact_manager_'.$field, 0, 64)); }
else { $data = (isset($contact_manager_options[$field]) ? $contact_manager_options[$field] : ''); }
if ($part > 0) { $data = explode(',', $data); $data = (isset($data[$part - 1]) ? trim($data[$part - 1]) : ''); }
$data = (string) do_shortcode($data);
if (($data === '') && (function_exists('commerce_data')) && (in_array($field, array(
'getresponse_api_key',
'mailchimp_api_key',
'recaptcha_private_key',
'recaptcha_public_key',
'sg_autorepondeur_account_id',
'sg_autorepondeur_activation_code')))) { $data = commerce_data($atts); }
if ($data === '') { $data = $default; }
$data = contact_format_data($field, $data);
if ($data === '') { $data = $default; }
$data = contact_filter_data($filter, $data);
$data = contact_decimals_data($decimals, $data);