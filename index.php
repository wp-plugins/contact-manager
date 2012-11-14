<?php if ((isset($_GET['action'])) || (isset($_GET['url']))) {
$file = 'wp-load.php'; $i = 0;
while ((!file_exists($file)) && ($i < 8)) { $file = '../'.$file; $i = $i + 1; }
include_once $file;
if (isset($_GET['action'])) {
switch ($_GET['action']) {
case 'install': install_contact_manager(); break;
default: if (!headers_sent()) { header('Location: '.HOME_URL); exit(); } } }
elseif (isset($_GET['url'])) {
$url = contact_decrypt_url($_SERVER['REQUEST_URI']);
if (!headers_sent()) { header('Location: '.$url); exit(); } } }
elseif (!headers_sent()) { header('Location: /'); exit(); }