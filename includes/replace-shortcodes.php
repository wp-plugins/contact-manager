<?php if ((function_exists('current_user_can')) && (!current_user_can('view_contact_manager'))
 && (function_exists('user_can')) && (!user_can($data['post_author'], 'view_contact_manager'))) {
global $contact_manager_shortcodes;
foreach (array('post_content', 'post_content_filtered', 'post_excerpt', 'post_title') as $key) {
foreach ((array) $contact_manager_shortcodes as $tag) {
$data[$key] = str_replace(array('['.$tag, $tag.']'), array('&#91;'.$tag, $tag.'&#93;'), $data[$key]); } } }