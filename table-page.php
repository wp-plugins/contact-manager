<?php global $wpdb; $error = '';
$back_office_options = (array) get_option('contact_manager_back_office');
$table_slug = str_replace('-', '_', str_replace('contact-manager-', '', $_GET['page']));
include 'tables.php';
include_once 'tables-functions.php';
$options = (array) get_option(str_replace('-', '_', $_GET['page']));
$table_name = table_name($table_slug);
$custom_fields = (array) $back_office_options[single_page_slug($table_slug).'_page_custom_fields'];
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = do_shortcode($value); }
asort($custom_fields); foreach ($custom_fields as $key => $value) {
$tables[$table_slug]['custom_field_'.$key] = array('modules' => array('custom-fields'), 'name' => $value, 'width' => 18); }
$undisplayed_keys = table_undisplayed_keys($tables, $table_slug, $back_office_options);
foreach ($tables[$table_slug] as $key => $value) {
if (!isset($value['name'])) { unset($tables[$table_slug][$key]); }
if ((isset($value['searchby'])) && (!in_array($key, $undisplayed_keys))) { $searchby_options[$key] = $value['searchby']; } }
$max_columns = count($tables[$table_slug]);
if ((!isset($_GET['orderby'])) || ($tables[$table_slug][$_GET['orderby']] == '')) { $_GET['orderby'] = $options['orderby']; }
if (!isset($_GET['order'])) { $_GET['order'] = ''; }
switch ($_GET['order']) { case 'asc': case 'desc': break; default: $_GET['order'] = $options['order']; }

if ((isset($_POST['submit'])) && (check_admin_referer($_GET['page']))) {
foreach ($_POST as $key => $value) {
if (is_string($value)) { $_POST[$key] = stripslashes($value); } }
$_GET['s'] = $_POST['s'];
if (isset($_POST['reset_columns'])) {
include 'initial-options.php';
$columns = $initial_options[$table_slug]['columns'];
$displayed_columns = $initial_options[$table_slug]['displayed_columns']; }
else {
$displayed_columns = array();
for ($i = 0; $i < $max_columns; $i++) {
$columns[$i] = $_POST['column'.$i];
if ((isset($_POST['column'.$i.'_displayed'])) && ($_POST['column'.$i.'_displayed'] == 'yes')) { $displayed_columns[] = $i; } } }
$columns_list_displayed = (isset($_POST['columns_list_displayed']) ? 'yes' : 'no');
$limit = (int) $_POST['limit'];
if ($limit > 1000) { $limit = 1000; }
elseif ($limit < 1) { $limit = $options['limit']; }
$searchby = $_POST['searchby'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date']; }
else {
if (function_exists('date_default_timezone_set')) { date_default_timezone_set('UTC'); }
if (isset($_GET['start_date'])) { $start_date = $_GET['start_date']; }
else { $start_date = $options['start_date']; }
if (isset($_GET['end_date'])) { $end_date = $_GET['end_date']; }
else { $end_date = date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET); }
$columns = (array) $options['columns'];
for ($i = 0; $i < $max_columns; $i++) {
if ((!isset($columns[$i])) || (!isset($tables[$table_slug][$columns[$i]]))) { $columns[$i] = 'id'; } }
$columns_list_displayed = $options['columns_list_displayed'];
$displayed_columns = (array) $options['displayed_columns'];
$limit = $options['limit'];
$searchby = $options['searchby']; }

if ($limit < 1) { $limit = 1; }
if ($start_date == '') { $start_date = $options['start_date']; }
else {
$d = preg_split('#[^0-9]#', $start_date, 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? 1 : 0)); }
$start_date = date('Y-m-d H:i:s', mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0])); }
if ($end_date == '') { $end_date = date('Y-m-d H:i:s', time() + 3600*UTC_OFFSET); }
else {
$d = preg_split('#[^0-9]#', $end_date, 0, PREG_SPLIT_NO_EMPTY);
for ($i = 0; $i < 6; $i++) { $d[$i] = (int) (isset($d[$i]) ? $d[$i] : ($i < 3 ? ($i < 2 ? 1 : 0) : ($i == 3 ? 23 : 59))); }
$end_date = date('Y-m-d H:i:s', mktime($d[3], $d[4], $d[5], $d[1], $d[2], $d[0])); }
$GLOBALS['date_criteria'] = str_replace(' ', '%20', '&amp;start_date='.$start_date.'&amp;end_date='.$end_date);
$date_criteria = "(date BETWEEN '$start_date' AND '$end_date')";

