<?php global $wpdb; $error = '';
include 'tables.php';
include_once 'tables-functions.php';
$back_office_options = (array) get_option('contact_manager_back_office');
$undisplayed_rows = (array) $back_office_options['statistics_page_undisplayed_rows'];
$undisplayed_columns = (array) $back_office_options['statistics_page_undisplayed_columns'];
include 'admin-pages.php';
$options = (array) get_option('contact_manager_statistics');

$tables_names = array(
'forms' => __('Forms', 'contact-manager'),
'forms_categories' => __('Forms categories', 'contact-manager'),
'messages' => __('Messages', 'contact-manager'));
$max_tables = count($tables_names);

$filterby_options = array(
'postcode' => __('postcode', 'contact-manager'),
'town' => __('town', 'contact-manager'),
'country' => __('country', 'contact-manager'),
'ip_address' => __('IP address ', 'contact-manager'),
'user_agent' => __('user agent', 'contact-manager'),
'referring_url' => __('referring URL', 'contact-manager'),
'form_id' => __('form ID', 'contact-manager'),
'referrer' => __('referrer', 'contact-manager'));

if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes($value); } }
$_GET['s'] = $_POST['s'];
$filterby = $_POST['filterby'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$displayed_tables = array();
for ($i = 0; $i < $max_tables; $i++) {
$tables_slugs[$i] = $_POST['table'.$i];
if (isset($_POST['table'.$i.'_displayed'])) { $displayed_tables[] = $i; } } }
else {
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
$displayed_tables = (array) $options['displayed_tables'];
$end_date = date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET);
$filterby = $options['filterby'];
$start_date = $options['start_date'];
$tables_slugs = $options['tables']; }

$start_date = trim(mysql_real_escape_string(strip_tags($start_date)));
if (strlen($start_date) == 10) { $start_date .= ' 00:00:00'; }
$end_date = trim(mysql_real_escape_string(strip_tags($end_date)));
if (strlen($end_date) == 10) { $end_date .= ' 23:59:59'; }
$_GET['date_criteria'] = str_replace(' ', '%20', '&amp;start_date='.$start_date.'&amp;end_date='.$end_date);
$date_criteria = "(date BETWEEN '$start_date' AND '$end_date')";

if (($options) && (contact_manager_user_can($back_office_options, 'manage'))) {
$options = array(
'displayed_tables' => $displayed_tables,
'filterby' => $filterby,
'start_date' => $start_date,
'tables' => $tables_slugs);
update_option('contact_manager_statistics', $options); }

$_GET['filter_criteria'] = ''; $filter_criteria = '';
if ((isset($_GET['s'])) && ($_GET['s'] != '')) {
$_GET['filter_criteria'] = str_replace(' ', '%20', '&amp;'.$filterby.'='.$_GET['s']);
$filter_criteria = (is_numeric($_GET['s']) ? "AND (".$filterby." = ".$_GET['s'].")" : "AND (".$filterby." = '".$_GET['s']."')"); }

$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE $date_criteria $selection_criteria $filter_criteria", OBJECT);
$messages_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms WHERE $date_criteria $selection_criteria $filter_criteria", OBJECT);
$forms_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE $date_criteria $selection_criteria $filter_criteria", OBJECT);
$forms_categories_number = (int) (isset($row->total) ? $row->total : 0);

$_GET['criteria'] = $_GET['date_criteria'].$_GET['selection_criteria'].$_GET['filter_criteria'];

$messages_a_tag = '<a style="text-decoration: none;" href="admin.php?page=contact-manager-messages'.$_GET['criteria'].'">';
$forms_a_tag = '<a style="text-decoration: none;" href="admin.php?page=contact-manager-forms'.$_GET['criteria'].'">';
$forms_categories_a_tag = '<a style="text-decoration: none;" href="admin.php?page=contact-manager-forms-categories'.$_GET['criteria'].'">'; ?>

