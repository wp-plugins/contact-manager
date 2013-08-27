<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
if (!isset($_POST[$prefix.'submit'])) { $content = ''; }
else {
$content = explode('[other]', do_shortcode($content));
if ((isset($GLOBALS['form_error'])) && ($GLOBALS['form_error'] == 'yes')) { $n = 1; } else { $n = 0; }
if (!isset($content[$n])) { $content[$n] = ''; }
$content = $content[$n]; }