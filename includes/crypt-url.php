<?php if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
switch($action) {
case 'decrypt':
if (strstr($url, '?url=')) {
$url = explode('?url=', $url);
$url = $url[1];
$url = base64_decode($url);
$url = trim(mcrypt_decrypt(MCRYPT_BLOWFISH, md5(contact_data('encrypted_urls_key')), $url, MCRYPT_MODE_ECB));
$url = explode('|', $url);
$T = $url[0];
$url = $url[1];
$S = time() - $T;
if ($S > 3600*contact_data('encrypted_urls_validity_duration')) { $url = HOME_URL; } } break;
case 'encrypt':
$url = time().'|'.$url;
$url = mcrypt_encrypt(MCRYPT_BLOWFISH, md5(contact_data('encrypted_urls_key')), $url, MCRYPT_MODE_ECB);
$url = base64_encode($url);
$url = CONTACT_MANAGER_URL.'?url='.$url; }