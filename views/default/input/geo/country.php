<?php

/**
 * Displays a dropdown of countries
 */

namespace hypeJunction\Geo;

use hypeJunction\Geo\Countries;

$vars['options_values'] = Countries::getCountries('iso', 'name', 'name');

if (isset($vars['class'])) {
	$vars['class'] = "{$vars['class']} geo-input-country";
} else {
	$vars['class'] = 'geo-input-country';
}

echo elgg_view('input/dropdown', $vars);
