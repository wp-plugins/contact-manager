<?php include dirname(__FILE__).'/countries.php';
$unsorted_countries = $countries;
$formatted_countries = array_map('format_nice_name', $countries);
asort($formatted_countries);
$countries = array(); foreach ($formatted_countries as $country_code => $country) { $countries[$country_code] = $unsorted_countries[$country_code]; }