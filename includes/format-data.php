<?php $data = do_shortcode($data);
if ($field != 'code') { $data = quotes_entities_decode($data); }
if (($data == '0000-00-00 00:00:00') && ((substr($field, -4) == 'date') || (substr($field, -8) == 'date_utc'))) { $data = ''; }
elseif (substr($field, -13) == 'email_address') { $data = format_email_address($data); }
elseif (substr($field, -19) == 'custom_instructions') { $data = format_instructions($data); }
elseif (($field == 'url') || (substr($field, -4) == '_url')
 || ((is_numeric(substr($field, -1))) && (substr($field, -5, -1) == '_url'))) { $data = format_url($data); }
switch ($field) {
case 'id': $data = (int) $data; break;
case 'automatic_display_maximum_forms_quantity': case 'maximum_messages_quantity': if ($data != 'unlimited') { $data = (int) $data; } break;
case 'maximum_messages_quantity_per_sender': if ($data != 'unlimited') { $data = (int) $data; } if ($data == 0) { $data = 'unlimited'; } break;
case 'encrypted_urls_validity_duration': $data = round($data, 2); break;
case 'commission_amount': case 'commission2_amount': $data = contact_decimals_data('0/2', round($data, 2)); }