<?php $string = (string) $string;
$string = do_shortcode(str_replace(array('(', ')'), array('[', ']'), $string));
$string = str_replace(array('[', ']'), array('(', ')'), $string);
$string = str_replace(array('&#40;', '&#41;', '&#91;', '&#93;'), array('(', ')', '[', ']'), $string);