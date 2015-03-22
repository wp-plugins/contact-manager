<?php if (!defined('DB_NAME')) {
$file = 'wp-config.php'; $i = 0;
while ((!file_exists($file)) && ($i < 8)) { $file = '../'.$file; $i = $i + 1; }
$wp_config_content = file_get_contents($file);
$content = trim($wp_config_content);
foreach (array('<?php', '<?') as $tag) { $n = strlen($tag); if (substr($content, 0, $n) == $tag) { $content = substr($content, $n); } }
if (substr($content, -2) == '?>') { $content = substr($content, 0, -2); }
$pattern = '#if([[:space:]]*)([(]*)([[:space:]]*)!([[:space:]]*)defined([[:space:]]*)\(([[:space:]]*)["\']ABSPATH["\']#';
if (!in_array($content, preg_grep($pattern, array($content)))) { $pattern = '#define([[:space:]]*)\(([[:space:]]*)["\']ABSPATH["\']#'; }
$array = preg_split($pattern, $content);
@eval($array[0].(substr(trim($array[0]), -1) == '{' ? '}' : ''));
if (isset($table_prefix)) { define(WPDB_PREFIX, $table_prefix); }
foreach (array('DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST', 'WPDB_PREFIX') as $constant) {
if (!defined($constant)) {
if ($constant == 'WPDB_PREFIX') { $pattern = '#\$table_prefix([[:space:]]*)=([[:space:]]*)#'; }
else { $pattern = '#define([[:space:]]*)\(([[:space:]]*)["\']'.$constant.'["\']([[:space:]]*),([[:space:]]*)#'; }
$array = preg_split($pattern, $wp_config_content);
if (isset($array[1])) {
$c = (substr($array[1], 0, 1) == '"' ? '"' : "'");
$strings = explode($c, substr($array[1], 1));
$i = 0; $value = $strings[0];
while (substr(str_replace('\\\\', '', $strings[$i]), -1) == '\\') {
$value = substr($value, 0, -1).$c.(isset($strings[$i + 1]) ? $strings[$i + 1] : ''); $i = $i + 1; }
define($constant, str_replace('\\\\', '\\', $value)); } } } }
$port = null; $socket = null; $host = DB_HOST;
$port_or_socket = strstr($host, ':');
if ($port_or_socket) {
$host = substr($host, 0, strpos($host, ':'));
$port_or_socket = substr($port_or_socket, 1);
if (strpos($port_or_socket, '/') != 0) {
$port = intval($port_or_socket);
$maybe_socket = strstr($port_or_socket, ':');
if ($maybe_socket) { $socket = substr($maybe_socket, 1); } }
else { $socket = $port_or_socket; } }
$link = mysqli_connect($host, DB_USER, DB_PASSWORD, DB_NAME, $port, $socket);