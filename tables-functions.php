<?php global $wpdb;

$_POST = array_map('quotes_entities', $_POST);
$GLOBALS['selection_criteria'] = ''; $selection_criteria = '';
foreach (array(
'category_id',
'commission_amount',
'commission_status',
'commission2_amount',
'commission2_status',
'form_id',
'ip_address',
'keywords',
'maximum_messages_quantity',
'maximum_messages_quantity_per_sender',
'referrer',
'referrer2') as $field) {
if (isset($_GET[$field])) {
$GLOBALS['selection_criteria'] .= '&amp;'.$field.'='.str_replace(' ', '%20', $_GET[$field]);
$selection_criteria .= ($field == "keywords" ? " AND (".$field." LIKE '%".$_GET[$field]."%')" :
 (is_numeric($_GET[$field]) ? " AND (".$field." = ".$_GET[$field].")" : " AND (".$field." = '".$_GET[$field]."')")); } }
$selection_criteria = str_replace("= '!0'", "!= 0", $selection_criteria);


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
 | <span class="delete"><a href="admin.php?page=contact-manager-form&amp;id='.$item->id.'&amp;action=delete">'.__('Delete', 'contact-manager').'</a></span>'
.($messages_number == 0 ? '' : ' | <span class="view"><a href="admin.php?page=contact-manager-messages&amp;form_id='.$item->id.'">'.__('Messages', 'contact-manager').'</a></span>'); break;
case 'forms_categories':
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms WHERE category_id = ".$item->id, OBJECT);
$forms_number = (int) (isset($row->total) ? $row->total : 0);
$row = $wpdb->get_row("SELECT count(*) as total FROM ".$wpdb->prefix."contact_manager_forms_categories WHERE category_id = ".$item->id, OBJECT);
$categories_number = (int) (isset($row->total) ? $row->total : 0);
$row_actions = 
'<span class="edit"><a href="admin.php?page=contact-manager-form-category&amp;id='.$item->id.'">'.__('Edit', 'contact-manager').'</a></span>
 | <span class="delete"><a href="admin.php?page=contact-manager-form-category&amp;id='.$item->id.'&amp;action=delete">'.__('Delete', 'contact-manager').'</a></span>'
.($forms_number == 0 ? '' : ' | <span class="view"><a href="admin.php?page=contact-manager-forms&amp;category_id='.$item->id.'">'.__('Forms', 'contact-manager').'</a></span>')
.($categories_number == 0 ? '' : ' | <span class="view"><a href="admin.php?page=contact-manager-forms-categories&amp;category_id='.$item->id.'">'.__('Subcategories', 'contact-manager').'</a></span>'); break;
case 'messages': $row_actions = 
'<span class="edit"><a href="admin.php?page=contact-manager-message&amp;id='.$item->id.'">'.__('Edit', 'contact-manager').'</a></span>
 | <span class="delete"><a href="admin.php?page=contact-manager-message&amp;id='.$item->id.'&amp;action=delete">'.__('Delete', 'contact-manager').'</a></span>'; break; }
return '<div class="row-actions" style="margin-top: 2em; position: absolute;">'.$row_actions.'</div>'; }


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
return $data; }


