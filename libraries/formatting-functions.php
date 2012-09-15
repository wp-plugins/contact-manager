<?php function fix_url() {
$url = $_SERVER['REQUEST_URI']; $error = false;
if (strstr($url, '&amp;')) { $url = str_replace('&amp;', '&', $url); $error = true; }
if (($error) && (!headers_sent())) { header('Location: '.$url); exit; } }


function format_email_address($string) {
$string = strtolower(trim(strip_tags($string)));
$string = str_replace('à', '@', $string);
$string = str_replace(';', '.', $string);
$string = str_replace(' ', '-', $string);
$string = strip_accents($string);
$string = preg_replace('/[^a-zA-Z0-9_@.-]/', '', $string);
return $string; }


function format_email_address_js() { ?>
<script type="text/javascript">
function format_email_address(string) {
string = string.toLowerCase();
string = string.replace(/[à]/gi, '@');
string = string.replace(/[;]/gi, '.');
string = string.replace(/[ ]/gi, '-');
string = strip_accents(string);
string = string.replace(/[^a-zA-Z0-9_@.-]/gi, '');
return string; }
</script>
<?php }


function format_instructions($string) {
$string = str_replace('<? ', '<?php ', trim($string));
if (substr($string, 0, 5) == '<?php') { $string = substr($string, 5); }
if (substr($string, -2) == '?>') { $string = substr($string, 0, -2); }
$string = trim($string);
return $string; }


function format_medium_nice_name($string) {
$string = strip_accents(trim(strip_tags($string)));
$string = str_replace(' ', '-', $string);
$string = preg_replace('/[^a-zA-Z0-9_-]/', '', $string);
return $string; }


function format_name($string) {
$string = strtolower(trim(strip_tags($string)));
$string = str_replace(array(' ', '_'), '-', $string);
$strings = explode('-', $string);
$n = count($strings);
for ($i = 0; $i < $n; $i++) { $strings[$i] = ucfirst($strings[$i]); }
$string = implode('-', $strings);
return $string; }


function format_name_js() { ?>
<script type="text/javascript">
function format_name(string) {
string = string.toLowerCase();
string = string.replace(/[ _]/gi, '-');
var strings = string.split('-');
var n = strings.length;
for (i = 0; i < n; i++) { strings[i] = (strings[i]).substr(0, 1).toUpperCase()+(strings[i]).substr(1); }
string = strings.join('-');
return string; }
</script>
<?php }


function format_nice_name($string) {
$string = strip_accents(strtolower(trim(strip_tags($string))));
$string = str_replace(' ', '-', $string);
$string = preg_replace('/[^a-zA-Z0-9_-]/', '', $string);
return $string; }


function format_nice_name_js() { ?>
<script type="text/javascript">
function format_nice_name(string) {
string = strip_accents(string.toLowerCase());
string = string.replace(/[ ]/gi, '-');
string = string.replace(/[^a-zA-Z0-9_-]/gi, '');
return string; }
</script>
<?php }


function format_url($string) {
if ($string != '') {
$string = trim(strip_tags($string));
$string = str_replace(' ', '-', $string);
if ((!strstr($string, 'http://')) && (!strstr($string, 'https://'))) {
$strings = explode('/', $string);
if (strstr($strings[0], '.')) { $string = 'http://'.$string; }
else { $string = 'http://'.$_SERVER['SERVER_NAME'].'/'.$string; } }
while (strstr($string, '//')) { $string = str_replace('//', '/', $string); }
$string = str_replace(':/', '://', $string); }
return $string; }


function quotes_entities($string) {
return str_replace(array("'", '"'), array("&apos;", '&quot;'), $string); }


function quotes_entities_decode($string) {
return str_replace(array("&apos;", '&quot;'), array("'", '"'), $string); }


function strip_accents($string) {
return str_replace(
explode(' ', 'á à â ä ã å ç é è ê ë í ì î ï ñ ó ò ô ö õ ø ú ù û ü ý ÿ Á À Â Ä Ã Å Ç É È Ê Ë Í Ì Î Ï Ñ Ó Ò Ô Ö Õ Ø Ú Ù Û Ü Ý Ÿ'),
explode(' ', 'a a a a a a c e e e e i i i i n o o o o o o u u u u y y A A A A A A C E E E E I I I I N O O O O O O U U U U Y Y'),
$string); }


function strip_accents_js() { ?>
<script type="text/javascript">
function strip_accents(string) {
string = string.replace(/[áàâäãå]/gi, 'a');
string = string.replace(/[ç]/gi, 'c');
string = string.replace(/[éèêë]/gi, 'e');
string = string.replace(/[íìîï]/gi, 'i');
string = string.replace(/[ñ]/gi, 'n');
string = string.replace(/[óòôöõø]/gi, 'o');
string = string.replace(/[úùûü]/gi, 'u');
string = string.replace(/[ýÿ]/gi, 'y');
string = string.replace(/[ÁÀÂÄÃÅ]/gi, 'A');
string = string.replace(/[Ç]/gi, 'C');
string = string.replace(/[ÉÈÊË]/gi, 'E');
string = string.replace(/[ÍÌÎÏ]/gi, 'I');
string = string.replace(/[Ñ]/gi, 'N');
string = string.replace(/[ÓÒÔÖÕØ]/gi, 'O');
string = string.replace(/[ÚÙÛÜ]/gi, 'U');
string = string.replace(/[ÝŸ]/gi, 'Y');
return string; }
</script>
<?php }