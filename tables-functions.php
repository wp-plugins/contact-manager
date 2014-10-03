<?php global $wpdb;

foreach ($_POST as $key => $value) { if (is_string($value)) { $_POST[$key] = quotes_entities($_POST[$key]); } }
$GLOBALS['selection_criteria'] = ''; $selection_criteria = '';
$all_tables_keys = all_tables_keys($tables);
foreach ($_GET as $key => $value) {
if ((in_array($key, $all_tables_keys)) || ((substr($key, 0, 13) == 'custom_field_') && ($key == str_replace('-', '_', format_nice_name($key))))) {
$GLOBALS['selection_criteria'] .= '&amp;'.$key.'='.str_replace('+', '%20', urlencode($value));
if (substr($key, 0, 13) != 'custom_field_') {
$selection_criteria .= ($key == "keywords" ? " AND (".$key." LIKE '%".$value."%')" :
 (is_numeric($value) ? " AND (".$key." = ".$value.")" : " AND (".$key." = '".$value."')")); }
else { $selection_criteria .= " AND (custom_fields LIKE '%s:".(strlen($key) - 13).":\"".substr($key, 13)."\";s:".strlen($value).":\"".$value."\";%')"; } } }
$selection_criteria = str_replace("= '!0'", "!= 0", $selection_criteria);


function all_tables_keys($tables) {
$keys = array();
foreach ($tables as $table_slug => $table) {
foreach ($table as $key => $value) {
if (!in_array($key, $keys)) { $keys[] = $key; } } }
return $keys; }


function no_items($table) {
switch ($table) {
case 'forms': $no_items = __('No forms', 'contact-manager'); break;
case 'forms_categories': $no_items = __('No categories', 'contact-manager'); break;
case 'messages': $no_items = __('No messages', 'contact-manager'); }
return $no_items; }


