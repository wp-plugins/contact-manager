<?php $html = ((strstr($body, '</')) || (strstr($body, '/>')));
foreach (array('sender', 'receiver', 'subject', 'body') as $field) {
$$field = str_replace(array("\\t", '\\', '&#91;', '&#93;'), array('	', '', '[', ']'), str_replace(array("\\r\\n", "\\n", "\\r"), '
', ((($html) && ($field == 'body')) ? $$field : str_replace(array('&lt;', '&gt;'), array('<', '>'), $$field)))); }
$headers = 'From: '.$sender.($html ? "\r\nContent-type: text/html" : "");
wp_mail($receiver, $subject, $body, $headers, $attachments);