if (($options) && (contact_manager_user_can($back_office_options, 'manage'))) {
$options = array(
'columns' => $columns,
'columns_list_displayed' => $columns_list_displayed,
'displayed_columns' => $displayed_columns,
'limit' => $limit,
'order' => $_GET['order'],
'orderby' => $_GET['orderby'],
'searchby' => $searchby,
'start_date' => $start_date);
update_option('contact_manager_'.$table_slug, $options); }

$GLOBALS['criteria'] = $GLOBALS['date_criteria'].$GLOBALS['selection_criteria'];

$GLOBALS['search_criteria'] = ''; $search_criteria = ''; $search_column = false;
if ((isset($_GET['s'])) && ($_GET['s'] != '')) {
if ($searchby == '') {
foreach ($tables[$table_slug] as $key => $value) {
if (substr($key, 0, 13) != 'custom_field_') { $search_criteria .= " OR ".$key." LIKE '%".$_GET['s']."%'"; } }
$search_criteria = substr($search_criteria, 4); }
else {
$search_column = true; for ($i = 0; $i < $max_columns; $i++) {
if ((in_array($i, $displayed_columns)) && ($searchby == $columns[$i])) { $search_column = false; } }
$search_criteria = $searchby." LIKE '%".$_GET['s']."%'"; }
$GLOBALS['search_criteria'] = str_replace(' ', '%20', '&amp;s='.$_GET['s']);
$search_criteria = 'AND ('.$search_criteria.')';
$GLOBALS['criteria'] .= $GLOBALS['search_criteria']; }

$query = $wpdb->get_row("SELECT count(*) as total FROM $table_name WHERE $date_criteria $selection_criteria $search_criteria", OBJECT);
$n = (int) $query->total;
$_GET['paged'] = (int) (isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1);
if ($_GET['paged'] < 1) { $_GET['paged'] = 1; }
$max_paged = ceil($n/$limit);
if ($max_paged < 1) { $max_paged = 1; }
if ($_GET['paged'] > $max_paged) { $_GET['paged'] = $max_paged; }
$start = ($_GET['paged'] - 1)*$limit;

if ($n > 0) {
switch ($_GET['orderby']) {
case 'id': case 'category_id': case 'date': case 'date_utc': case 'displays_count':
case 'ip_address': case 'maximum_messages_quantity_per_sender': case 'messages_count':
case 'referrer': case 'referring_url': case 'status': case 'user_agent': $sorting_method = 'basic'; break;
default: $sorting_method = 'advanced'; }
if (($table_slug == 'messages') && (substr($_GET['orderby'], 0, 13) != 'custom_field_')) { $sorting_method = 'basic'; }

if ($sorting_method == 'basic') { $items = $wpdb->get_results("SELECT * FROM $table_name WHERE $date_criteria $selection_criteria $search_criteria ORDER BY ".$_GET['orderby']." ".strtoupper($_GET['order'])." LIMIT $start, $limit", OBJECT); }
else {
$items = $wpdb->get_results("SELECT * FROM $table_name WHERE $date_criteria $selection_criteria $search_criteria", OBJECT);
foreach ($items as $item) { $all_datas[$item->id] = $item; $datas[$item->id] = table_data($table_slug, $_GET['orderby'], $item); }
if ($_GET['order'] == 'asc') { asort($datas); } else { arsort($datas); }
$array = array(); foreach ($datas as $key => $value) { $array[] = array('id' => $key, 'data' => $value); }
$ids = array(); for ($i = $start; $i < $start + $limit; $i++) { if (isset($array[$i])) { $ids[] = $array[$i]['id']; } }
$items = array(); foreach ($ids as $id) { $items[] = $all_datas[$id]; }
foreach ($items as $item) { $item->$_GET['orderby'] = $datas[$item->id]; } } } ?>

