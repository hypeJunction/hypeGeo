<?php

/**
 * Displays a location input
 * Defaults to session location, if known
 */

namespace hypeJunction\Geo;

if (!isset($vars['value'])) {
	$geopositioning = get_geopositioning();
	if ($geopositioning['location']) {
		$vars['value'] = $geopositioning['location'];
	}
}

if (elgg_view_exists('input/tokeninput')) {
	$vars['callback'] = 'hypeJunction\\Geo\\search_locations';

	$vars['class'] = 'wall-location-tokeninput';

	if (!isset($vars['multiple'])) {
		$vars['multiple'] = false;
	}

	if (!isset($vars['strict'])) {
		$vars['strict'] = false;
	}

	$vars['data-token-delimiter'] = ";";
	$vars['data-allow-tab-out'] = true;

	echo elgg_view('input/tokeninput', $vars);
} else {
	echo elgg_view('input/text', $vars);
}