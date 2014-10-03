<?php $lang = strtolower(substr(get_locale(), 0, 2));
if ($lang == '') { $lang = 'en'; }
$file = dirname(__FILE__).'/'.$lang.'.php';
if (!file_exists($file)) { $file = dirname(__FILE__).'/en.php'; }
include $file;