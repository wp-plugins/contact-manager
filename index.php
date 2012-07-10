<?php if (isset($_GET['url'])) {
$file = 'wp-load.php'; $i = 0;
while ((!file_exists($file)) && ($i < 8)) { $file = '../'.$file; $i = $i + 1; }
include_once $file;
$url = contact_decrypt_url($_SERVER['REQUEST_URI']);
if (!headers_sent()) { header('Location: '.$url); exit(); } }
else { if (!headers_sent()) { header('Location: /'); exit(); } }