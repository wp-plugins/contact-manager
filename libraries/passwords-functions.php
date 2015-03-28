<?php function kleor_generate_password($length = 0) {
$length = (int) $length; if ($length <= 0) { $length = 12; }
$characters = 'bcdfghjklmnpqrstvwxzBCDFGHJKLMNPQRSTVWXZ01234567890123456789()[]{}<>-+=*.!?@#$%&';
$minimum_strength = min(64, $length*(3 + log($length, 2)) - ($length == 1 ? 2 : 3));
$n = 0; $best_password = ''; $best_strength = 0;
while (($n < 8) && ($best_strength < $minimum_strength)) {
$password = ''; for ($i = 0; $i < $length; $i++) { $password .= substr($characters, mt_rand(0, 79), 1); }
$n += 1; $strength = kleor_password_strength($password);
if ($strength > $best_strength) { $best_strength = $strength; $best_password = $password; } }
return $best_password; }


function kleor_generate_password_js() { ?>
<script type="text/javascript">
function kleor_generate_password(length) {
var length = parseInt(length); if (length <= 0) { length = 12; }
var characters = 'bcdfghjklmnpqrstvwxzBCDFGHJKLMNPQRSTVWXZ01234567890123456789()[]{}<>-+=*.!?@#$%&';
if (length == 1) { var minimum_strength = 1; }
else { var minimum_strength = Math.min(64, length*(3 + (Math.log(length))/Math.LN2) - 3); }
var n = 0; var best_password = ''; var best_strength = 0;
while ((n < 8) && (best_strength < minimum_strength)) {
var password = ''; for (i = 0; i < length; i++) { password += characters.substr(Math.floor(80*Math.random()), 1); }
n += 1; strength = kleor_password_strength(password);
if (strength > best_strength) { best_strength = strength; best_password = password; } }
return best_password; }
</script>
<?php }


function kleor_password_strength($password) {
$password = kleor_strip_accents($password);
$length = strlen($password); $strength = 0;
if ($length > 0) {
if ((strtolower($password) != $password) && (strtoupper($password) != $password)) { $strength += 1; }
foreach (array('/[0-9]/', '/[^a-zA-Z0-9]/') as $pattern) {
if (preg_replace($pattern, '', $password) != $password) { $strength += 1; } }
$characters = array(); for ($i = 0; $i < $length; $i++) { $characters[] = substr($password, $i, 1); }
$strength = $length*($strength + log(count(array_unique($characters)), 2)); }
return $strength; }


function kleor_password_strength_js() { ?>
<script type="text/javascript">
function kleor_password_strength(password) {
var password = kleor_strip_accents(password);
var length = password.length; var strength = 0;
if (length > 0) {
if ((password.toLowerCase() != password) && (password.toUpperCase() != password)) { strength += 1; }
if (password.replace(/[0-9]/g, '') != password) { strength += 1; }
if (password.replace(/[^a-zA-Z0-9]/g, '') != password) { strength += 1; }
var characters = []; for (i = 0; i < length; i++) {
var character = password.substr(i, 1);
if (password.substr(0, i).indexOf(character) == -1) { characters.push(character); } }
strength = length*(strength + (Math.log(characters.length))/Math.LN2); }
return strength; }
</script>
<?php }