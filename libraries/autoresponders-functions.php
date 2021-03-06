<?php function kleor_subscribe_to_autoresponder($autoresponder, $list, $contact) {
if ($list != '') {
include CONTACT_MANAGER_PATH.'libraries/personal-informations.php';
foreach (array_merge($personal_informations, array('ip_address', 'referrer')) as $field) {
if (!isset($contact[$field])) { $contact[$field] = ''; } }
$contact['email_address'] = kleor_format_email_address($contact['email_address']);
$contact['website_url'] = kleor_format_url($contact['website_url']);
switch ($autoresponder) {
case 'AWeber': kleor_subscribe_to_aweber($list, $contact); break;
case 'CyberMailing': kleor_subscribe_to_cybermailing($list, $contact); break;
case 'GetResponse': kleor_subscribe_to_getresponse($list, $contact); break;
case 'MailChimp': kleor_subscribe_to_mailchimp($list, $contact); break;
case 'SG Autorépondeur': kleor_subscribe_to_sg_autorepondeur($list, $contact); break; } } }


function kleor_subscribe_to_aweber($list, $contact) {
$contact['first_name'] = kleor_strip_accents($contact['first_name']);
$array = explode('|', contact_data('aweber_api_key'));
if (count($array) != 4) { $email_parser = true; }
else {
$email_parser = false;
list($consumerKey, $consumerSecret, $accessToken, $accessSecret) = $array;
try {
if (!class_exists('AWeberAPI')) { include_once CONTACT_MANAGER_PATH.'libraries/aweber.php'; }
$aweber = new AWeberAPI($consumerKey, $consumerSecret);
$account = $aweber->getAccount($accessToken, $accessSecret);
if (!is_numeric(str_replace('awlist', '', $list))) {
$lists = $account->lists->find(array('name' => $list))->data;
if ((isset($lists['entries'])) && (isset($lists['entries'][0])) && (isset($lists['entries'][0]['id']))) { $list = $lists['entries'][0]['id']; } else { $email_parser = true; } }
if (!$email_parser) {
$list = $account->loadFromUrl('/accounts/'.$account->id.'/lists/'.preg_replace('/[^0-9]/', '', $list));
$list->subscribers->create(array(
'email' => $contact['email_address'],
'name' => $contact['first_name'],
'ip_address' => $contact['ip_address'],
'ad_tracking' => $contact['referrer'])); } }
catch(Exception $exc) { $email_parser = true; } }
if ($email_parser) {
if (is_numeric($list)) { $list = 'awlist'.$list; }
$list = str_replace('à', '@', $list);
if (!strstr($list, '@')) { $list = $list.'@aweber.com'; }
$subject = 'AWeber Subscription';
$body =
"\nEmail: ".$contact['email_address'].
"\nName: ".$contact['first_name'].
"\nReferrer: ".$contact['referrer'];
$domain = $_SERVER['SERVER_NAME'];
if (substr($domain, 0, 4) == 'www.') { $domain = substr($domain, 4); }
if (strlen($domain) < 36) { $sender = 'wordpress@'.$domain; } else { $sender = 'w@'.$domain; }
foreach (array($sender, $contact['first_name'].' <'.$contact['email_address'].'>') as $string) {
mail($list, $subject, $body, 'From: '.$string); } } }


function kleor_subscribe_to_cybermailing($list, $contact) {
wp_remote_get('http://www.cybermailing.com/mailing/subscribe.php?'.
'Liste='.$list.'&'.
'ListName='.$list.'&'.
'Identifiant='.$contact['login'].'&'.
'Name='.$contact['first_name'].'&'.
'Email='.$contact['email_address'].'&'.
'WebSite='.$contact['website_url'], array('timeout' => 10)); }


function kleor_subscribe_to_getresponse($list, $contact) {
ini_set('display_errors', 0);
if (!class_exists('jsonRPCClient')) { include_once CONTACT_MANAGER_PATH.'libraries/getresponse.php'; }
$api_key = contact_data('getresponse_api_key');
$client = new jsonRPCClient('http://api2.getresponse.com');
$campaigns = $client->get_campaigns($api_key, array('name' => array('EQUALS' => $list)));
$campaign_id = array_pop(array_keys($campaigns));
$data = array(
'campaign' => $campaign_id,
'name' => $contact['first_name'],
'email' => $contact['email_address']);
if ($contact['referrer'] != '') { $data['customs'] = array(array('name' => 'referrer', 'content' => $contact['referrer'])); }
$result = $client->add_contact($api_key, $data); }


function kleor_subscribe_to_mailchimp($list, $contact) {
if (!class_exists('MailChimp')) { include_once CONTACT_MANAGER_PATH.'libraries/mailchimp.php'; }
$api_key = contact_data('mailchimp_api_key');
$MailChimp = new MailChimp($api_key);
$result = $MailChimp->call('lists/subscribe', array(
'id' => $list,
'email' => array('email' => $contact['email_address']),
'merge_vars' => array('FNAME' => $contact['first_name'], 'LNAME' => $contact['last_name']),
'double_optin' => true,
'update_existing' => true,
'replace_interests' => false)); }


function kleor_subscribe_to_sg_autorepondeur($list, $contact) {
$data = array(
'membreid' => contact_data('sg_autorepondeur_account_id'),
'codeactivationclient' => contact_data('sg_autorepondeur_activation_code'),
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