function table_td($table, $column, $item) {
$data = htmlspecialchars(table_data($table, $column, $item));
switch ($column) {
case 'affiliation_enabled': case 'affiliation_registration_confirmation_email_sent': case 'affiliation_registration_notification_email_sent':
case 'commerce_registration_confirmation_email_sent': case 'commerce_registration_notification_email_sent': case 'commission2_enabled':
case 'membership_registration_confirmation_email_sent': case 'membership_registration_notification_email_sent':
case 'message_confirmation_email_sent': case 'message_custom_instructions_executed': case 'message_notification_email_sent': case 'messages_registration_enabled':
case 'sender_subscribed_as_a_client': case 'sender_subscribed_as_a_user': case 'sender_subscribed_to_affiliate_program': case 'sender_subscribed_to_autoresponder': case 'sender_subscribed_to_members_areas': 
if ($data == 'yes') { $table_td = '<span style="color: #008000;">'.__('Yes', 'contact-manager').'</span>'; }
elseif ($data == 'no')  { $table_td = '<span style="color: #c00000;">'.__('No', 'contact-manager').'</span>'; }
else { $table_td = contact_excerpt($data, 50); } break;
case 'category_id': $description = ($data == 0 ? __('No category', 'contact-manager') : htmlspecialchars(contact_excerpt(contact_form_category_data(array(0 => 'name', 'id' => $data)), 50)));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.$data.'">'.$data.$description.'</a>'; break;
case 'form_id': $description = ($data == 0 ? __('No form', 'contact-manager') : htmlspecialchars(contact_excerpt(contact_form_data(array(0 => 'name', 'id' => $data)), 50)));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.$data.'">'.$data.$description.'</a>'; break;
case 'ip_address': case 'referrer': case 'referrer2': $table_td = ($data == '' ? '' : '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.str_replace(' ', '%20', $data).'">'.contact_excerpt($data, 50).'</a>'); break;
case 'commission_amount': case 'commission2_amount':
if ($table == 'messages') { $table_td = ($data == '' ? '' : '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.str_replace(' ', '%20', $data).'">'.$data.'</a>'); break; }
else { $table_td = $data; } break;
case 'commission_status': case 'commission2_status': if ($data == 'paid') { $table_td = '<a style="color: #008000;" href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'=paid">'.__('Paid', 'contact-manager').'</a>'; }
elseif ($data == 'unpaid') { $table_td = '<a style="color: #e08000;" href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'=unpaid">'.__('Unpaid', 'contact-manager').'</a>'; }
else { $table_td = contact_excerpt($data, 50); } break;
case 'custom_fields':
$back_office_options = (array) get_option('contact_manager_back_office');
$custom_fields = (array) $back_office_options[single_page_slug($table).'_page_custom_fields'];
$item_custom_fields = (array) unserialize(htmlspecialchars_decode($data));
foreach ($custom_fields as $key => $value) { $custom_fields[$key] = do_shortcode($value); }
asort($custom_fields); $table_td = '';
foreach ($custom_fields as $key => $value) {
if ((isset($item_custom_fields[$key])) && ($item_custom_fields[$key] != '')) { $table_td .= htmlspecialchars($value).' => '.htmlspecialchars($item_custom_fields[$key]).',<br />'; } } break;
case 'email_address': $table_td = '<a href="mailto:'.$data.'">'.contact_excerpt($data, 50).'</a>'; break;
case 'gift_download_url': case 'referring_url': case 'website_url': $table_td = ($data == '' ? '' : '<a href="'.$data.'">'.($data == ROOT_URL ? '/' : contact_excerpt(str_replace(ROOT_URL, '', $data), 80)).'</a>'); break;
case 'keywords':
$keywords = explode(',', $data);
$keywords_list = '';
foreach ($keywords as $keyword) {
$keyword = strtolower(trim($keyword));
$keyword = '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;keywords='.$keyword.'">'.$keyword.'</a>';
$keywords_list .= $keyword.', '; }
$table_td = substr($keywords_list, 0, -2); break;
case 'maximum_messages_quantity': case 'maximum_messages_quantity_per_sender': if ($data == 'unlimited') { $table_td = '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'=unlimited">'.__('Unlimited', 'contact-manager').'</a>'; } else { $table_td = ($data == '' ? '' : '<a href="admin.php?page='.$_GET['page'].$GLOBALS['criteria'].'&amp;'.$column.'='.$data.'">'.$data.'</a>'); } break;
case 'messages_count': $table_td = ($data == 0 ? 0 : '<a href="admin.php?page=contact-manager-messages&amp;form_id='.$item->id.'">'.$data.'</a>'); break;
case 'sender_affiliate_status': case 'sender_client_status': case 'sender_member_status':
if ($data == 'active') { $table_td = '<span style="color: #008000;">'.__('Active', 'contact-manager').'</span>'; }
elseif ($data == 'inactive') { $table_td = '<span style="color: #e08000;">'.__('Inactive', 'contact-manager').'</span>'; }
else { $table_td = contact_excerpt($data, 50); } break;
case 'sender_affiliate_category_id': $description = ($data == 0 ? __('No category', 'contact-manager') : (function_exists('affiliate_category_data') ? htmlspecialchars(contact_excerpt(affiliate_category_data(array(0 => 'name', 'id' => $data)), 50)) : ''));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = $data.$description; break;
case 'sender_client_category_id': $description = ($data == 0 ? __('No category', 'contact-manager') : (function_exists('client_category_data') ? htmlspecialchars(contact_excerpt(client_category_data(array(0 => 'name', 'id' => $data)), 50)) : ''));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = $data.$description; break;
case 'sender_member_category_id': $description = ($data == 0 ? __('No category', 'contact-manager') : (function_exists('member_category_data') ? htmlspecialchars(contact_excerpt(member_category_data(array(0 => 'name', 'id' => $data)), 50)) : ''));
if ($description != '') { $description = ' <span class="description">('.$description.')</span>'; } $table_td = $data.$description; break;
case 'sender_members_areas':
$members_areas = array_unique(preg_split('#[^0-9]#', $data, 0, PREG_SPLIT_NO_EMPTY));
$members_areas_list = '';
foreach ($members_areas as $member_area) {
if ((function_exists('membership_manager_admin_menu')) && ($member_area > 0)) { $member_area = '<a href="admin.php?page=membership-manager-member-area&amp;id='.$member_area.'">'.$member_area.'</a>'; }
$members_areas_list .= $member_area.', '; }
$table_td = substr($members_areas_list, 0, -2); break;
case 'sender_user_role': $roles = contact_manager_users_roles(); $table_td = contact_excerpt($roles[$data], 50); break;
case 'website_name': $website_url = htmlspecialchars(table_data($table, 'website_url', $item)); $table_td = ($website_url == '' ? contact_excerpt($data, 50) : '<a href="'.$website_url.'">'.contact_excerpt(($data == '' ? str_replace(ROOT_URL, '', $website_url) : $data), 50).'</a>'); break;
default: $table_td = contact_excerpt($data); }
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
<a class="first-page'.($_GET['paged'] == 1 ? ' disabled' : '').'" title="'.__('Go to the first page').'" href="'.$url.'&amp;paged=1">&laquo;</a>
<a class="prev-page'.($_GET['paged'] == 1 ? ' disabled' : '').'" title="'.__('Go to the previous page').'" href="'.$url.'&amp;paged='.$prev_paged.'">&lsaquo;</a>
<span class="paging-input">'.($location == 'top' ? '<input class="current-page" title="'.__('Current page').'" type="text" name="paged" id="paged" value="'.$_GET['paged'].'" size="2" />' : $_GET['paged']).' '.__('of').' <span class="total-pages">'.$max_paged.'</span></span>
<a class="next-page'.($_GET['paged'] == $max_paged ? ' disabled' : '').'" title="'.__('Go to the next page').'" href="'.$url.'&amp;paged='.$next_paged.'">&rsaquo;</a>
<a class="last-page'.($_GET['paged'] == $max_paged ? ' disabled' : '').'" title="'.__('Go to the last page').'" href="'.$url.'&amp;paged='.$max_paged.'">&raquo;</a></div>'; }


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