<div class="wrap">
<div id="poststuff">
<?php contact_manager_pages_top($back_office_options); ?>
<form method="post" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field($_GET['page']); ?>
<?php contact_manager_pages_menu($back_office_options); ?>
<?php contact_manager_pages_search_field('search', $searchby, $searchby_options); ?>
<?php contact_manager_pages_date_picker($start_date, $end_date); ?>
<div class="tablenav top">
<div class="alignleft actions">
<?php _e('Display', 'contact-manager'); ?> <input style="text-align: center;" type="text" name="limit" id="limit" size="2" value="<?php echo $limit; ?>" /> 
<?php _e('results per page', 'contact-manager'); ?> <input type="submit" class="button-secondary" name="submit" value="<?php _e('Update'); ?>" />
</div><?php tablenav_pages($table_slug, $n, $max_paged, 'top'); ?></div>
<div style="overflow: auto;">
<table class="wp-list-table widefat">
<?php if ($search_column) { $search_table_th = table_th($tables, $table_slug, $searchby); $table_ths = $search_table_th; } else { $table_ths = ''; }
$columns_displayed = array();
$original_displayed_columns = $displayed_columns;
foreach ($displayed_columns as $key => $value) {
if (in_array($columns[$value], $columns_displayed)) { unset($displayed_columns[$key]); }
$columns_displayed[] = $columns[$value]; }
for ($i = 0; $i < $max_columns; $i++) { if (in_array($i, $displayed_columns)) { $table_ths .= table_th($tables, $table_slug, $columns[$i]); } }
if ($table_ths != '') { echo '<thead><tr>'.$table_ths.'</tr></thead><tfoot><tr>'.$table_ths.'</tr></tfoot>'; } ?>
<tbody id="the-list">
<?php $boolean = false; if ($n > 0) { foreach ($items as $item) {
$table_tds = '';
if ($search_column) { $search_table_td = '<td>'.table_td($table_slug, $searchby, $item).'</td>'; } else { $search_table_td = ''; }
$first = true; for ($i = 0; $i < $max_columns; $i++) {
if (in_array($i, $displayed_columns)) {
$table_tds .= '<td'.($first ? ' style="height: 6em;"' : '').'>'.table_td($table_slug, $columns[$i], $item).($first ? row_actions($table_slug, $item) : '').'</td>';
$first = false; } }
echo '<tr'.($boolean ? '' : ' class="alternate"').'>'.$search_table_td.$table_tds.'</tr>';
$table_tds = ''; $boolean = !$boolean; } }
else { echo '<tr class="no-items"><td class="colspanchange" colspan="'.count($displayed_columns).'">'.no_items($table_slug).'</td></tr>'; } ?>
</tbody>
</table>
</div>
<div class="tablenav bottom">
<?php tablenav_pages($table_slug, $n, $max_paged, 'bottom'); ?>
<div class="alignleft actions">
<input type="hidden" name="submit" value="true" />
<?php $displayed_columns = $original_displayed_columns;
$all_columns_checked = (count($displayed_columns) == $max_columns);
$columns_inputs = '<input style="margin-bottom: 0.5em;" type="submit" class="button-secondary" name="reset_columns" value="'.__('Reset the columns', 'contact-manager').'" />
<input style="margin-bottom: 0.5em; margin-right: 0.5em;" type="submit" class="button-secondary" name="submit" value="'.__('Update').'" />
<label><input type="checkbox" name="check_all_columns1" id="check_all_columns1" value="yes" onclick="check_all_columns1_js();"'.($all_columns_checked ? ' checked="checked"' : '').' /> <span id="check_all_columns1_text">'.($all_columns_checked ? __('Uncheck all columns', 'contact-manager') : __('Check all columns', 'contact-manager')).'</span></label>';
echo $columns_inputs.' <label><input type="checkbox" name="columns_list_displayed" id="columns_list_displayed" value="yes" 
onclick="if (this.checked == true) { document.getElementById(\'columns-list\').style.display = \'block\'; } else { document.getElementById(\'columns-list\').style.display = \'none\'; }"
'.($columns_list_displayed == 'yes' ? ' checked="checked"' : '').' /> '.__('Display the columns list', 'contact-manager').'</label>'; ?><br />
<span id="columns-list"<?php if ($columns_list_displayed == 'no') { echo ' style="display: none;"'; } ?>>
<?php $j = 0; for ($i = 0; $i < $max_columns; $i++) {
if (!isset($columns[$i])) { $columns[$i] = 'id'; }
if ((in_array($columns[$i], $undisplayed_keys)) && (!in_array($i, $displayed_columns))) {
echo '<input type="hidden" name="column'.$i.'" id="column'.$i.'" value="'.$columns[$i].'" />
<input type="hidden" name="column'.$i.'_displayed" id="column'.$i.'_displayed" value="no" />'; }
else {
$j = $j + 1; if ($j < 10) { $space = '&nbsp;&nbsp;&nbsp;&nbsp;'; } elseif ($j < 100) { $space = '&nbsp;&nbsp;'; } else { $space = ''; }
echo '<label>'.__('Column', 'contact-manager').' '.$j.$space.' <select style="float: none;" name="column'.$i.'" id="column'.$i.'">';
foreach ($tables[$table_slug] as $key => $value) {
if ((!in_array($key, $undisplayed_keys)) || ($columns[$i] == $key)) { echo '<option value="'.$key.'"'.($columns[$i] == $key ? ' selected="selected"' : '').'>'.$value['name'].'</option>'."\n"; } }
echo '</select></label>
<label><input type="checkbox" name="column'.$i.'_displayed" id="column'.$i.'_displayed" value="yes"'.(!in_array($i, $displayed_columns) ? '' : ' checked="checked"').' /> '.__('Display', 'contact-manager').'</label><br />'; } } ?>
<?php echo str_replace(array('check_all_columns1', 'margin-bottom'), array('check_all_columns2', 'margin-top'), $columns_inputs); ?></span>
</div></div>
</form>
</div>
</div>

<script type="text/javascript">
function check_all_columns_js() {
if (document.getElementById('check_all_columns1').checked == true) {
for (i = 1; i <= 2; i++) { document.getElementById('check_all_columns'+i+'_text').innerHTML = '<?php _e('Uncheck all columns', 'contact-manager'); ?>'; }
for (i = 0; i < <?php echo $max_columns; ?>; i++) { document.getElementById('column'+i+'_displayed').checked = true; } }
else {
for (i = 1; i <= 2; i++) { document.getElementById('check_all_columns'+i+'_text').innerHTML = '<?php _e('Check all columns', 'contact-manager'); ?>'; }
for (i = 0; i < <?php echo $max_columns; ?>; i++) { document.getElementById('column'+i+'_displayed').checked = false; } } }

function check_all_columns1_js() {
if (document.getElementById('check_all_columns1').checked == true) {
document.getElementById('check_all_columns2').checked = true; }
else { document.getElementById('check_all_columns2').checked = false; }
check_all_columns_js(); }

function check_all_columns2_js() {
if (document.getElementById('check_all_columns2').checked == true) {
document.getElementById('check_all_columns1').checked = true; }
else { document.getElementById('check_all_columns1').checked = false; }
check_all_columns_js(); }
</script>