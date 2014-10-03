<?php function fix_url() {
$url = $_SERVER['REQUEST_URI']; $error = false;
if (strstr($url, '&amp;')) { $url = str_replace('&amp;', '&', $url); $error = true; }
if (($error) && (!headers_sent())) { header('Location: '.$url); exit(); } }


function format_email_address($string) {
$string = strtolower(trim(strip_tags($string)));
$string = str_replace('à', '@', $string);
$string = str_replace(';', '.', $string);
$string = str_replace(' ', '-', $string);
$string = strip_accents($string);
$string = preg_replace('/[^a-z0-9_@.-]/', '', $string);
return $string; }


function format_email_address_js() { ?>
<script type="text/javascript">
function format_email_address(string) {
string = string.toLowerCase();
string = string.replace(/[à]/g, '@');
string = string.replace(/[;]/g, '.');
string = string.replace(/[ ]/g, '-');
string = strip_accents(string);
string = string.replace(/[^a-z0-9_@.-]/g, '');
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
$string = str_replace('_', '-', $string);
foreach (array(' ', '-') as $character) {
$strings = explode($character, $string);
$n = count($strings);
for ($i = 0; $i < $n; $i++) { $strings[$i] = ucfirst($strings[$i]); }
$string = implode($character, $strings); }
return $string; }


function format_name_js() { ?>
<script type="text/javascript">
function format_name(string) {
string = string.toLowerCase();
string = string.replace('_', '-');
var characters = [' ', '-'];
for (character in characters) {
var strings = string.split(characters[character]);
var n = strings.length;
for (i = 0; i < n; i++) { strings[i] = (strings[i]).substr(0, 1).toUpperCase()+(strings[i]).substr(1); }
string = strings.join(characters[character]); }
return string; }
</script>
<?php }


function format_nice_name($string) {
$string = strip_accents(strtolower(trim(strip_tags($string))));
$string = str_replace(' ', '-', $string);
$string = preg_replace('/[^a-z0-9_-]/', '', $string);
return $string; }


function format_nice_name_js() { ?>
<script type="text/javascript">
function format_nice_name(string) {
string = strip_accents(string.toLowerCase());
string = string.replace(/[ ]/g, '-');
string = string.replace(/[^a-z0-9_-]/g, '');
return string; }
</script>
<?php }


function format_url($string) {
if ($string != '') {
$string = trim(strip_tags($string));
$string = str_replace(' ', '-', $string);
if ((substr($string, 0, 1) != '.') && (!strstr($string, 'http://')) && (!strstr($string, 'https://'))) {
$strings = explode('/', $string);
if (strstr($strings[0], '.')) { $string = 'http://'.$string; }
else { $string = 'http://'.$_SERVER['SERVER_NAME'].'/'.$string; } }
while (strstr($string, '//')) { $string = str_replace('//', '/', $string); }
$string = str_replace(':/', '://', $string); }
return $string; }


function quotes_entities($string) {
return str_replace(array("'", '"'), array("&apos;", '&quot;'), $string); }


function quotes_entities_decode($string) {
return str_replace(array("&apos;", "&#39;", "&#039;", '&quot;', '&#34;', '&#034;'), array("'", "'", "'", '"', '"', '"'), $string); }


function strip_accents($string) {
return str_replace(
explode(' ', 'á à â ä ã å ç é è ê ë í ì î ï ñ ó ò ô ö õ ø ú ù û ü ý ÿ Á À Â Ä Ã Å Ç É È Ê Ë Í Ì Î Ï Ñ Ó Ò Ô Ö Õ Ø Ú Ù Û Ü Ý Ÿ'),
explode(' ', 'a a a a a a c e e e e i i i i n o o o o o o u u u u y y A A A A A A C E E E E I I I I N O O O O O O U U U U Y Y'),
$string); }


function strip_accents_js() { ?>
<script type="text/javascript">
function strip_accents(string) {
string = string.replace(/[áàâäãå]/g, 'a');
string = string.replace(/[ç]/g, 'c');
string = string.replace(/[éèêë]/g, 'e');
string = string.replace(/[íìîï]/g, 'i');
string = string.replace(/[ñ]/g, 'n');
string = string.replace(/[óòôöõø]/g, 'o');
string = string.replace(/[úùûü]/g, 'u');
string = string.replace(/[ýÿ]/g, 'y');
string = string.replace(/[ÁÀÂÄÃÅ]/g, 'A');
string = string.replace(/[Ç]/g, 'C');
string = string.replace(/[ÉÈÊË]/g, 'E');
string = string.replace(/[ÍÌÎÏ]/g, 'I');
string = string.replace(/[Ñ]/g, 'N');
string = string.replace(/[ÓÒÔÖÕØ]/g, 'O');
string = string.replace(/[ÚÙÛÜ]/g, 'U');
string = string.replace(/[ÝŸ]/g, 'Y');
return string; }
</script>
<?php }