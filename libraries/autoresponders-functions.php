<?php function subscribe_to_autoresponder($autoresponder, $list, $contact) {
if ($list != '') {
include dirname(__FILE__).'/personal-informations.php';
foreach (array_merge($personal_informations, array('ip_address', 'referrer')) as $field) {
if (!isset($contact[$field])) { $contact[$field] = ''; } }
$contact['email_address'] = format_email_address($contact['email_address']);
$contact['website_url'] = format_url($contact['website_url']);
switch ($autoresponder) {
case 'AWeber': subscribe_to_aweber($list, $contact); break;
case 'CyberMailing': subscribe_to_cybermailing($list, $contact); break;
case 'GetResponse': subscribe_to_getresponse($list, $contact); break;
case 'MailChimp': subscribe_to_mailchimp($list, $contact); break;
case 'SG Autorépondeur': subscribe_to_sg_autorepondeur($list, $contact); break; } } }


function subscribe_to_aweber($list, $contact) {
$list = str_replace('à', '@', $list);
if (!strstr($list, '@')) { $list = $list.'@aweber.com'; }
$contact['first_name'] = strip_accents($contact['first_name']);
$subject = 'AWeber Subscription';
$body =
"\nEmail: ".$contact['email_address'].
"\nName: ".$contact['first_name'].
"\nReferrer: ".$contact['referrer'];
$domain = $_SERVER['SERVER_NAME'];
if (substr($domain, 0, 4) == 'www.') { $domain = substr($domain, 4); }
if (strlen($domain) < 36) { $sender = 'wordpress@'.$domain; }
else { $sender = 'w@'.$domain; }
foreach (array($sender, $contact['first_name'].' <'.$contact['email_address'].'>') as $string) {
mail($list, $subject, $body, 'From: '.$string); } }


function subscribe_to_cybermailing($list, $contact) {
wp_remote_get('http://www.cybermailing.com/mailing/subscribe.php?'.
'Liste='.$list.'&'.
'ListName='.$list.'&'.
'Identifiant='.$contact['login'].'&'.
'Name='.$contact['first_name'].'&'.
'Email='.$contact['email_address'].'&'.
'WebSite='.$contact['website_url']); }


function subscribe_to_getresponse($list, $contact) {
ini_set('display_errors', 0);
include_once dirname(__FILE__).'/jsonRPCClient.php';
$api_key = contact_data('getresponse_api_key');
if (($api_key == '') && (function_exists('commerce_data'))) { $api_key = commerce_data('getresponse_api_key'); }
$client = new jsonRPCClient('http://api2.getresponse.com');
$result = NULL;
try { $result = $client->get_campaigns($api_key, array('name' => array('EQUALS' => $list))); }
catch (Exception $e) { die($e->getMessage()); }
$campaign_id = array_pop(array_keys($result));
$data = array(
'campaign' => $campaign_id,
'name' => $contact['first_name'],
'email' => $contact['email_address'],
'cycle_day' => '0');
if ($contact['referrer'] != '') { $data['customs'] = array(array('name' => 'referrer', 'content' => $contact['referrer'])); }
try { $result = $client->add_contact($api_key, $data); }
catch (Exception $e) { die($e->getMessage()); } }


function subscribe_to_mailchimp($list, $contact) {
include_once dirname(__FILE__).'/MCAPI.class.php';
$apiUrl = 'http://api.mailchimp.com/1.3/';
$api_key = contact_data('mailchimp_api_key');
if (($api_key == '') && (function_exists('commerce_data'))) { $api_key = commerce_data('mailchimp_api_key'); }
$api = new MCAPI($api_key);
$data = array('FNAME' => $contact['first_name'], 'LNAME' => $contact['last_name']);
$result = $api->listSubscribe($list, $contact['email_address'], $data); }


function subscribe_to_sg_autorepondeur($list, $contact) {
foreach (array('id', 'code') as $key) {
$$key = contact_data('sg_autorepondeur_account_'.$key);
if (($$key == '') && (function_exists('commerce_data'))) { $$key = commerce_data('sg_autorepondeur_account_'.$key); } }
$data = array(
'membreid' => $id,
'codeactivationclient' => $code,
'inscription_normale' => 'non',
'listeid' => $list,
'email' => $contact['email_address'],
'nom' => $contact['last_name'],
'prenom' => $contact['first_name'],
'adresse' => $contact['address'],
'codepostal' => $contact['postcode'],
'ville' => $contact['town'],
'pays' => $contact['country'],
'siteweb' => $contact['website_url'],
'telephone' => $contact['phone_number'],
'parrain' => $contact['referrer'],
'pseudo' => $contact['login'],
'ip' => $contact['ip_address']);
if (is_serialized($contact['custom_fields'])) { $custom_fields = (array) unserialize(stripslashes($contact['custom_fields'])); }
else { $custom_fields = (array) $contact['custom_fields']; }
for ($i = 1; $i <= 16; $i++) {
if (isset($custom_fields['"'.$i.'"'])) { $data['champs_'.$i] = $custom_fields['"'.$i.'"']; }
elseif (isset($custom_fields[$i])) { $data['champs_'.$i] = $custom_fields[$i]; } }
$data = http_build_query($data);
$fp = fsockopen('sg-autorepondeur.com', 80);
fwrite($fp, "POST /inscr_decrypt.php HTTP/1.1\r\n");
fwrite($fp, "Host: sg-autorepondeur.com\r\n");
fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
fwrite($fp, "Content-Length: ".strlen($data)."\r\n");
fwrite($fp, "Connection: close\r\n");
fwrite($fp, "\r\n");
fwrite($fp, $data);
$headers = array();
$body = array();
$in_body = false;
while (!feof($fp)) {
if (!$in_body) {
$line = trim(fgets($fp, 1024));
if ($line != '') { $headers[] = $line; } 
else { $in_body = true; continue; } }
else { $body[] = fgets($fp, 1024); } }
$body = trim(implode('', $body));
$result = array('headers' => $headers, 'body' => $body, 'successful' => !strlen($body)); }