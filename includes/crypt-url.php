<?php date_default_timezone_set('UTC');
switch ($action) {
case 'decrypt':
if (strstr($url, '?url=')) {
$url = explode('?url=', $url);
$url = $url[1];
$url = base64_decode($url);
if (function_exists('mcrypt_decrypt')) {
$md5 = md5(contact_data('encrypted_urls_key'));
$url = mcrypt_decrypt(MCRYPT_BLOWFISH, $md5, $url, MCRYPT_MODE_ECB, substr($md5, 0, 8)); }
$url = explode('|', trim($url));
$T = $url[0];
$url = $url[1];
$S = time() - $T;
if ($S > 3600*contact_data('encrypted_urls_validity_duration')) { $url = HOME_URL; } }
else { $url = HOME_URL; } break;
case 'encrypt':
$url = time().'|'.$url;
if (function_exists('mcrypt_encrypt')) {
$md5 = md5(contact_data('encrypted_urls_key'));
$url = mcrypt_encrypt(MCRYPT_BLOWFISH, $md5, $url, MCRYPT_MODE_ECB, substr($md5, 0, 8)); }
$url = base64_encode($url);
$url = CONTACT_MANAGER_URL.'index.php?url='.$url; }