function row_actions($table, $item) {
global $wpdb;
switch ($table) {
case 'forms':
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_messages WHERE form_id = ".$item->id, OBJECT);
$messages_number = (int) (isset($row->total) ? $row->total : 0);
$row_actions =
'<span class="edit"><a href="admin.php?page=contact-manager-form&amp;id='.$item->id.'">'.__('Edit', 'contact-manager').'</a></span>
 | <span class="delete"><a href="admin.php?page=contact-manager-form&amp;id='.$item->id.'&amp;action=delete">'.__('Delete', 'contact-manager').'</a></span>
 | <span class="view"><a href="admin.php?page=contact-manager-statistics&amp;form_id='.$item->id.'">'.__('Statistics', 'contact-manager').'</a></span>'
.($messages_number == 0 ? '' : ' | <span class="view"><a href="admin.php?page=contact-manager-messages&amp;form_id='.$item->id.'&amp;start_date=0">'.__('Messages', 'contact-manager').' <span class="count">('.$messages_number.')</span></a></span>'); break;
case 'forms_categories':
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms WHERE category_id = ".$item->id, OBJECT);
$forms_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE category_id = ".$item->id, OBJECT);
$categories_number = (int) (isset($row->total) ? $row->total : 0);
$row_actions = 
'<span class="edit"><a href="admin.php?page=contact-manager-form-category&amp;id='.$item->id.'">'.__('Edit', 'contact-manager').'</a></span>
 | <span class="delete"><a href="admin.php?page=contact-manager-form-category&amp;id='.$item->id.'&amp;action=delete">'.__('Delete', 'contact-manager').'</a></span>'
.($forms_number == 0 ? '' : ' | <span class="view"><a href="admin.php?page=contact-manager-forms&amp;category_id='.$item->id.'&amp;start_date=0">'.__('Forms', 'contact-manager').' <span class="count">('.$forms_number.')</span></a></span>')
.($categories_number == 0 ? '' : ' | <span class="view"><a href="admin.php?page=contact-manager-forms-categories&amp;category_id='.$item->id.'&amp;start_date=0">'.__('Subcategories', 'contact-manager').' <span class="count">('.$categories_number.')</span></a></span>'); break;
case 'messages': $row_actions = 
'<span class="edit"><a href="admin.php?page=contact-manager-message&amp;id='.$item->id.'">'.__('Edit', 'contact-manager').'</a></span>
 | <span class="delete"><a href="admin.php?page=contact-manager-message&amp;id='.$item->id.'&amp;action=delete">'.__('Delete', 'contact-manager').'</a></span>'; break; }
return '<div class="row-actions" style="margin-top: 3em; position: absolute;">'.$row_actions.'</div>'; }


function single_page_slug($table) {
switch ($table) {
case 'forms_categories': $page = 'form_category'; break;
default: $page = substr($table, 0, -1); }
return $page; }


function table_name($table) {
global $wpdb;
return $wpdb->prefix.'contact_manager_'.$table; }


function table_undisplayed_keys($tables, $table, $back_office_options) {
global $wpdb;
$undisplayed_modules = (array) $back_office_options[single_page_slug($table).'_page_undisplayed_modules'];
$undisplayed_keys = array();
switch ($table) {
case 'forms': case 'forms_categories':
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms_categories", OBJECT);
$n = (int) (isset($row->total) ? $row->total : 0); if ($n == 0) { $undisplayed_keys[] = 'category_id'; } break; }
foreach ($tables[$table] as $key => $value) {
foreach ((array) $value['modules'] as $module) {
if (in_array($module, $undisplayed_modules)) { $undisplayed_keys[] = $key; } } }
return $undisplayed_keys; }


function table_data($table, $column, $item) {
switch ($table) {
case 'forms': $GLOBALS['contact_form_id'] = $item->id; $GLOBALS['contact_form_data'] = (array) $item; $data = contact_form_data($column); break;
case 'forms_categories': $GLOBALS['contact_form_category_id'] = $item->id; $GLOBALS['contact_form_category_data'] = (array) $item; $data = contact_form_category_data($column); break;
case 'messages': $GLOBALS['message_id'] = $item->id; $GLOBALS['message_data'] = (array) $item; $data = message_data($column); break;
default: $data = contact_format_data($column, $item->$column); }
if (strstr($table, 'forms')) {
if (in_array($column, array('sender_member_category_id', 'sender_member_status', 'membership_registration_confirmation_email_sent', 'membership_registration_notification_email_sent'))) {
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', contact_item_data(($table == 'forms' ? 'contact_form' : 'contact_form_category'), 'sender_members_areas'), 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 1) { $GLOBALS['member_area_id'] = (int) $members_areas[0]; }
else { $GLOBALS['member_area_id'] = 0; $GLOBALS['member_area_data'] = array(); } }
foreach (array('category_id', 'status') as $field) {
if (($column == 'sender_client_'.$field) && ($data == '') && (function_exists('commerce_data'))) { $data = commerce_data('clients_initial_'.$field); }
if (($column == 'sender_affiliate_'.$field) && ($data == '') && (function_exists('affiliation_data'))) { $data = affiliation_data('affiliates_initial_'.$field); }
if (($column == 'sender_member_'.$field) && ($data == '') && (function_exists('member_area_data'))) { $data = member_area_data('members_initial_'.$field); } }
foreach (array('confirmation', 'notification') as $action) {
if (($column == 'commerce_registration_'.$action.'_email_sent') && ($data == '') && (function_exists('commerce_data'))) { $data = commerce_data('registration_'.$action.'_email_sent'); }
if (($column == 'affiliation_registration_'.$action.'_email_sent') && ($data == '') && (function_exists('affiliation_data'))) { $data = affiliation_data('registration_'.$action.'_email_sent'); }
if (($column == 'membership_registration_'.$action.'_email_sent') && ($data == '') && (function_exists('member_area_data'))) { $data = member_area_data('registration_'.$action.'_email_sent'); } }
if (substr($column, -11) == 'category_id') { $data = (int) $data; } }
return $data; }


function table_td($table, $column, $item) {
$data = htmlspecialchars(table_data($table, $column, $item));
if (substr($column, 0, 13) == 'custom_field_') { $table_td = ($data == '' ? '' : '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.str_replace('+', '%20', urlencode(html_entity_decode($data))).'">'.contact_excerpt($data, 50).'</a>'); }
else {
switch ($column) {
case 'affiliation_enabled': case 'affiliation_registration_confirmation_email_sent': case 'affiliation_registration_notification_email_sent':
case 'commerce_registration_confirmation_email_sent': case 'commerce_registration_notification_email_sent': case 'commission2_enabled':
case 'membership_registration_confirmation_email_sent': case 'membership_registration_notification_email_sent':
case 'message_confirmation_email_sent': case 'message_custom_instructions_executed': case 'message_notification_email_sent': case 'messages_registration_enabled':
case 'sender_subscribed_as_a_client': case 'sender_subscribed_as_a_user': case 'sender_subscribed_to_affiliate_program': case 'sender_subscribed_to_autoresponder': case 'sender_subscribed_to_members_areas': 
if ($data == 'yes') { $table_td = '<span style="color: #008000;">'.__('Yes', 'contact-manager').'</span>'; }
elseif ($data == 'no') { $table_td = '<span style="color: #c00000;">'.__('No', 'contact-manager').'</span>'; }
else { $table_td = contact_excerpt($data, 50); } break;
case 'category_id': $description = ($data == 0 ? __('No category', 'contact-manager') : htmlspecialchars(contact_excerpt(contact_form_category_data(array(0 => 'name', 'id' => $data)), 50)));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.$data.'">'.$data.$description.'</a>';
if ($data > 0) { $table_td .= ' <span class="row-actions edit"><a href="admin.php?page=contact-manager-form-category&amp;id='.$data.'">'.__('Edit', 'contact-manager').'</a></span>'; } break;
case 'commission_amount': case 'commission2_amount':
if ($table == 'messages') { $table_td = ($data == '' ? '' : '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.$data.'">'.$data.'</a>'); break; }
else { $table_td = $data; } break;
case 'commission_status': case 'commission2_status': if ($data == 'paid') { $table_td = '<a style="color: #008000;" href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'=paid">'.__('Paid', 'contact-manager').'</a>'; }
elseif ($data == 'unpaid') { $table_td = '<a style="color: #e08000;" href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'=unpaid">'.__('Unpaid', 'contact-manager').'</a>'; }
else { $table_td = contact_excerpt($data, 50); }
if ($table_td != '') { $table_td .= ' <span class="row-actions edit"><a href="admin.php?page=contact-manager-message&amp;id='.$item->id.'#'.($column == 'commission_status' ? 'level-1-commission' : 'level-2-commission').'">'.__('Change', 'contact-manager').'</a></span>'; } break;
case 'custom_fields':
$back_office_options = (array) get_option('contact_manager_back_office');
$custom_fields = (array) $back_office_options[single_page_slug($table).'_page_custom_fields'];
$item_custom_fields = (array) unserialize(htmlspecialchars_decode($data));
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = do_shortcode($value); }
asort($custom_fields); $table_td = '';
foreach ($custom_fields as $key => $value) {
if ((isset($item_custom_fields[$key])) && ($item_custom_fields[$key] != '')) { $table_td .= htmlspecialchars($value).' => '.htmlspecialchars($item_custom_fields[$key]).',<br />'; } } break;
case 'email_address': $table_td = '<a href="mailto:'.$data.'">'.contact_excerpt($data, 50).'</a>'; break;
case 'form_id': $description = ($data == 0 ? __('No form', 'contact-manager') : htmlspecialchars(contact_excerpt(contact_form_data(array(0 => 'name', 'id' => $data)), 50)));
$result = ((isset($GLOBALS['contact_form'.$data.'_data'])) && (isset($GLOBALS['contact_form'.$data.'_data']['id'])));
if ((!$result) && ($data > 0)) { $description = __('Inexistent or deleted form', 'contact-manager'); }
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.$data.'">'.$data.$description.'</a>';
if ($data > 0) { $table_td .= ' <span class="row-actions">'.($result ? '<span class="edit"><a href="admin.php?page=contact-manager-form&amp;id='.$data.'">'.__('Edit', 'contact-manager').'</a></span> | ' : '')
.'<span class="view"><a href="admin.php?page=contact-manager-statistics&amp;form_id='.$data.'">'.__('Statistics', 'contact-manager').'</a></span></span>'; } break;
case 'gift_download_url': case 'referring_url': case 'website_url': $table_td = ($data == '' ? '' : '<a href="'.$data.'">'.($data == ROOT_URL ? '/' : contact_excerpt(str_replace(ROOT_URL, '', $data), 80)).'</a>'); break;
case 'ip_address': $table_td = ($data == '' ? '' : '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.str_replace('+', '%20', urlencode(html_entity_decode($data))).'">'.contact_excerpt($data, 50).'</a>'); break;
case 'keywords':
$keywords = explode(',', $data);
$keywords_list = '';
foreach ($keywords as $keyword) {
$keyword = strtolower(trim($keyword));
$keyword = '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;keywords='.str_replace('+', '%20', urlencode(html_entity_decode($keyword))).'">'.$keyword.'</a>';
$keywords_list .= $keyword.', '; }
$table_td = (string) substr($keywords_list, 0, -2); break;
case 'maximum_messages_quantity': case 'maximum_messages_quantity_per_sender': if ($data === 'unlimited') { $table_td = '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'=unlimited">'.__('Unlimited', 'contact-manager').'</a>'; } else { $table_td = ($data == '' ? '' : '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.$data.'">'.$data.'</a>'); } break;
case 'messages_count': $table_td = ($data == 0 ? 0 : '<a href="admin.php?page=contact-manager-messages&amp;form_id='.$item->id.'&amp;start_date=0">'.$data.'</a>'); break;
case 'referrer': case 'referrer2': if ($data != '') { $table_td = '<a style="margin-right: 1em;" href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.$data.'">'.contact_excerpt($data, 50).'</a>';
if (function_exists('affiliation_data')) {
if (strstr($data, '@')) { $result = false; } else { global $wpdb; $result = $wpdb->get_row("SELECT id FROM ".$wpdb->prefix."affiliation_manager_affiliates WHERE login = '".$data."'", OBJECT); }
$table_td .= ' <span class="row-actions">'.($result ? '<span class="edit"><a href="admin.php?page=affiliation-manager-affiliate&amp;id='.$result->id.'">'.__('Edit', 'contact-manager').'</a></span> | ' : '')
.'<span class="view"><a href="admin.php?page=affiliation-manager-statistics&amp;referrer='.$data.'">'.__('Statistics', 'contact-manager').'</a></span></span>'; } }
else { $table_td = ''; } break;
case 'sender_affiliate_status': case 'sender_client_status': case 'sender_member_status':
if ($data == 'active') { $table_td = '<span style="color: #008000;">'.__('Active', 'contact-manager').'</span>'; }
elseif ($data == 'inactive') { $table_td = '<span style="color: #e08000;">'.__('Inactive', 'contact-manager').'</span>'; }
else { $table_td = contact_excerpt($data, 50); } break;
case 'sender_affiliate_category_id': $description = ($data == 0 ? __('No category', 'contact-manager') : (function_exists('affiliate_category_data') ? htmlspecialchars(contact_excerpt(affiliate_category_data(array(0 => 'name', 'id' => $data)), 50)) : ''));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = $data.$description;
if ((function_exists('affiliation_data')) && ($data > 0)) { $table_td .= ' <span class="row-actions edit"><a href="admin.php?page=affiliation-manager-affiliate-category&amp;id='.$data.'">'.__('Edit', 'contact-manager').'</a></span>'; } break;
case 'sender_client_category_id': $description = ($data == 0 ? __('No category', 'contact-manager') : (function_exists('client_category_data') ? htmlspecialchars(contact_excerpt(client_category_data(array(0 => 'name', 'id' => $data)), 50)) : ''));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = $data.$description;
if ((function_exists('commerce_data')) && ($data > 0)) { $table_td .= ' <span class="row-actions edit"><a href="admin.php?page=commerce-manager-client-category&amp;id='.$data.'">'.__('Edit', 'contact-manager').'</a></span>'; } break;
case 'sender_member_category_id': $description = ($data == 0 ? __('No category', 'contact-manager') : (function_exists('member_category_data') ? htmlspecialchars(contact_excerpt(member_category_data(array(0 => 'name', 'id' => $data)), 50)) : ''));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = $data.$description;
if ((function_exists('membership_data')) && ($data > 0)) { $table_td .= ' <span class="row-actions edit"><a href="admin.php?page=membership-manager-member-category&amp;id='.$data.'">'.__('Edit', 'contact-manager').'</a></span>'; } break;
case 'sender_members_areas':
$members_areas = array_unique(array_map('intval', preg_split('#[^0-9]#', $data, 0, PREG_SPLIT_NO_EMPTY)));
if (count($members_areas) == 0) { $table_td = __('None', 'contact-manager'); }
elseif (count($members_areas) == 1) {
$description = ($data == 0 ? __('All members areas', 'contact-manager') : (function_exists('member_area_data') ? htmlspecialchars(contact_excerpt(member_area_data(array(0 => 'name', 'id' => $data)), 50)) : ''));
if ((function_exists('member_area_data')) && ($data > 0) && ((!isset($GLOBALS['member_area'.$data.'_data'])) || (!isset($GLOBALS['member_area'.$data.'_data']['id'])))) { $description = __('Inexistent or deleted member area', 'contact-manager'); }
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = $data.$description;
if ((function_exists('member_area_data')) && ($data > 0) && (isset($GLOBALS['member_area'.$data.'_data'])) && (isset($GLOBALS['member_area'.$data.'_data']['id']))) { $table_td = '<a href="admin.php?page=membership-manager-member-area&amp;id='.$data.'">'.$table_td.'</a>'; } }
else {
$members_areas_list = '';
foreach ($members_areas as $member_area) {
if ((function_exists('membership_data')) && ($member_area > 0)) { $member_area = '<a href="admin.php?page=membership-manager-member-area&amp;id='.$member_area.'">'.$member_area.'</a>'; }
$members_areas_list .= $member_area.', '; }
$table_td = (string) substr($members_areas_list, 0, -2); } break;
case 'sender_user_role': $roles = contact_manager_users_roles(); $table_td = contact_excerpt($roles[$data], 50); break;
case 'website_name': $website_url = htmlspecialchars(table_data($table, 'website_url', $item)); $table_td = ($website_url == '' ? contact_excerpt($data, 50) : '<a href="'.$website_url.'">'.contact_excerpt(($data == '' ? str_replace(ROOT_URL, '', $website_url) : $data), 50).'</a>'); break;
default: $table_td = contact_excerpt($data); } }
return $table_td; }


function table_th($tables, $table, $column) {
if (strstr($_GET['page'], 'statistics')) { $table_th = '<th scope="col" class="manage-column" style="width: '.$tables[$table][$column]['width'].'%;">'.$tables[$table][$column]['name'].'</th>'; }
else {
$reverse_order = ($_GET['order'] == 'asc' ? 'desc' : 'asc');
$table_th = '<th scope="col" class="manage-column '.($_GET['orderby'] == $column ? 'sorted '.$_GET['order'] : 'sortable '.$reverse_order).'" style="width: '.$tables[$table][$column]['width'].'%;">
<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;orderby='.$column.'&amp;order='.($_GET['orderby'] == $column ? $reverse_order : $_GET['order']).'">
<span>'.$tables[$table][$column]['name'].'</span><span class="sorting-indicator"></span></a></th>'; }
return $table_th; }


function tablenav_pages($table, $n, $max_paged, $location) {
switch ($table) {
case 'forms': $singular = __('form', 'contact-manager'); $plural = __('forms', 'contact-manager'); break;
case 'forms_categories': $singular = __('form category', 'contact-manager'); $plural = __('forms categories', 'contact-manager'); break;
case 'messages': $singular = __('message', 'contact-manager'); $plural = __('messages', 'contact-manager'); break;
default: $singular = __('item', 'contact-manager'); $plural = __('items', 'contact-manager'); }
if ($_GET['paged'] == 1) { $prev_paged = 1; } else { $prev_paged = $_GET['paged'] - 1; }
if ($_GET['paged'] == $max_paged) { $next_paged = $max_paged; } else { $next_paged = $_GET['paged'] + 1; }
$url = 'admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;orderby='.$_GET['orderby'].'&amp;order='.$_GET['order'];
echo '<div class="tablenav-pages" style="float: right;"><span class="displaying-num">'.$n.' '.($n <= 1 ? $singular : $plural).'</span>
<a class="first-page'.($_GET['paged'] == 1 ? ' disabled' : '').'" title="'.__('Go to the first page', 'contact-manager').'" href="'.$url.'&amp;paged=1">&laquo;</a>
<a class="prev-page'.($_GET['paged'] == 1 ? ' disabled' : '').'" title="'.__('Go to the previous page', 'contact-manager').'" href="'.$url.'&amp;paged='.$prev_paged.'">&lsaquo;</a>
<span class="paging-input" id="paging-input-'.$location.'">'.($location == 'top' ? '<input type="hidden" name="old_paged" value="'.$_GET['paged'].'" /><input class="current-page" title="'.__('Current page', 'contact-manager').'" type="text" name="paged" id="paged" value="'.$_GET['paged'].'" size="2" onfocus="this.value = \'\';" onblur="if (this.value == \'\') { this.value = this.form.old_paged.value; }" onchange="this.value = this.value.replace(/[^0-9]/g, \'\'); if ((this.value == \'\') || (this.value == 0)) { this.value = this.form.old_paged.value; } if (this.value > '.$max_paged.') { this.value = '.$max_paged.'; } if (this.value != this.form.old_paged.value) { window.location = \''.$url.'&amp;paged=\'+this.value; }" />' : $_GET['paged']).'</span> '.__('of', 'contact-manager').' <span class="total-pages">'.$max_paged.'</span>
<a class="next-page'.($_GET['paged'] == $max_paged ? ' disabled' : '').'" title="'.__('Go to the next page', 'contact-manager').'" href="'.$url.'&amp;paged='.$next_paged.'">&rsaquo;</a>
<a class="last-page'.($_GET['paged'] == $max_paged ? ' disabled' : '').'" title="'.__('Go to the last page', 'contact-manager').'" href="'.$url.'&amp;paged='.$max_paged.'">&raquo;</a></div>'; }


foreach (array(
'affiliate',
'affiliation-activation-url',
'affiliation-user',
'click',
'client',
'commerce-activation-url',
'commerce-user',
'commission',
'customer',
'member',
'membership-activation-url',
'membership-user',
'message',
'message-commission',
'order',
'order-invoice-url',
'prospect',
'prospect-commission',
'purchase-url',
'recurring-commission',
'recurring-payment',
'recurring-payment-invoice-url',
'referrer',
'referrer-affiliate',
'sender',
'user') as $tag) { remove_shortcode($tag); }