<?php function subscribe_to_autoresponder($autoresponder, $list, $contact) {
if ($list != '') {
$contact['email_address'] = format_email_address($contact['email_address']);
$contact['website_url'] = format_url($contact['website_url']);
switch ($autoresponder) {
case 'AWeber': subscribe_to_aweber($list, $contact); break;
case 'CyberMailing': subscribe_to_cybermailing($list, $contact); break;
case 'GetResponse': subscribe_to_getresponse($list, $contact); break;
case 'SG Autorépondeur': subscribe_to_sg_autorepondeur($list, $contact); break; } } }


function subscribe_to_aweber($list, $contact) {
$list = str_replace('à', '@', $list);
if (!strstr($list, '@')) { $list = $list.'@aweber.com'; }
$subject = 'AWeber Subscription';
$body =
"\nEmail: ".$contact['email_address'].
"\nName: ".strip_accents($contact['first_name']).
"\nReferrer: ".$contact['referrer'];
$domain = $_SERVER['SERVER_NAME'];
if (substr($domain, 0, 4) == 'www.') { $domain = substr($domain, 4); }
$sender = 'wordpress@'.$domain;
wp_mail($list, $subject, $body, 'From: '.$sender); }


function subscribe_to_cybermailing($list, $contact) {
wp_remote_get('http://www.cybermailing.com/mailing/subscribe.php?'.
'Liste='.$list.'&amp;'.
'ListName='.$list.'&amp;'.
'Identifiant='.$contact['login'].'&amp;'.
'Name='.$contact['first_name'].'&amp;'.
'Email='.$contact['email_address'].'&amp;'.
'WebSite='.$contact['website_url']); }


function subscribe_to_getresponse($list, $contact) {
ini_set('display_errors', 0);
include_once dirname(__FILE__).'/jsonRPCClient.php';
$api_key = contact_data('getresponse_api_key');
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


function subscribe_to_sg_autorepondeur($list, $contact) {
$data = http_build_query(array(
'membreid' => contact_data('sg_autorepondeur_account_id'),
'codeactivationclient' => contact_data('sg_autorepondeur_activation_code'),
'inscription_normale' => 'non',
'listeid' => $list,
'email' => $contact['email_address'],
'nom' => $contact['last_name'],
'prenom' => $contact['first_name'],
'civilite' => '',
'adresse' => $contact['address'],
'codepostal' => $contact['postcode'],
'ville' => $contact['town'],
'pays' => $contact['country'],
'siteweb' => $contact['website_url'],
'telephone' => $contact['phone_number'],
'parrain' => $contact['referrer'],
'fax' => '',
'msn' => '',
'skype' => '',
'pseudo' => $contact['login'],
'sexe' => '',
'journaissance' => '',
'moisnaissance' => '',
'anneenaissance' => '',
'ip' => $contact['ip_address'],
'identite' => ''));
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