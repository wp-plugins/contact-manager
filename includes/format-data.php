<?php $data = do_shortcode($data);
if ($field != 'code') { $data = quotes_entities_decode($data); }
if ((strstr($field, 'date')) && ($data == '0000-00-00 00:00:00')) { $data = ''; }
elseif (substr($field, -13) == 'email_address') { $data = format_email_address($data); }
elseif (substr($field, -19) == 'custom_instructions') { $data = format_instructions($data); }
elseif ((($field == 'url') || (strstr($field, '_url'))) && (!strstr($field, 'urls'))) { $data = format_url($data); }
switch ($field) {
case 'id': $data = (int) $data; break;
case 'automatic_display_maximum_forms_quantity': case 'maximum_messages_quantity': if ($data != 'unlimited') { $data = (int) $data; } break;
case 'maximum_messages_quantity_per_sender': if ($data != 'unlimited') { $data = (int) $data; } if ($data == 0) { $data = 'unlimited'; } break;
case 'commission_amount': case 'commission2_amount': case 'encrypted_urls_validity_duration': $data = round(100*$data)/100; }