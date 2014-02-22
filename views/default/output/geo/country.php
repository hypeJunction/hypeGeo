<?php

/**
 * Outputs a country name with a flag
 *
 * @uses $vars['value'] Country code or country name
 */

namespace hypeJunction\Geo;

$countriesByIso = Countries::getCountries('iso', 'name');
$countriesByName = Countries::getCountries('name', 'iso');

$value = elgg_extract('value', $vars);
if (!$value) {
	return;
}

if (array_key_exists(strtoupper($value), $countriesByIso)) {
	$value = strtoupper($value);
	$value_lower = strtolower($value);
	echo '<div>';
	echo '<span>' . $countriesByIso[$value] . '</span>';
	echo elgg_view_icon("flag-$value_lower");
	echo '</div>';
} else
if (array_key_exists($value, $countriesByName)) {
	$code = $countriesByName[$value];
	$code_loower = strotolower($code);
	echo '<div>';
	echo '<span>' . $value . '</span>';
	echo elgg_view_icon("flag-$code_lower");
	echo '</div>';
} else {
	echo $value;
}