<div class="wrap">
<div id="poststuff">
<?php contact_manager_pages_top($back_office_options); ?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php contact_manager_pages_search_field('filter', $filterby, $filterby_options); ?>
<?php contact_manager_pages_date_picker($start_date, $end_date); ?>
<?php if (count($undisplayed_rows) < count($statistics_rows)) {
$global_table_ths = '';
foreach ($statistics_columns as $key => $value) {
if (!in_array($key, $undisplayed_columns)) { $global_table_ths .= '<th scope="col" class="manage-column" style="width: '.$value['width'].'%;">'.$value['name'].'</th>'; } }
echo '
<h3 id="global-statistics"><strong>'.__('Global statistics', 'contact-manager').'</strong></h3>
<table class="wp-list-table widefat fixed" style="margin: 1em 0 2em 0;">
<thead><tr>'.$global_table_ths.'</tr></thead>
<tfoot><tr>'.$global_table_ths.'</tr></tfoot>
<tbody>';
$boolean = false;
if (!in_array('messages', $undisplayed_rows)) { echo '
<tr'.($boolean ? '' : ' class="alternate"').'>
<td><strong>'.$statistics_rows['messages']['name'].'</strong></td>
'.(in_array('quantity', $undisplayed_columns) ? '' : '<td>'.$messages_a_tag.$messages_number.'</a></td>').'
</tr>'; $boolean = !$boolean; }
if (!in_array('forms', $undisplayed_rows)) { echo '
<tr'.($boolean ? '' : ' class="alternate"').'>
<td><strong>'.$statistics_rows['forms']['name'].'</strong></td>
'.(in_array('quantity', $undisplayed_columns) ? '' : '<td>'.$forms_a_tag.$forms_number.'</a></td>').'
</tr>'; $boolean = !$boolean; }
if (!in_array('forms_categories', $undisplayed_rows)) { echo '
<tr'.($boolean ? '' : ' class="alternate"').'>
<td><strong>'.$statistics_rows['forms_categories']['name'].'</strong></td>
'.(in_array('quantity', $undisplayed_columns) ? '' : '<td>'.$forms_categories_a_tag.$forms_categories_number.'</a></td>').'
</tr>'; $boolean = !$boolean; }
echo '</tbody></table>'; } ?>
<div style="text-align: center;">
<?php for ($i = 0; $i < $max_tables; $i++) {
echo '<label>'.__('Table', 'contact-manager').' '.($i + 1).' <select name="table'.$i.'" id="table'.$i.'">';
foreach ($tables_names as $key => $value) { echo '<option value="'.$key.'"'.($tables_slugs[$i] == $key ? ' selected="selected"' : '').'>'.$value.'</option>'."\n"; }
echo '</select></label>
<label><input type="checkbox" name="table'.$i.'_displayed" id="table'.$i.'_displayed" value="yes"'.(!in_array($i, $displayed_tables) ? '' : ' checked="checked"').' /> '.__('Display', 'contact-manager').'</label><br />'; } ?><br />
<input type="submit" class="button-secondary" name="submit" value="<?php _e('Update'); ?>" />
</div>
<?php $tables_displayed = array();
foreach ($displayed_tables as $key => $value) {
if (in_array($tables_slugs[$value], $tables_displayed)) { unset($displayed_tables[$key]); }
$tables_displayed[] = $tables_slugs[$value]; }
$summary = '';
if (count($displayed_tables) > 1) {
for ($i = 0; $i < $max_tables; $i++) {
if (in_array($i, $displayed_tables)) { $summary .= '<li>&nbsp;| <a href="#'.str_replace('_', '-', $tables_slugs[$i]).'">'.$tables_names[$tables_slugs[$i]].'</a></li>'; } }
$summary = '<ul class="subsubsub" style="float: none; text-align: center;">
<li>'.substr($summary, 12).'</ul>'; }
for ($i = 0; $i < $max_tables; $i++) {
if (in_array($i, $displayed_tables)) {
$table_slug = $tables_slugs[$i];
$table_name = table_name($table_slug);
$custom_fields = (array) $back_office_options[single_page_slug($table_slug).'_page_custom_fields'];
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = do_shortcode($value); }
asort($custom_fields); foreach ($custom_fields as $key => $value) {
$tables[$table_slug]['custom_field_'.$key] = array('modules' => array('custom-fields'), 'name' => $value, 'width' => 18); }
$options = (array) get_option('contact_manager_'.$table_slug);
$columns = (array) $options['columns'];
$max_columns = count($columns);
for ($k = 0; $k < $max_columns; $k++) {
if (!isset($tables[$table_slug][$columns[$k]])) { $columns[$k] = 'id'; } }
$displayed_columns = (array) $options['displayed_columns'];
$table_ths = '';
for ($j = 0; $j < $max_columns; $j++) { if (in_array($j, $displayed_columns)) { $table_ths .= table_th($tables, $table_slug, $columns[$j]); } }
echo $summary.'
<h3 id="'.str_replace('_', '-', $tables_slugs[$i]).'"><strong>'.$tables_names[$tables_slugs[$i]].'</strong></h3>
<div style="overflow: auto;">
<table class="wp-list-table widefat" style="margin: 1em 0 2em 0;">
<thead><tr>'.$table_ths.'</tr></thead>
<tfoot><tr>'.$table_ths.'</tr></tfoot>
<tbody>';
$boolean = false;
$items = $wpdb->get_results("SELECT * FROM $table_name WHERE $date_criteria $selection_criteria $filter_criteria ORDER BY date DESC", OBJECT);
if ($items) { foreach ($items as $item) {
$table_tds = '';
$first = true; for ($j = 0; $j < $max_columns; $j++) {
if (in_array($j, $displayed_columns)) {
$table_tds .= '<td'.($first ? ' style="height: 6em;"' : '').'>'.table_td($table_slug, $columns[$j], $item).($first ? row_actions($table_slug, $item) : '').'</td>';
$first = false; } }
echo '<tr'.($boolean ? '' : ' class="alternate"').'>'.$table_tds.'</tr>';
$table_tds = ''; $boolean = !$boolean; } }
else { echo '<tr class="no-items"><td class="colspanchange" colspan="'.count($displayed_columns).'">'.no_items($table_slug).'</td></tr>'; }
echo '</tbody></table></div>';
$table_ths = ''; } } ?>
</form>
</div>
</div>