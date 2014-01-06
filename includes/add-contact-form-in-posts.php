<?php global $post;
if ((contact_data('automatic_display_enabled') == 'yes')
 && ((contact_data('automatic_display_only_on_single_post_pages') == 'no') || (is_single()))) {
$id = contact_data('automatic_display_form_id');
$location = contact_data('automatic_display_location');
$quantity = contact_data('automatic_display_maximum_forms_quantity');
if (!isset($GLOBALS['contact_form'.$id.'_number'])) { $GLOBALS['contact_form'.$id.'_number'] = 0; }
foreach (array('top', 'bottom') as $string) {
if ((strstr($location, $string)) && (($quantity === 'unlimited') || ($GLOBALS['contact_form'.$id.'_number'] < $quantity))) {
include_once CONTACT_MANAGER_PATH.'forms.php';
$content = ($string == 'top' ? '' : $content).contact_form(array('id' => $id)).($string == 'bottom' ? '' : $content); } } }