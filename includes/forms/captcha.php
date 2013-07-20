<?php $form_id = $GLOBALS['contact_form_id'];
$prefix = $GLOBALS['contact_form_prefix'];
$attributes = array(
'answer' => '',
'class' => 'captcha',
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
'question' => '',
'style' => '',
'theme' => contact_data('default_recaptcha_theme'),
'title' => '',
'type' => contact_data('default_captcha_type'),
'xmlns' => '');
if ((isset($atts['answer'])) && (isset($atts['question']))) { $atts['type'] = 'question'; }
$markup = '';
foreach ($attributes as $key => $value) {
if ((!isset($atts[$key])) || ($atts[$key] == '')) { $atts[$key] = $attributes[$key]; }
if ((is_string($key)) && ($key != 'answer') && ($key != 'question') && ($key != 'theme') && ($key != 'type') && ($atts[$key] != '')) { $markup .= ' '.$key.'="'.$atts[$key].'"'; } }
if ($atts['type'] == 'recaptcha') {
$GLOBALS[$prefix.'recaptcha_js'] = '<script type="text/javascript">var RecaptchaOptions = { lang: \''.strtolower(substr(WPLANG, 0, 2)).'\', theme: \''.$atts['theme'].'\' };</script>'."\n";
if (!function_exists('_recaptcha_qsencode')) { include_once CONTACT_MANAGER_PATH.'/libraries/recaptchalib.php'; }
foreach (array('public', 'private') as $string) {
if (!defined('RECAPTCHA_'.strtoupper($string).'_KEY')) {
$key = contact_data('recaptcha_'.$string.'_key');
if (($key == '') && (function_exists('commerce_data'))) { $key = commerce_data('recaptcha_'.$string.'_key'); }
define('RECAPTCHA_'.strtoupper($string).'_KEY', $key); } }
$content = str_replace(' frameborder="0"', '', recaptcha_get_html(RECAPTCHA_PUBLIC_KEY)); }
else {
switch ($atts['type']) {
case 'arithmetic':
$captchas_numbers = (array) get_option('contact_manager_captchas_numbers');
$m = mt_rand(0, 15);
$n = mt_rand(0, 15);
$string = $captchas_numbers[$m].' + '.$captchas_numbers[$n];
$valid_captcha = $m + $n; break;
case 'question':
$string = $atts['question'];
$valid_captcha = $atts['answer']; break;
case 'reversed-string':
include CONTACT_MANAGER_PATH.'/libraries/captchas.php';
$n = mt_rand(5, 12);
$string = '';
for ($i = 0; $i < $n; $i++) { $string .= $captchas_letters[mt_rand(0, 25)]; }
$valid_captcha = strrev($string); break; }
$content = '<label for="'.$prefix.'captcha"><span'.$markup.'>'.$string.'</span></label>
<input type="hidden" name="'.$prefix.'valid_captcha" value="'.hash('sha256', $valid_captcha).'" />'; }